@extends('layouts.app')

@section('title', 'Payslip')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Payslip</h1>
        <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Print Payslip
        </button>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6">
        <div>
            <h2 class="text-lg font-semibold mb-2">Employee Information</h2>
            <p><strong>Name:</strong> {{ $employee->name }}</p>
            <p><strong>Email:</strong> {{ $employee->email }}</p>
            <p><strong>Pay Period:</strong> {{ $payPeriod->start_date->format('M d, Y') }} - {{ $payPeriod->end_date->format('M d, Y') }}</p>
        </div>
        <div>
            <h2 class="text-lg font-semibold mb-2">Pay Information</h2>
            @php
                $details = json_decode($payslip->details, true);
            @endphp
            <p><strong>Monthly Effective Salary:</strong> ₱{{ number_format(optional($details)['monthly_salary'], 2) }}</p>
            <p><strong>Pay Period Type:</strong> {{ ucfirst($employee->pay_period) }}</p>
            <p><strong>Daily Rate:</strong> ₱{{ number_format(optional($details)['daily_rate'], 2) }}</p>
            <p><strong>Hourly Rate:</strong> ₱{{ number_format(optional($details)['hourly_rate'], 2) }}</p>
        </div>
    </div>

    <div class="border-t pt-6">
        <h2 class="text-lg font-semibold mb-4">Earnings & Deductions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold mb-2">Earnings</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Gross Pay (Before Deductions):</span>
                        <span>₱{{ number_format($payslip->gross_pay, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Overtime Pay:</span>
                        <span>₱{{ number_format($payslip->overtime_pay, 2) }}</span>
                    </div>
                    @if(optional($details)['holiday_working_days']['regular'] > 0)
                    <div class="flex justify-between text-green-700">
                        <span>Regular Holiday Pay (x{{ optional($details)['holiday_working_days']['regular'] }} days):</span>
                        <span>₱{{ number_format(optional($details)['holiday_working_days']['regular'] * optional($details)['daily_rate'] * 2, 2) }}</span>
                    </div>
                    @endif
                    @if(optional($details)['holiday_working_days']['special_non_working'] > 0)
                    <div class="flex justify-between text-yellow-700">
                        <span>Special Non-Working Holiday Pay (x{{ optional($details)['holiday_working_days']['special_non_working'] }} days):</span>
                        <span>₱{{ number_format(optional($details)['holiday_working_days']['special_non_working'] * optional($details)['daily_rate'] * 1.3, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-semibold">
                        <span>Total Gross Pay:</span>
                        <span>₱{{ number_format($payslip->gross_pay, 2) }}</span>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="font-semibold mb-2">Deductions</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Late Deductions:</span>
                        <span>₱{{ number_format($payslip->late_deductions, 2) }}</span>
                    </div>
                    @if(isset($details['sss_deduction']) && $details['sss_deduction'] > 0)
                    <div class="flex justify-between">
                        <span>SSS Contribution
                            @if(isset($details['sss_is_percentage']) && $details['sss_is_percentage'])
                                ({{ number_format($details['sss_employee_share_rate'], 2) }}%)
                            @endif:
                        </span>
                        <span>₱{{ number_format($details['sss_deduction'], 2) }}</span>
                    </div>
                    @endif
                    @if(isset($details['philhealth_deduction']) && $details['philhealth_deduction'] > 0)
                    <div class="flex justify-between">
                        <span>PhilHealth Contribution
                            @if(isset($details['philhealth_is_percentage']) && $details['philhealth_is_percentage'])
                                ({{ number_format($details['philhealth_employee_share_rate'], 2) }}%)
                            @endif:
                        </span>
                        <span>₱{{ number_format($details['philhealth_deduction'], 2) }}</span>
                    </div>
                    @endif
                    @if(isset($details['pagibig_deduction']) && $details['pagibig_deduction'] > 0)
                    <div class="flex justify-between">
                        <span>Pag-IBIG Contribution
                            @if(isset($details['pagibig_is_percentage']) && $details['pagibig_is_percentage'])
                                ({{ number_format($details['pagibig_employee_share_rate'], 2) }}%)
                            @endif:
                        </span>
                        <span>₱{{ number_format($details['pagibig_deduction'], 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-semibold border-t pt-2 mt-2">
                        <span>Total Deductions:</span>
                        <span>₱{{ number_format($payslip->deductions, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t mt-6 pt-6">
        <div class="flex justify-between text-xl font-bold">
            <span>Net Pay:</span>
            <span>₱{{ number_format($payslip->net_pay, 2) }}</span>
        </div>
    </div>

    <div class="border-t mt-6 pt-6">
        <h2 class="text-lg font-semibold mb-4">Attendance Summary</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <p><strong>Total Hours Worked:</strong> {{ $payslip->total_hours_worked }}</p>
                <p><strong>Overtime Hours:</strong> {{ $payslip->overtime_hours }}</p>
            </div>
            <div>
                <p><strong>Late Minutes:</strong> {{ $payslip->late_minutes }}</p>
                <p><strong>Absent Days:</strong> {{ $payslip->absent_days }}</p>
            </div>
        </div>
    </div>
</div>
@endsection