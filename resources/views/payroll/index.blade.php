@extends('layouts.app')

@section('title', 'Payroll')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">Payroll Management</h2>

        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-100 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold text-blue-800">Total Employees</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalEmployees ?? 0 }}</p>
                </div>

                <div class="bg-purple-100 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold text-purple-800">Total Payroll</h3>
                    <p class="text-3xl font-bold text-purple-600">₱{{ number_format($totalPayroll ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Quick-select buttons for semi-monthly payroll --}}
        <div class="mb-2 flex gap-2">
            @php
                $now = \Carbon\Carbon::now();
                $firstHalfStart = $now->copy()->startOfMonth()->format('Y-m-d');
                $firstHalfEnd = $now->copy()->day(15)->format('Y-m-d');
                $secondHalfStart = $now->copy()->day(16)->format('Y-m-d');
                $secondHalfEnd = $now->copy()->endOfMonth()->format('Y-m-d');
            @endphp
            <a href="?start_date={{ $firstHalfStart }}&end_date={{ $firstHalfEnd }}" class="px-3 py-2 bg-blue-100 text-blue-800 rounded hover:bg-blue-200">1st–15th ({{ $now->format('F Y') }})</a>
            <a href="?start_date={{ $secondHalfStart }}&end_date={{ $secondHalfEnd }}" class="px-3 py-2 bg-purple-100 text-purple-800 rounded hover:bg-purple-200">16th–End ({{ $now->format('F Y') }})</a>
            <a href="?start_date={{ $now->copy()->startOfMonth()->format('Y-m-d') }}&end_date={{ $now->copy()->endOfMonth()->format('Y-m-d') }}" class="px-3 py-2 bg-green-100 text-green-800 rounded hover:bg-green-200">Whole Month ({{ $now->format('F Y') }})</a>
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
            @php $period = $currentPeriod; @endphp
            <div class="mb-4 flex items-center justify-between">
                <div>
                    @if($period && $period->status === 'paid')
                        <span class="px-3 py-2 bg-green-100 text-green-800 rounded">Payroll for selected period: <strong>Paid</strong></span>
                    @elseif($period && $period->status === 'unpaid')
                        <span class="px-3 py-2 bg-yellow-100 text-yellow-800 rounded">Payroll for selected period: <strong>Unpaid</strong></span>
                    @else
                        <span class="px-3 py-2 bg-gray-100 text-gray-800 rounded">Payroll for selected period: <strong>Not generated</strong></span>
                    @endif
                </div>

                <div class="flex gap-3">
                    <form method="POST" action="{{ route('payroll.generate.range') }}" class="">
                        @csrf
                        <input type="hidden" name="start_date" value="{{ $start }}">
                        <input type="hidden" name="end_date" value="{{ $end }}">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Generate Payroll</button>
                    </form>

                    @if(!empty($period) && ($payrolls->count() ?? 0) > 0 && $period->status !== 'paid')
                        <form method="POST" action="{{ route('payroll.pay-periods.complete', ['payPeriod' => $period->id]) }}">
                            @csrf
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Done Payment</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Employee</th>
                        <th class="px-4 py-2">Work Days</th>
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
                            <td class="px-4 py-2">{{ $payslip->present_days ?? 0 }} / {{ $payslip->work_days ?? 0 }}</td>
                            <td class="px-4 py-2">₱{{ number_format(optional($payslip->user)->hourly_rate, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->basic_pay + $payslip->overtime_pay, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->sss, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->gsis, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->philhealth, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->other_deductions ?? 0, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($payslip->net_pay, 2) }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('payroll.show-payslip', ['employee' => $payslip->user->id, 'payPeriod' => $payslip->pay_period_id]) }}" class="text-blue-500 hover:text-blue-700 mr-2" title="View Payslip">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="openEditDeductionsModal({{ $payslip->id }}, {{ $payslip->sss }}, {{ $payslip->gsis }}, {{ $payslip->philhealth }}, {{ $payslip->other_deductions ?? 0 }})" class="text-red-500 hover:text-red-700" title="Set Other Deduction">
                                    <i class="fas fa-coins"></i>
                                </button>
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

@push('modals')
<div id="editDeductionsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-md p-6 max-w-lg w-full mx-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Edit Deductions</h2>
            <button onclick="closeEditDeductionsModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editDeductionsForm" method="POST" action="" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="payslip_id" id="edit_deductions_payslip_id">

            <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                <h3 class="text-lg font-semibold mb-4 text-blue-800">Government Deductions</h3>
                <div>
                    <label for="sss_amount" class="block text-gray-700 font-medium mb-2">SSS (₱)</label>
                    <input type="number" min="0" step="0.01" name="sss" id="sss_amount" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label for="gsis_amount" class="block text-gray-700 font-medium mb-2">GSIS (₱)</label>
                    <input type="number" min="0" step="0.01" name="gsis" id="gsis_amount" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label for="philhealth_amount" class="block text-gray-700 font-medium mb-2">PhilHealth (₱)</label>
                    <input type="number" min="0" step="0.01" name="philhealth" id="philhealth_amount" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                <h3 class="text-lg font-semibold mb-4 text-blue-800">Other Deductions</h3>
                <div>
                    <label for="other_deductions_amount" class="block text-gray-700 font-medium mb-2">Other Deductions (₱)</label>
                    <input type="number" min="0" step="0.01" name="other_deductions" id="other_deductions_amount" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6">
                <button type="button" onclick="closeEditDeductionsModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openEditDeductionsModal(payslipId, sss, gsis, philhealth, other_deductions) {
        document.getElementById('edit_deductions_payslip_id').value = payslipId;

        document.getElementById('sss_amount').value = sss;
        document.getElementById('gsis_amount').value = gsis;
        document.getElementById('philhealth_amount').value = philhealth;
        document.getElementById('other_deductions_amount').value = 0; // As per user request

        const form = document.getElementById('editDeductionsForm');
        form.action = "/payroll/payslips/" + payslipId + "/deductions";

        document.getElementById('editDeductionsModal').classList.remove('hidden');
        document.getElementById('editDeductionsModal').classList.add('flex');
    }

    function closeEditDeductionsModal() {
        document.getElementById('editDeductionsModal').classList.add('hidden');
        document.getElementById('editDeductionsModal').classList.remove('flex');
    }
</script>
@endpush