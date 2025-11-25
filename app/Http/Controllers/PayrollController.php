<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DTRRecord;
use App\Models\PayPeriod;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\PayrollService;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->middleware(['auth']);
        $this->middleware(\App\Http\Middleware\EnsureHrAdminRole::class)->except(['employeePayslips', 'showPayslip']); // Apply HR/Admin role to most methods, but allow all authenticated users for employeePayslips and showPayslip
        $this->payrollService = $payrollService;
    }

    /**
     * Display a listing of payslips for the authenticated employee.
     */
    public function employeePayslips()
    {
        $user = Auth::user();
        $payslips = Payslip::where('user_id', $user->id)
            ->orderByDesc('pay_period_end')
            ->get();
        
        return view('payroll.employee_payslips', compact('payslips'));
    }

    public function index(Request $request)
    {
        $payPeriods = PayPeriod::orderBy('end_date', 'desc')->get();
        $today = Carbon::today();
        $start = $request->input('start_date', $today->copy()->startOfMonth()->format('Y-m-d'));
        $end = $request->input('end_date', $today->copy()->endOfMonth()->format('Y-m-d'));

        $payrolls = collect();
        $currentPeriod = null;

        $currentPeriod = PayPeriod::where('start_date', $start)->where('end_date', $end)->first();

        if ($currentPeriod) {
            $payslipQuery = Payslip::with(['user', 'payPeriod'])
                ->where('pay_period_id', $currentPeriod->id);

            $payrolls = $payslipQuery->get();
        }

        // Get total employees directly from the User model, filtered by pay schedule if applicable
        $totalEmployeesQuery = User::where('role', 'employee');
        $totalEmployees = $totalEmployeesQuery->count();
        $totalGrossPay = $payrolls->sum('gross_pay');
        $totalDeductions = $payrolls->sum('deductions');
        $totalNetPay = $payrolls->sum('net_pay');

        return view('payroll.index', compact('payPeriods', 'currentPeriod', 'payrolls', 'totalEmployees', 'totalGrossPay', 'totalDeductions', 'totalNetPay', 'start', 'end'));
    }

    public function createPayPeriod(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        PayPeriod::create($validated);
        return redirect()->route('payroll.index')->with('success', 'Pay period created successfully');
    }

    public function generatePayslips(PayPeriod $payPeriod)
    {
        // Call the PayrollService to generate payslips
        // Determine pay schedule filter based on the pay period duration
        $payScheduleFilter = null;
        if ($payPeriod->start_date->day === 1 && $payPeriod->end_date->day === 15) {
            $payScheduleFilter = 'semi-monthly';
        } elseif ($payPeriod->start_date->day === 16 && $payPeriod->end_date->isSameDay($payPeriod->end_date->endOfMonth())) {
            $payScheduleFilter = 'semi-monthly';
        } elseif ($payPeriod->start_date->isSameDay($payPeriod->start_date->copy()->startOfMonth()) && $payPeriod->end_date->isSameDay($payPeriod->end_date->copy()->endOfMonth())) {
            $payScheduleFilter = 'monthly';
        }
        
        $this->payrollService->generatePayslipsForPeriod($payPeriod->start_date, $payPeriod->end_date, $payScheduleFilter);

        // Mark period as completed after creating payslips
        $payPeriod->update(['status' => 'unpaid']); // Or 'processing' if there's another step

        return redirect()->route('payroll.index')->with('success', 'Payslips generated successfully');
    }

    // Generate for arbitrary date range submitted from the UI
    public function generateForRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $payScheduleFilter = null;
        if (Carbon::parse($start)->day === 1 && Carbon::parse($end)->day === 15) {
            $payScheduleFilter = 'semi-monthly';
        } elseif (Carbon::parse($start)->day === 16 && Carbon::parse($end)->isSameDay(Carbon::parse($end)->endOfMonth())) {
            $payScheduleFilter = 'semi-monthly';
        } elseif (Carbon::parse($start)->isSameDay(Carbon::parse($start)->startOfMonth()) && Carbon::parse($end)->isSameDay(Carbon::parse($end)->endOfMonth())) {
            $payScheduleFilter = 'monthly';
        }

        $payPeriod = PayPeriod::firstOrCreate([
            'start_date' => $start,
            'end_date' => $end
        ], ['status' => 'draft', 'pay_period_type' => $payScheduleFilter ?? 'semi-monthly']);

        // Do not regenerate if already generated/unpaid or paid
        if (in_array($payPeriod->status, ['unpaid', 'paid']) && !$request->has('force_regenerate')) {
            return redirect()->route('payroll.index', ['start_date' => $start, 'end_date' => $end])->with('info', 'Payroll for the selected period has already been generated. Click \'Regenerate Payroll\' again to force regeneration.');
        }

        // If forced regeneration, set status to draft to allow re-generation
        if ($request->has('force_regenerate') && in_array($payPeriod->status, ['unpaid', 'paid'])) {
            $payPeriod->update(['status' => 'draft']);
        }

        // Call existing generator
        // Instead of calling generatePayslips directly, we determine the filter and pass it
        $this->payrollService->generatePayslipsForPeriod(Carbon::parse($start), Carbon::parse($end), $payScheduleFilter);

        $payPeriod->update(['status' => 'unpaid']); // Update status after generation

        return redirect()->route('payroll.index', ['start_date' => $start, 'end_date' => $end])->with('success', 'Payroll generated for selected period.');
    }

    // Mark pay period as completed (done payment)
    public function completePayPeriod(PayPeriod $payPeriod)
    {
        if ($payPeriod->status !== 'paid') {
            $payPeriod->update(['status' => 'paid']);
        }

        return redirect()->route('payroll.index', ['start_date' => $payPeriod->start_date, 'end_date' => $payPeriod->end_date])->with('success', 'Pay period marked as paid.');
    }

    public function showPayslip(User $employee, PayPeriod $payPeriod)
    {
        $payslip = Payslip::where('user_id', $employee->id)
            ->where('pay_period_id', $payPeriod->id)
            ->firstOrFail();

        return view('payroll.payslip', compact('payslip', 'employee', 'payPeriod'));
    }

    /**
     * Update Other Deduction for a payslip.
     */
    public function updateOtherDeduction(Request $request, Payslip $payslip)
    {
        $request->validate([
            'other_deductions' => 'required|numeric|min:0',
        ]);
        $user = Auth::user();
        // Only admin/hr can do this.
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }
        $payslip->other_deductions = $request->input('other_deductions');
        $payslip->save();
        return redirect()->back()->with('success', 'Other Deduction updated successfully.');
    }

    public function updateDeductions(Request $request, Payslip $payslip)
    {
        $request->validate([
            'deductions' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $payslip->deductions = $request->input('deductions');
        // Recalculate net pay
        $grossPay = $payslip->gross_pay; // Use the gross_pay already calculated by PayrollService
        $payslip->net_pay = $grossPay - $payslip->deductions;

        $payslip->save();

        return back()->with('success', 'Deductions updated successfully.');
    }

    public function downloadPdf(Request $request)
    {
        if (!Auth::user()->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $payPeriod = PayPeriod::where('start_date', $start)->where('end_date', $end)->first();

        if (!$payPeriod) {
            return redirect()->back()->with('error', 'No payroll period found for the selected dates.');
        }

        $payslipQuery = Payslip::with(['user', 'payPeriod'])
            ->where('pay_period_id', $payPeriod->id);

        $payrolls = $payslipQuery->get();

        $data = [
            'payPeriod' => $payPeriod,
            'payrolls' => $payrolls,
            'payScheduleFilter' => null, // Pass to view for potential display
        ];

        // Render both views to HTML
        $payrollHtml = view('payroll.payslips_pdf', $data)->render();
        $signaturesHtml = view('payroll.payslips_signatures_pdf', $data)->render();

        // No explicit page break needed here; will merge PDFs later
        $combinedHtml = $payrollHtml . $signaturesHtml;

        $pdf = Pdf::loadHtml($combinedHtml)->setPaper('a4', 'landscape');
        
        return $pdf->download('payroll_report_' . $start . '_' . $end . '.pdf');
    }
}