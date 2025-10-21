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
            <p><strong>Basic Salary:</strong> ₱{{ number_format($employee->basic_salary, 2) }}</p>
            <p><strong>Pay Period Type:</strong> {{ ucfirst($employee->pay_period) }}</p>
            <p><strong>Daily Rate:</strong> ₱{{ number_format($employee->daily_rate, 2) }}</p>
        </div>
    </div>

    <div class="border-t pt-6">
        <h2 class="text-lg font-semibold mb-4">Earnings & Deductions</h2>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold mb-2">Earnings</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Basic Pay:</span>
                        <span>₱{{ number_format($payslip->basic_pay, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Overtime Pay:</span>
                        <span>₱{{ number_format($payslip->overtime_pay, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold">
                        <span>Total Earnings:</span>
                        <span>₱{{ number_format($payslip->basic_pay + $payslip->overtime_pay, 2) }}</span>
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
                    <div class="flex justify-between">
                        <span>SSS:</span>
                        <span>₱{{ number_format($payslip->sss, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>GSIS:</span>
                        <span>₱{{ number_format($payslip->gsis, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>PhilHealth:</span>
                        <span>₱{{ number_format($payslip->philhealth, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Other Deductions:</span>
                        <span>₱{{ number_format($payslip->other_deductions, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold border-t pt-2 mt-2">
                        <span>Total Deductions:</span>
                        <span>₱{{ number_format($payslip->late_deductions + $payslip->sss + $payslip->gsis + $payslip->philhealth + $payslip->other_deductions, 2) }}</span>
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
        <div class="grid grid-cols-2 gap-6">
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