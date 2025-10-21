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
                
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-6">
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
