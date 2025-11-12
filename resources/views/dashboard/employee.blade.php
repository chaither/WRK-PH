@extends('layouts.app')

@section('title', 'Employee Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative">
    <!-- Header Section -->
    <div class="bg-white/80 backdrop-blur-sm shadow-lg border-b border-gray-200/50 p-6 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    Employee Dashboard
                </h1>
                <p class="text-gray-600 mt-2">Welcome, {{ auth()->user()->name }}!</p>
            </div>
            <div class="flex items-center space-x-4">
                @if(auth()->user()->shift)
                    <span class="text-sm text-gray-700"><i class="fas fa-clock mr-1"></i> Shift: {{ auth()->user()->shift->name }} ({{ \Illuminate\Support\Carbon::parse(auth()->user()->shift->start_time)->format('h:i A') }} - {{ \Illuminate\Support\Carbon::parse(auth()->user()->shift->end_time)->format('h:i A') }})</span>
                @else
                    <span class="text-sm text-gray-700">Shift: Not Assigned</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-6">
        @if(auth()->user()->role === 'employee')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Quick Stats Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Quick Stats</h2>
                <div class="space-y-3">
                    <p class="text-gray-600 flex justify-between items-center">
                        <span><i class="fas fa-user-tie mr-2 text-blue-500"></i>Position:</span>
                        <span class="font-medium text-gray-800">{{ auth()->user()->position }}</span>
                    </p>
                    <p class="text-gray-600 flex justify-between items-center">
                        <span><i class="fas fa-briefcase mr-2 text-indigo-500"></i>Department:</span>
                        <span class="font-medium text-gray-800">{{ auth()->user()->department->name ?? 'N/A' }}</span>
                    </p>
                    <p class="text-gray-600 flex justify-between items-center">
                        <span><i class="fas fa-calendar-alt mr-2 text-green-500"></i>Start Date:</span>
                        <span class="font-medium text-gray-800">{{ \Illuminate\Support\Carbon::parse(auth()->user()->start_date)->format('M d, Y') }}</span>
                    </p>
                    <p class="text-gray-600 flex justify-between items-center">
                        <span><i class="fas fa-calendar-check mr-2 text-purple-500"></i>Working Days:</span>
                        <span class="font-medium text-gray-800">{{ implode(', ', auth()->user()->working_days ?? ['N/A']) }}</span>
                    </p>
                    <p class="text-gray-600 flex justify-between items-center">
                        <span><i class="fas fa-couch mr-2 text-red-500"></i>Rest Days:</span>
                        <span class="font-medium text-gray-800">{{ implode(', ', auth()->user()->rest_days ?? ['N/A']) }}</span>
                    </p>
                </div>
            </div>

            <!-- Leave Balance Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Leave Balance</h2>
                <div class="flex items-center justify-between">
                    <p class="text-5xl font-bold text-blue-600">{{ auth()->user()->leave_balance ?? 0 }}</p>
                    <span class="text-gray-500">Days Remaining</span>
                </div>
                <a href="{{ route('employee.leave.index') }}" class="mt-4 inline-block text-blue-500 hover:text-blue-700 text-sm font-medium">
                    Request Leave <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <!-- Next Shift Card (Optional, if you want to display this prominently) -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Your Shift Details</h2>
                @if(auth()->user()->shift)
                    <div class="space-y-3">
                        <p class="text-gray-600 flex justify-between items-center">
                            <span><i class="fas fa-clock mr-2 text-indigo-500"></i>Shift Name:</span>
                            <span class="font-medium text-gray-800">{{ auth()->user()->shift->name }}</span>
                        </p>
                        <p class="text-gray-600 flex justify-between items-center">
                            <span><i class="fas fa-hourglass-start mr-2 text-green-500"></i>Start Time:</span>
                            <span class="font-medium text-gray-800">{{ \Illuminate\Support\Carbon::parse(auth()->user()->shift->start_time)->format('h:i A') }}</span>
                        </p>
                        <p class="text-gray-600 flex justify-between items-center">
                            <span><i class="fas fa-hourglass-end mr-2 text-red-500"></i>End Time:</span>
                            <span class="font-medium text-gray-800">{{ \Illuminate\Support\Carbon::parse(auth()->user()->shift->end_time)->format('h:i A') }}</span>
                        </p>
                    </div>
                @else
                    <p class="text-gray-600">No shift assigned.</p>
                @endif
            </div>
        </div>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // The profile dropdown toggle and close logic is now handled in layouts/app.blade.php
    });
</script>
@endpush
