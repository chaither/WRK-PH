@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @if(auth()->user()->role === 'admin')
        <div class="bg-blue-100 p-6 rounded-lg">
            <h3 class="text-xl font-semibold text-blue-800">Total Employees</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $employeeCount ?? 0 }}</p>
        </div>
        @endif

        @if(in_array(auth()->user()->role, ['admin', 'hr']))
        <div class="bg-green-100 p-6 rounded-lg">
            <h3 class="text-xl font-semibold text-green-800">Present Today</h3>
            <p class="text-3xl font-bold text-green-600">{{ $presentToday ?? 0 }}</p>
        </div>

        <div class="bg-yellow-100 p-6 rounded-lg">
            <h3 class="text-xl font-semibold text-yellow-800">Late Today</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $lateToday ?? 0 }}</p>
        </div>

        <div class="bg-red-100 p-6 rounded-lg">
            <h3 class="text-xl font-semibold text-red-800">Absent Today</h3>
            <p class="text-3xl font-bold text-red-600">{{ $absentToday ?? 0 }}</p>
        </div>
        @endif
    </div>
</div>
@if(auth()->user()->role === 'employee')
<div class="bg-white rounded-lg shadow-md p-6 mt-8">
    <h2 class="text-xl font-bold mb-4">My Payslips</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2">Pay Period</th>
                    <th class="px-4 py-2">Net Pay</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payslips as $payslip)
                    <tr class="border-b">
                        <td class="px-4 py-2">
                            {{ $payslip->payPeriod->start_date->format('M d, Y') }} - {{ $payslip->payPeriod->end_date->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-2">₱{{ number_format($payslip->net_pay, 2) }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('payroll.show-payslip', ['employee' => auth()->id(), 'payPeriod' => $payslip->pay_period_id]) }}" class="text-blue-500 hover:text-blue-700" title="View Payslip">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-center text-gray-500">No payslips found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection