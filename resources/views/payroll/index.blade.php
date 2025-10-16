@extends('layouts.app')

@section('title', 'Payroll')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">Payroll Management</h2>

        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-blue-100 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold text-blue-800">Total Employees</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalEmployees ?? 0 }}</p>
                </div>

                <div class="bg-green-100 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold text-green-800">Total Work Hours</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $totalHours ?? 0 }}</p>
                </div>

                <div class="bg-purple-100 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold text-purple-800">Total Payroll</h3>
                    <p class="text-3xl font-bold text-purple-600">₱{{ number_format($totalPayroll ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <form method="GET" class="mb-4 flex flex-wrap gap-4 items-end">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $start ?? '' }}" class="border rounded px-2 py-1">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $end ?? '' }}" class="border rounded px-2 py-1">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
        </form>
        @if($start && $end)
        <form method="POST" action="{{ route('payroll.generate.range') }}" class="mb-4">
            @csrf
            <input type="hidden" name="start_date" value="{{ $start }}">
            <input type="hidden" name="end_date" value="{{ $end }}">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Generate Payroll</button>
        </form>
        @endif
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Employee</th>
                        <th class="px-4 py-2">Work Hours</th>
                        <th class="px-4 py-2">Rate/Hour</th>
                        <th class="px-4 py-2">Gross Pay</th>
                        <th class="px-4 py-2">SSS</th>
                        <th class="px-4 py-2">GSIS</th>
                        <th class="px-4 py-2">PhilHealth</th>
                        <th class="px-4 py-2">Other Deductions</th>
                        <th class="px-4 py-2">Net Pay</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payslip)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $payslip->user->name }}</td>
                            <td class="px-4 py-2">{{ $payslip->total_hours_worked }}</td>
                            <td class="px-4 py-2">₱{{ number_format(optional($payslip->user)->hourly_rate, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->basic_pay + $payslip->overtime_pay, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->sss, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->gsis, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->philhealth, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->late_deductions + $payslip->absences_deductions, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->net_pay, 2) }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('payroll.show-payslip', ['employee' => $payslip->user->id, 'payPeriod' => $payslip->pay_period_id]) }}" class="text-blue-500 hover:text-blue-700" title="View Payslip">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-2 text-center text-gray-500">No payroll records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection