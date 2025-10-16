<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DTRRecord;
use App\Models\PayPeriod;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $payPeriods = PayPeriod::orderBy('end_date', 'desc')->get();
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $payrolls = collect();
        $currentPeriod = null;
        $employees = collect();
        if ($start && $end) {
            // Determine if this is a whole month filter
            $startDate = \Carbon\Carbon::parse($start);
            $endDate = \Carbon\Carbon::parse($end);
            $isWholeMonth = $startDate->isSameDay($startDate->copy()->startOfMonth()) && $endDate->isSameDay($endDate->copy()->endOfMonth());
            if ($isWholeMonth) {
                $employees = User::where('role', 'employee')->where('pay_period', 'monthly')->get();
            } else {
                $employees = User::where('role', 'employee')->where('pay_period', 'semi-monthly')->get();
            }

            $currentPeriod = PayPeriod::firstOrCreate([
                'start_date' => $start,
                'end_date' => $end
            ], ['status' => 'draft']);

            if ($currentPeriod) {
                $payrolls = Payslip::with(['user', 'payPeriod'])
                    ->where('pay_period_id', $currentPeriod->id)
                    ->whereHas('user', function($q) use ($isWholeMonth) {
                        $q->where('pay_period', $isWholeMonth ? 'monthly' : 'semi-monthly');
                    })
                    ->get();

                // compute work_days (weekdays) and present_days for each payslip
                $payrolls = $payrolls->map(function($p) {
                    $start = \Carbon\Carbon::parse($p->payPeriod->start_date);
                    $end = \Carbon\Carbon::parse($p->payPeriod->end_date);
                    $workDays = 0;
                    for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                        if ($d->isWeekday()) $workDays++;
                    }
                    $presentDays = max(0, $workDays - ($p->absent_days ?? 0));
                    $p->work_days = $workDays;
                    $p->present_days = $presentDays;
                    return $p;
                });
            }
        } else {
            // Default: show all employees (or leave empty)
            $employees = User::where('role', 'employee')->get();
        }

        // (Generation moved to generateForRange route)

        $totalEmployees = $employees->count();
        $totalHours = $payrolls->sum('total_hours_worked');
        $totalPayroll = $payrolls->sum('net_pay');

        return view('payroll.index', compact('payPeriods', 'currentPeriod', 'employees', 'payrolls', 'totalEmployees', 'totalHours', 'totalPayroll', 'start', 'end'));
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
        $employees = User::where('role', 'employee')->get();

        foreach ($employees as $employee) {
            $dtrRecords = DTRRecord::where('user_id', $employee->id)
                ->whereBetween('date', [$payPeriod->start_date, $payPeriod->end_date])
                ->get();

            $totalHours = $dtrRecords->sum(function($record) {
                return $record->calculateWorkHours();
            });

            $overtimeHours = $dtrRecords->sum(function($record) {
                return $record->calculateOvertimeHours();
            });

            $lateMinutes = $dtrRecords->sum('late_minutes');
            $absentDays = $this->calculateAbsentDays($employee, $payPeriod, $dtrRecords);

            // Calculate pays and deductions
            $basicPay = $totalHours * $employee->hourly_rate;
            $overtimePay = $overtimeHours * ($employee->hourly_rate * 1.25); // 25% overtime premium
            $lateDeductions = ($lateMinutes / 60) * $employee->hourly_rate;
            $absenceDeductions = $absentDays * $employee->daily_rate;

            // Example government deduction formulas (replace with your actual logic)
            $sss = $basicPay * 0.045; // 4.5% SSS
            $gsis = $basicPay * 0.09; // 9% GSIS
            $philhealth = $basicPay * 0.035; // 3.5% PhilHealth
            $other_deductions = 0; // Reset on generation

            $totalDeductions = $lateDeductions + $absenceDeductions + $sss + $gsis + $philhealth + $other_deductions;
            $grossPay = $basicPay + $overtimePay;
            $netPay = $grossPay - $totalDeductions;

            Payslip::updateOrCreate(
                [
                    'user_id' => $employee->id,
                    'pay_period_id' => $payPeriod->id
                ],
                [
                    'basic_pay' => $basicPay,
                    'overtime_pay' => $overtimePay,
                    'late_deductions' => $lateDeductions,
                    'absences_deductions' => $absenceDeductions,
                    'sss' => $sss,
                    'gsis' => $gsis,
                    'philhealth' => $philhealth,
                    'other_deductions' => $other_deductions,
                    'net_pay' => $netPay,
                    'total_hours_worked' => $totalHours,
                    'overtime_hours' => $overtimeHours,
                    'late_minutes' => $lateMinutes,
                    'absent_days' => $absentDays
                ]
            );
        }

    // mark period as unpaid after creating payslips
    $payPeriod->update(['status' => 'unpaid']);
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

        $payPeriod = PayPeriod::firstOrCreate([
            'start_date' => $start,
            'end_date' => $end
        ], ['status' => 'draft']);

        // Do not regenerate if already generated/unpaid or paid
        if (in_array($payPeriod->status, ['unpaid', 'paid'])) {
            return redirect()->route('payroll.index', ['start_date' => $start, 'end_date' => $end])->with('info', 'Payroll for the selected period has already been generated.');
        }

        // Call existing generator
        $this->generatePayslips($payPeriod);

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

    private function getCurrentPayPeriod()
    {
        $today = Carbon::today();
        $day = $today->day;

        // For semi-monthly: 1-15 and 16-end of month
        if ($day <= 15) {
            $startDate = $today->copy()->startOfMonth();
            $endDate = $today->copy()->day(15);
        } else {
            $startDate = $today->copy()->day(16);
            $endDate = $today->copy()->endOfMonth();
        }

        return PayPeriod::firstOrCreate(
            [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            ['status' => 'draft']
        );
    }

    private function calculateAbsentDays($employee, $payPeriod, $dtrRecords)
    {
        $startDate = Carbon::parse($payPeriod->start_date);
        $endDate = Carbon::parse($payPeriod->end_date);
        $workingDays = 0;

        // Count working days (Monday to Friday) in the pay period
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekday()) {
                $workingDays++;
            }
        }

        $presentDays = $dtrRecords->unique('date')->count();
        return $workingDays - $presentDays;
    }

    public function updateDeductions(Request $request, Payslip $payslip)
    {
        $request->validate([
            'sss' => 'required|numeric|min:0',
            'gsis' => 'required|numeric|min:0',
            'philhealth' => 'required|numeric|min:0',
            'other_deductions' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $payslip->sss = $request->input('sss');
        $payslip->gsis = $request->input('gsis');
        $payslip->philhealth = $request->input('philhealth');
        $payslip->other_deductions = $request->input('other_deductions');

        // Recalculate net pay
        $totalDeductions = $payslip->sss + $payslip->gsis + $payslip->philhealth + $payslip->other_deductions + $payslip->late_deductions + $payslip->absences_deductions;
        $grossPay = $payslip->basic_pay + $payslip->overtime_pay;
        $payslip->net_pay = $grossPay - $totalDeductions;

        $payslip->save();

        return back()->with('success', 'Deductions updated successfully.');
    }
}