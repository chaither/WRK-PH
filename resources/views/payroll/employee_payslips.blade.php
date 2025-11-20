@extends('layouts.app')

@section('title', 'My Payslips')

@section('content')
<div class="container mx-auto px-6 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-receipt mr-3 text-indigo-600"></i> My Payslips
    </h1>

    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Your Payroll History</h2>

            @if($payslips->isEmpty())
                <div class="text-center py-10">
                    <p class="text-gray-600 text-lg mb-4">No payslips available yet.</p>
                    <p class="text-gray-500">Please check back after your next payroll generation.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Pay Period</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Gross Pay</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Deductions</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-extrabold text-indigo-700 uppercase tracking-wider">Net Pay</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($payslips as $payslip)
                                <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif" x-data="{ open: false }">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button @click="open = !open" class="flex justify-between items-center w-full focus:outline-none sm:cursor-default">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($payslip->pay_period_start)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($payslip->pay_period_end)->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ ucfirst($payslip->payPeriod->pay_period_type ?? 'N/A') }}</div>
                                            </div>
                                            <svg x-show="!open" class="w-4 h-4 sm:hidden" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            <svg x-show="open" class="w-4 h-4 sm:hidden" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 hidden sm:table-cell">₱{{ number_format($payslip->gross_pay, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 hidden sm:table-cell">₱{{ number_format($payslip->deductions, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-700 hidden sm:table-cell">₱{{ number_format($payslip->net_pay, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium hidden sm:table-cell">
                                        <a href="{{ route('payroll.show-payslip', ['employee' => $payslip->user->id, 'payPeriod' => $payslip->pay_period_id]) }}" class="text-indigo-600 hover:text-indigo-800 p-2 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150" title="View Payslip" target="_blank">
                                            <i class="fas fa-eye text-base"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="sm:hidden">
                                    <td colspan="5" class="px-6 py-4">
                                        <div class="space-y-2 text-sm text-gray-700">
                                            <p><span class="font-medium">Gross Pay:</span> ₱{{ number_format($payslip->gross_pay, 2) }}</p>
                                            <p><span class="font-medium">Deductions:</span> <span class="text-red-600">₱{{ number_format($payslip->deductions, 2) }}</span></p>
                                            <p class="font-bold">Net Pay: ₱{{ number_format($payslip->net_pay, 2) }}</p>
                                            <div class="flex justify-end mt-4">
                                                <a href="{{ route('payroll.show-payslip', ['employee' => $payslip->user->id, 'payPeriod' => $payslip->pay_period_id]) }}" class="text-indigo-600 hover:text-indigo-800 p-2 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150" title="View Payslip" target="_blank">
                                                    <i class="fas fa-eye text-base"></i> View Payslip
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
