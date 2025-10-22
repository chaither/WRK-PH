@extends('layouts.app')

@section('title', 'Payroll')

@section('content')
<div class="container mx-auto px-6 py-6"> {{-- Reduced vertical padding to py-6 --}}
    <header class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-money-bill-wave mr-3 text-indigo-600"></i> Payroll Management
        </h1>
    </header>

    <div class="bg-white rounded-lg shadow-xl p-6"> {{-- Reduced padding to p-6 --}}
        <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Payroll Overview</h2>

        {{-- Payroll Statistics Cards (More compact design) --}}
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4"> {{-- Reduced gap to 4 --}}
                {{-- Total Employees Card --}}
                <div class="bg-indigo-600 text-white p-5 rounded-lg shadow-md transition duration-200 hover:shadow-lg"> {{-- Reduced padding to p-5 --}}
                    <h3 class="text-base font-medium mb-1 opacity-90">Total Employees</h3> {{-- Smaller text --}}
                    <p class="text-4xl font-extrabold">{{ $totalEmployees ?? 0 }}</p> {{-- Slightly smaller text --}}
                </div>

                {{-- Total Payroll Card --}}
                <div class="bg-green-600 text-white p-5 rounded-lg shadow-md transition duration-200 hover:shadow-lg"> {{-- Reduced padding to p-5 --}}
                    <h3 class="text-base font-medium mb-1 opacity-90">Total Payroll</h3> {{-- Smaller text --}}
                    <p class="text-4xl font-extrabold">₱{{ number_format($totalPayroll ?? 0, 2) }}</p> {{-- Slightly smaller text --}}
                </div>
            </div>
        </div>

        {{-- Quick-select buttons for semi-monthly payroll --}}
        <div class="mb-4 flex flex-wrap gap-2"> {{-- Reduced gap and margin --}}
            @php
                $now = \Carbon\Carbon::now();
                $firstHalfStart = $now->copy()->startOfMonth()->format('Y-m-d');
                $firstHalfEnd = $now->copy()->day(15)->format('Y-m-d');
                $secondHalfStart = $now->copy()->day(16)->format('Y-m-d');
                $secondHalfEnd = $now->copy()->endOfMonth()->format('Y-m-d');
            @endphp
            <a href="?start_date={{ $firstHalfStart }}&end_date={{ $firstHalfEnd }}" class="px-3 py-1.5 text-sm bg-indigo-100 text-indigo-700 font-medium rounded-md hover:bg-indigo-200 transition duration-150 border border-indigo-200">
                1st–15th ({{ $now->format('F Y') }})
            </a>
            <a href="?start_date={{ $secondHalfStart }}&end_date={{ $secondHalfEnd }}" class="px-3 py-1.5 text-sm bg-green-100 text-green-700 font-medium rounded-md hover:bg-green-200 transition duration-150 border border-green-200">
                16th–End ({{ $now->format('F Y') }})
            </a>
            <a href="?start_date={{ $now->copy()->startOfMonth()->format('Y-m-d') }}&end_date={{ $now->copy()->endOfMonth()->format('Y-m-d') }}" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 font-medium rounded-md hover:bg-gray-200 transition duration-150 border border-gray-200">
                Whole Month ({{ $now->format('F Y') }})
            </a>
        </div>

        {{-- Date Filter Form --}}
        <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end p-3 bg-gray-50 rounded-lg"> {{-- More compact form area --}}
            <div>
                <label for="start_date" class="block text-xs font-medium text-gray-600 mb-1">Start Date</label> {{-- Smaller label text --}}
                <input type="date" name="start_date" id="start_date" value="{{ $start ?? '' }}" class="border-gray-300 rounded-md px-2 py-1.5 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="end_date" class="block text-xs font-medium text-gray-600 mb-1">End Date</label> {{-- Smaller label text --}}
                <input type="date" name="end_date" id="end_date" value="{{ $end ?? '' }}" class="border-gray-300 rounded-md px-2 py-1.5 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 text-sm rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
        </form>

        @if($start && $end)
            @php $period = $currentPeriod; @endphp
            <div class="mb-4 flex items-center justify-between p-3 bg-white border-t border-b"> {{-- Sticky behavior removed, padding reduced --}}
                <div>
                    @if($period && $period->status === 'paid')
                        <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                            <i class="fas fa-check-circle mr-1"></i> Paid
                        </span>
                    @elseif($period && $period->status === 'unpaid')
                        <span class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                            <i class="fas fa-clock mr-1"></i> Unpaid
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full">
                            <i class="fas fa-info-circle mr-1"></i> Not generated
                        </span>
                    @endif
                </div>

                <div class="flex gap-3">
                    {{-- Generate Payroll Button --}}
                    <form method="POST" action="{{ route('payroll.generate.range') }}" class="">
                        @csrf
                        <input type="hidden" name="start_date" value="{{ $start }}">
                        <input type="hidden" name="end_date" value="{{ $end }}">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 text-sm rounded-md font-medium hover:bg-blue-700 transition duration-150 shadow-sm">
                            <i class="fas fa-calculator mr-1"></i> Generate
                        </button>
                    </form>

<<<<<<< HEAD
                    {{-- Done Payment Button --}}
=======
                    @if(!empty($period) && ($payrolls->count() ?? 0) > 0)
                        <a href="{{ route('payroll.download_pdf', ['start_date' => $start, 'end_date' => $end]) }}" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" target="_blank">
                            <i class="fas fa-file-pdf mr-2"></i> Download PDF
                        </a>
                    @endif

>>>>>>> 52c6ed0c543db19c12964109628fe2029b5e3559
                    @if(!empty($period) && ($payrolls->count() ?? 0) > 0 && $period->status !== 'paid')
                        <form method="POST" action="{{ route('payroll.pay-periods.complete', ['payPeriod' => $period->id]) }}">
                            @csrf
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 text-sm rounded-md font-medium hover:bg-indigo-700 transition duration-150 shadow-sm">
                                <i class="fas fa-dollar-sign mr-1"></i> Done Payment
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
        
        {{-- Payroll Records Table --}}
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Employee</th> {{-- Reduced px and py --}}
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Work Days</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Rate/Hour</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Gross Pay</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">SSS</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">GSIS</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">PhilHealth</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Other Deductions</th>
                        <th class="px-4 py-2 text-left text-xs font-extrabold text-indigo-700 uppercase tracking-wider">Net Pay</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($payrolls as $payslip)
                        <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif">
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $payslip->user->name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $payslip->present_days ?? 0 }} / {{ $payslip->work_days ?? 0 }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">₱{{ number_format(optional($payslip->user)->hourly_rate, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">₱{{ number_format($payslip->basic_pay + $payslip->overtime_pay, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-red-600">₱{{ number_format($payslip->sss, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-red-600">₱{{ number_format($payslip->gsis, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-red-600">₱{{ number_format($payslip->philhealth, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-red-600">₱{{ number_format($payslip->other_deductions ?? 0, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-indigo-700">₱{{ number_format($payslip->net_pay, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-center">
                                <a href="{{ route('payroll.show-payslip', ['employee' => $payslip->user->id, 'payPeriod' => $payslip->pay_period_id]) }}" class="text-indigo-600 hover:text-indigo-800 p-1.5 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150" title="View Payslip">
                                    <i class="fas fa-eye text-base"></i>
                                </a>
                                <button onclick="openEditDeductionsModal({{ $payslip->id }}, {{ $payslip->sss }}, {{ $payslip->gsis }}, {{ $payslip->philhealth }}, {{ $payslip->other_deductions ?? 0 }})" class="text-red-600 hover:text-red-800 p-1.5 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150" title="Set Other Deduction" {{ $period && $period->status === 'paid' ? 'disabled' : '' }}>
                                    <i class="fas fa-coins text-base"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-4 text-center text-base text-gray-500 bg-white">
                                <i class="fas fa-file-invoice-dollar mr-2"></i> No payroll records found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div id="editDeductionsModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full transform transition-all duration-300 scale-100"> {{-- Reduced max-width to sm and padding --}}
        <div class="flex justify-between items-center mb-4 border-b pb-2"> {{-- Reduced margin and padding --}}
            <h2 class="text-xl font-bold text-gray-800">⚙️ Edit Deductions</h2>
            <button onclick="closeEditDeductionsModal()" class="text-gray-500 hover:text-gray-900 transition duration-150 p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form id="editDeductionsForm" method="POST" action="" class="space-y-4"> {{-- Reduced space-y --}}
            @csrf
            @method('PUT')
            <input type="hidden" name="payslip_id" id="edit_deductions_payslip_id">

            {{-- Government Deductions Section --}}
            <div class="bg-indigo-50 p-4 rounded-lg space-y-3 border border-indigo-200"> {{-- Reduced padding and space-y --}}
                <h3 class="text-base font-bold text-indigo-700 flex items-center"><i class="fas fa-landmark mr-2"></i> Government Deductions</h3> {{-- Smaller text --}}
                <div>
                    <label for="sss_amount" class="block text-xs font-medium text-gray-700 mb-1">SSS (₱)</label>
                    <input type="number" min="0" step="0.01" name="sss" id="sss_amount" class="w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="gsis_amount" class="block text-xs font-medium text-gray-700 mb-1">GSIS (₱)</label>
                    <input type="number" min="0" step="0.01" name="gsis" id="gsis_amount" class="w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="philhealth_amount" class="block text-xs font-medium text-gray-700 mb-1">PhilHealth (₱)</label>
                    <input type="number" min="0" step="0.01" name="philhealth" id="philhealth_amount" class="w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            {{-- Other Deductions Section --}}
            <div class="bg-red-50 p-4 rounded-lg space-y-3 border border-red-200">
                <h3 class="text-base font-bold text-red-700 flex items-center"><i class="fas fa-minus-circle mr-2"></i> Other Deductions</h3>
                <div>
                    <label for="other_deductions_amount" class="block text-xs font-medium text-gray-700 mb-1">Other Deductions (₱)</label>
                    <input type="number" min="0" step="0.01" name="other_deductions" id="other_deductions_amount" class="w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditDeductionsModal()" class="px-4 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-1.5 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    // Existing script remains the same for functionality
    function openEditDeductionsModal(payslipId, sss, gsis, philhealth, other_deductions) {
        document.getElementById('edit_deductions_payslip_id').value = payslipId;

        document.getElementById('sss_amount').value = sss;
        document.getElementById('gsis_amount').value = gsis;
        document.getElementById('philhealth_amount').value = philhealth;
        document.getElementById('other_deductions_amount').value = other_deductions;

        const form = document.getElementById('editDeductionsForm');
        // NOTE: Ensure your Laravel route is correctly defined in web.php, e.g., Route::put('/payroll/payslips/{payslip}/deductions', 'PayrollController@updateDeductions')->name('payroll.payslips.deductions.update');
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