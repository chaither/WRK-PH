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
                    <p class="text-4xl font-extrabold">₱{{ number_format($totalNetPay ?? 0, 2) }}</p> {{-- Slightly smaller text --}}
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
            <a href="?start_date={{ $firstHalfStart }}&end_date={{ $firstHalfEnd }}" class="px-3 py-1.5 text-sm bg-indigo-100 text-indigo-700 font-medium rounded-md hover:bg-indigo-200 transition duration-150 border border-indigo-200 {{ request()->input('start_date') === $firstHalfStart && request()->input('end_date') === $firstHalfEnd ? 'bg-indigo-200' : '' }}">
                1st–15th ({{ $now->format('F Y') }})
            </a>
            <a href="?start_date={{ $secondHalfStart }}&end_date={{ $secondHalfEnd }}" class="px-3 py-1.5 text-sm bg-green-100 text-green-700 font-medium rounded-md hover:bg-green-200 transition duration-150 border border-green-200 {{ request()->input('start_date') === $secondHalfStart && request()->input('end_date') === $secondHalfEnd ? 'bg-green-200' : '' }}">
                16th–End ({{ $now->format('F Y') }})
            </a>
            <a href="?start_date={{ $now->copy()->startOfMonth()->format('Y-m-d') }}&end_date={{ $now->copy()->endOfMonth()->format('Y-m-d') }}" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 font-medium rounded-md hover:bg-gray-200 transition duration-150 border border-gray-200 {{ request()->input('start_date') === $now->copy()->startOfMonth()->format('Y-m-d') && request()->input('end_date') === $now->copy()->endOfMonth()->format('Y-m-d') ? 'bg-gray-200' : '' }}">
                Whole Month ({{ $now->format('F Y') }})
            </a>
        </div>

        {{-- Date Filter Form --}}
        <form method="GET" class="mb-6 flex flex-wrap gap-2 items-end p-3 bg-gray-50 rounded-lg sm:flex-nowrap sm:gap-4"> {{-- More compact form area, allow wrapping on small screens --}}
            <div class="w-full sm:w-auto">
                <label for="start_date" class="block text-xs font-medium text-gray-600 mb-1">Start Date</label> {{-- Smaller label text --}}
                <input type="date" name="start_date" id="start_date" value="{{ $start ?? '' }}" class="w-full border-gray-300 rounded-md px-2 py-1.5 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="w-full sm:w-auto">
                <label for="end_date" class="block text-xs font-medium text-gray-600 mb-1">End Date</label> {{-- Smaller label text --}}
                <input type="date" name="end_date" id="end_date" value="{{ $end ?? '' }}" class="w-full border-gray-300 rounded-md px-2 py-1.5 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit" class="w-full sm:w-auto bg-indigo-600 text-white px-4 py-1.5 text-sm rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
        </form>

        @php $period = $currentPeriod; @endphp
        <div class="mb-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between p-3 bg-white border-t border-b gap-3"> {{-- Sticky behavior removed, padding reduced, flex-col on small screens --}}
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

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" onclick="openGovernmentContributionModal()" class="w-full sm:flex-1 bg-orange-600 text-white px-4 py-1.5 h-10 text-sm rounded-md font-medium hover:bg-orange-700 transition duration-150 shadow-sm flex items-center justify-center">
                    <i class="fas fa-hands-helping mr-1"></i> Manage Govt. Contributions
                </button>
                <button type="button" onclick="openPayrollScheduleModal()" class="w-full sm:flex-1 bg-purple-600 text-white px-4 py-1.5 h-10 text-sm rounded-md font-medium hover:bg-purple-700 transition duration-150 shadow-sm flex items-center justify-center">
                    <i class="fas fa-calendar-alt mr-1"></i> Manage Payroll Schedules
                </button>
                {{-- Generate Payroll Button --}}
                <form method="POST" action="{{ route('payroll.generate.range') }}" class="w-full sm:flex-1">
                    @csrf
                    <input type="hidden" name="start_date" value="{{ $start }}">
                    <input type="hidden" name="end_date" value="{{ $end }}">
                    @if($period && in_array($period->status, ['unpaid', 'paid']))
                        <button type="submit" name="force_regenerate" value="true" class="w-full bg-orange-600 text-white px-4 py-1.5 h-10 text-sm rounded-md font-medium hover:bg-orange-700 transition duration-150 shadow-sm flex items-center justify-center">
                            <i class="fas fa-redo mr-1"></i> Regenerate Payroll
                        </button>
                    @else
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-1.5 h-10 text-sm rounded-md font-medium hover:bg-blue-700 transition duration-150 shadow-sm flex items-center justify-center">
                            <i class="fas fa-calculator mr-1"></i> Generate Payroll
                        </button>
                    @endif
                </form>

                {{-- Download PDF Button --}}
                @if(!empty($period) && ($payrolls->count() ?? 0) > 0)
                    <a href="{{ route('payroll.download_pdf', ['start_date' => $start, 'end_date' => $end]) }}" class="w-full sm:flex-1 bg-red-600 text-white px-4 py-1.5 h-10 text-sm rounded-md font-medium hover:bg-red-700 transition duration-150 shadow-sm flex items-center justify-center" target="_blank">
                        <i class="fas fa-file-pdf mr-2"></i> Download PDF
                    </a>
                @endif

                {{-- Done Payment Button --}}
                @if(!empty($period) && ($payrolls->count() ?? 0) > 0 && $period->status !== 'paid')
                    <form method="POST" action="{{ route('payroll.pay-periods.complete', ['payPeriod' => $period->id]) }}" class="w-full sm:flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-1.5 h-10 text-sm rounded-md font-medium hover:bg-indigo-700 transition duration-150 shadow-sm flex items-center justify-center">
                            <i class="fas fa-dollar-sign mr-1"></i> Done Payment
                        </button>
                    </form>
                @endif
            </div>
        </div>
        
        {{-- Payroll Records Table --}}
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Employee</th> {{-- Reduced px and py --}}
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Work Days</th>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Work Hours</th>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Rate/Hour</th>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Gross Pay</th>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Deductions</th>
                        <th class="px-3 py-2 text-left text-xs font-extrabold text-indigo-700 uppercase tracking-wider hidden sm:table-cell">Net Pay</th>
                        <th class="px-3 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($payrolls as $payslip)
                        @php
                            $details = is_array($payslip->details) ? $payslip->details : (json_decode($payslip->details, true) ?? []);
                            $sss = $details['sss_deduction'] ?? $details['sss'] ?? 0;
                            $phil = $details['philhealth_deduction'] ?? $details['philhealth'] ?? 0;
                            $pagibig = $details['pagibig_deduction'] ?? $details['pagibig'] ?? 0;
                            $other = $details['other_deductions'] ?? $details['other_deduction'] ?? 0;
                            $componentsTotal = $sss + $phil + $pagibig + $other;
                        @endphp
                        <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif">
                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                <div class="flex flex-col">
                                    <span>{{ $payslip->user->name }}</span>
                                    <div class="sm:hidden text-xs text-gray-700 mt-1 space-y-1">
                                        <p><span class="font-medium">Work Days:</span> {{ $details['present_days'] ?? 0 }} / {{ $details['expected_working_days_in_period'] ?? 0 }}</p>
                                        <p><span class="font-medium">Work Hours:</span> {{ round($payslip->total_hours_worked ?? 0, 2) }}</p>
                                        <p><span class="font-medium">Rate/Hour:</span> ₱{{ number_format($details['hourly_rate_computed'] ?? 0, 2) }}</p>
                                        <p><span class="font-medium">Gross Pay:</span> ₱{{ number_format($payslip->gross_pay, 2) }}</p>
                                        <p><span class="font-medium">Deductions:</span>
                                            @if($componentsTotal > 0)
                                                @if($sss > 0)
                                                    <div class="text-xs text-gray-700">SSS: <span class="text-red-600">₱{{ number_format($sss, 2) }}</span></div>
                                                @endif
                                                @if($phil > 0)
                                                    <div class="text-xs text-gray-700">PhilHealth: <span class="text-red-600">₱{{ number_format($phil, 2) }}</span></div>
                                                @endif
                                                @if($pagibig > 0)
                                                    <div class="text-xs text-gray-700">Pag-IBIG: <span class="text-red-600">₱{{ number_format($pagibig, 2) }}</span></div>
                                                @endif
                                                @if($other > 0)
                                                    <div class="text-xs text-gray-700">Other: <span class="text-red-600">₱{{ number_format($other, 2) }}</span></div>
                                                @endif
                                                <div class="text-xs font-semibold mt-1">Total: <span class="text-red-600">₱{{ number_format($payslip->deductions, 2) }}</span></div>
                                            @else
                                                <span class="text-red-600">₱{{ number_format($payslip->deductions, 2) }}</span>
                                            @endif
                                        </p>
                                        <p class="font-bold mt-2">Net Pay: ₱{{ number_format($payslip->net_pay, 2) }}</p>
                                        <div class="flex flex-col sm:flex-row justify-end mt-3 gap-2">
                                            <a href="{{ route('payroll.show-payslip', ['employee' => $payslip->user->id, 'payPeriod' => $payslip->pay_period_id]) }}" class="text-indigo-600 hover:text-indigo-800 p-1.5 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150 w-full sm:w-auto" title="View Payslip">
                                                <i class="fas fa-eye text-base mr-1"></i> View Payslip
                                            </a>
                                            <button onclick="openEditDeductionsModal('{{ $payslip->id }}', '{{ $payslip->deductions }}')" class="text-red-600 hover:text-red-800 p-1.5 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150 w-full sm:w-auto" title="Set Deductions" {{ $period && $period->status === 'paid' ? 'disabled' : '' }}>
                                                <i class="fas fa-coins text-base mr-1"></i> Set Deductions
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 hidden sm:table-cell">{{ $details['present_days'] ?? 0 }} / {{ $details['expected_working_days_in_period'] ?? 0 }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 hidden sm:table-cell">{{ round($payslip->total_hours_worked ?? 0, 2) }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 hidden sm:table-cell">₱{{ number_format($details['hourly_rate_computed'] ?? 0, 2) }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 hidden sm:table-cell">₱{{ number_format($payslip->gross_pay, 2) }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm hidden sm:table-cell">
                                @if($componentsTotal > 0)
                                    @if($sss > 0)
                                        <div class="text-xs text-gray-700">SSS: <span class="text-red-600">₱{{ number_format($sss, 2) }}</span></div>
                                    @endif
                                    @if($phil > 0)
                                        <div class="text-xs text-gray-700">PhilHealth: <span class="text-red-600">₱{{ number_format($phil, 2) }}</span></div>
                                    @endif
                                    @if($pagibig > 0)
                                        <div class="text-xs text-gray-700">Pag-IBIG: <span class="text-red-600">₱{{ number_format($pagibig, 2) }}</span></div>
                                    @endif
                                    @if($other > 0)
                                        <div class="text-xs text-gray-700">Other: <span class="text-red-600">₱{{ number_format($other, 2) }}</span></div>
                                    @endif
                                    <div class="text-xs font-semibold mt-1">Total: <span class="text-red-600">₱{{ number_format($payslip->deductions, 2) }}</span></div>
                                @else
                                    <span class="text-red-600">₱{{ number_format($payslip->deductions, 2) }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-indigo-700 hidden sm:table-cell">₱{{ number_format($payslip->net_pay, 2) }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-center hidden sm:table-cell">
                                <a href="{{ route('payroll.show-payslip', ['employee' => $payslip->user->id, 'payPeriod' => $payslip->pay_period_id]) }}" class="text-indigo-600 hover:text-indigo-800 p-1.5 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150" title="View Payslip">
                                    <i class="fas fa-eye text-base"></i>
                                </a>
                                <button onclick="openEditDeductionsModal('{{ $payslip->id }}', '{{ $payslip->deductions }}')" class="text-red-600 hover:text-red-800 p-1.5 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150" title="Set Deductions" {{ $period && $period->status === 'paid' ? 'disabled' : '' }}>
                                    <i class="fas fa-coins text-base"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-3 text-center text-base text-gray-500 bg-white"> {{-- Reduced px and py --}}
                                <i class="fas fa-file-invoice-dollar mr-2"></i> No payroll records found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Edit Deductions Modal (further compacting) --}}
        {{-- The payroll schedule and government contributions modals will be handled in separate steps --}}
    </div>
</div>
@endsection

@push('modals')
@include('components.payroll_schedule_modal')
@include('components.government_contribution_modal')
<div id="editDeductionsModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50 p-2"> {{-- Reduced overall padding --}}
    <div class="bg-white rounded-xl shadow-2xl p-4 max-w-xs sm:max-w-2xl w-full max-h-screen-70 overflow-y-auto transform transition-all duration-300 scale-100"> {{-- Reduced max-width and padding --}}
        <div class="flex justify-between items-center mb-3 border-b pb-2"> {{-- Reduced margin and padding --}}
            <h2 class="text-lg font-bold text-gray-800">⚙️ Edit Deductions</h2> {{-- Smaller text --}}
            <button onclick="closeEditDeductionsModal()" class="text-gray-500 hover:text-gray-900 transition duration-150 p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-base"></i> {{-- Smaller icon --}}
            </button>
        </div>
        <form id="editDeductionsForm" method="POST" action="" class="space-y-3"> {{-- Reduced space-y --}}
            @csrf
            @method('PUT')
            <input type="hidden" name="payslip_id" id="edit_deductions_payslip_id">

            {{-- Total Deductions Section --}}
            <div class="bg-red-50 p-3 rounded-lg space-y-2 border border-red-200"> {{-- Reduced padding and space-y --}}
                <h3 class="text-sm font-bold text-red-700 flex items-center"><i class="fas fa-minus-circle mr-2"></i> Total Deductions</h3> {{-- Smaller text --}}
                <div>
                    <label for="deductions_amount" class="block text-xs font-medium text-gray-700 mb-1">Total Deductions (₱)</label>
                    <input type="number" min="0" step="0.01" name="deductions" id="deductions_amount" class="w-full px-2 py-1.5 border border-gray-300 rounded-md text-sm shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditDeductionsModal()" class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                    Cancel
                </button>
                <button type="submit" class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
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
    function openEditDeductionsModal(payslipId, totalDeductions) {
        document.getElementById('edit_deductions_payslip_id').value = payslipId;
        document.getElementById('deductions_amount').value = totalDeductions;

        const form = document.getElementById('editDeductionsForm');
        form.action = "{{ route('payroll.payslips.update-deductions', '_PAYSLIP_ID_') }}".replace('_PAYSLIP_ID_', payslipId);

        document.getElementById('editDeductionsModal').classList.remove('hidden');
        document.getElementById('editDeductionsModal').classList.add('flex');
    }

    function closeEditDeductionsModal() {
        document.getElementById('editDeductionsModal').classList.add('hidden');
        document.getElementById('editDeductionsModal').classList.remove('flex');
    }
</script>
@endpush