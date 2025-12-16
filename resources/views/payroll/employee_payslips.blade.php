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
                <!-- Search Input -->
                <div class="mb-4">
                    <input type="text" id="payslip-search" placeholder="Search payslips by pay period..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <!-- Desktop View -->
                <div class="hidden sm:block">
                    <div class="">
                        <table class="divide-y divide-gray-200 w-full">
                            <thead class="bg-gray-50">
                                <tr class="bg-gray-50">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Pay Period</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Net Pay</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($payslips as $payslip)
                                    <tr class="payslip-row hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($payslip->pay_period_start)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($payslip->pay_period_end)->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-500">
                                                @php
                                                    $start = \Carbon\Carbon::parse($payslip->pay_period_start);
                                                    $end = \Carbon\Carbon::parse($payslip->pay_period_end);
                                                    $isMonthly = $start->day == 1 && $end->isSameDay($end->copy()->endOfMonth());
                                                @endphp
                                                {{ $isMonthly ? 'Monthly' : 'Semi-monthly' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-indigo-700">₱{{ number_format($payslip->net_pay, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($payslip->payPeriod->status === 'paid')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Done Payment
                                                </span>
                                            @elseif($payslip->payPeriod->status === 'closed')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-800">
                                                    Closed
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
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
                </div>

                <!-- Mobile View -->
                <div class="sm:hidden">
                    @foreach($payslips as $payslip)
                        <div x-data="{ open: false }" class="payslip-card mb-4 bg-white rounded-lg shadow-md overflow-hidden">
                            <button @click="open = !open; console.log('Mobile Button clicked, open state:', open)" class="flex justify-between items-center w-full px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 focus:outline-none">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($payslip->pay_period_start)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($payslip->pay_period_end)->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">
                                        @php
                                            $start = \Carbon\Carbon::parse($payslip->pay_period_start);
                                            $end = \Carbon\Carbon::parse($payslip->pay_period_end);
                                            $isMonthly = $start->day == 1 && $end->isSameDay($end->copy()->endOfMonth());
                                        @endphp
                                        {{ $isMonthly ? 'Monthly' : 'Semi-monthly' }}
                                    </div>
                                </div>
                                <svg x-show="!open" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="open" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            <div x-bind:style="open ? 'display: block;' : 'display: none;'" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="p-4 border-t border-gray-200">
                                <div class="space-y-3 text-sm text-gray-700">
                                    <p class="flex justify-between items-center"><span class="font-medium text-gray-800">Gross Pay:</span> <span class="text-gray-700">₱{{ number_format($payslip->gross_pay, 2) }}</span></p>
                                    <p class="flex justify-between items-center"><span class="font-medium text-gray-800">Deductions:</span> <span class="text-red-600">₱{{ number_format($payslip->deductions, 2) }}</span></p>
                                    <p class="flex justify-between items-center text-lg font-bold text-indigo-700"><span>Net Pay:</span> <span>₱{{ number_format($payslip->net_pay, 2) }}</span></p>
                                    <p class="flex justify-between items-center">
                                        <span class="font-medium text-gray-800">Status:</span>
                                        @if($payslip->payPeriod->status === 'paid')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Done Payment
                                            </span>
                                        @elseif($payslip->payPeriod->status === 'closed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-800">
                                                Closed
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Pending
                                            </span>
                                        @endif
                                    </p>
                                    <div class="pt-3 border-t border-gray-200 flex justify-center">
                                        <a href="{{ route('payroll.show-payslip', ['employee' => $payslip->user->id, 'payPeriod' => $payslip->pay_period_id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out" title="View Payslip" target="_blank">
                                            <i class="fas fa-eye mr-2"></i> View Payslip
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.getElementById('payslip-search').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.payslip-row');
    const cards = document.querySelectorAll('.payslip-card');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });

    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>
@endsection
