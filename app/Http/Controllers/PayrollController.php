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
        $employees = User::where('role', 'employee')->get();

        // Date range filter
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $payrolls = collect();
        $currentPeriod = null;
        if ($start && $end) {
            $currentPeriod = PayPeriod::firstOrCreate([
                'start_date' => $start,
                'end_date' => $end
            ], ['status' => 'draft']);
            if ($currentPeriod) {
                $payrolls = Payslip::with('user')
                    ->where('pay_period_id', $currentPeriod->id)
                    ->get();
            }
        }

        // Handle generate payroll action
        if ($request->isMethod('post') && $request->input('generate_payroll') && $start && $end) {
            // Only generate if not already generated
            $currentPeriod = PayPeriod::firstOrCreate([
                'start_date' => $start,
                'end_date' => $end
            ], ['status' => 'draft']);
            $this->generatePayslips($currentPeriod);
            return redirect()->route('payroll.index', ['start_date' => $start, 'end_date' => $end])->with('success', 'Payroll generated for selected period.');
        }

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

            $netPay = $basicPay + $overtimePay - $lateDeductions - $absenceDeductions - $sss - $gsis - $philhealth;

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
                    'net_pay' => $netPay,
                    'total_hours_worked' => $totalHours,
                    'overtime_hours' => $overtimeHours,
                    'late_minutes' => $lateMinutes,
                    'absent_days' => $absentDays
                ]
            );
        }

        $payPeriod->update(['status' => 'completed']);
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

        // Call existing generator
        $this->generatePayslips($payPeriod);

        return redirect()->route('payroll.index', ['start_date' => $start, 'end_date' => $end])->with('success', 'Payroll generated for selected period.');
    }

    public function showPayslip(User $employee, PayPeriod $payPeriod)
    {
        $payslip = Payslip::where('user_id', $employee->id)
            ->where('pay_period_id', $payPeriod->id)
            ->firstOrFail();

        return view('payroll.payslip', compact('payslip', 'employee', 'payPeriod'));
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
}