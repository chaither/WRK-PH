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
                                <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($payslip->pay_period_start)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($payslip->pay_period_end)->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ ucfirst($payslip->payPeriod->pay_period_type ?? 'N/A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₱{{ number_format($payslip->gross_pay, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">₱{{ number_format($payslip->deductions, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-700">₱{{ number_format($payslip->net_pay, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('payroll.show-payslip', ['employee' => $payslip->user->id, 'payPeriod' => $payslip->pay_period_id]) }}" class="text-indigo-600 hover:text-indigo-800 p-2 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150" title="View Payslip" target="_blank">
                                            <i class="fas fa-eye text-base"></i>
                                        </a>
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
