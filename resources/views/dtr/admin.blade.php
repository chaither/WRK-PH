@extends('layouts.app')

@section('title', 'DTR Management')

@php
    use App\Helpers\TimeHelper;
@endphp

@section('content')
<div class="mx-6 py-6"> {{-- Consistent compact padding --}}
    <header class="mb-6">
        <h1 class="text-3xl font-bold text-white flex items-center">
            <i class="fas fa-clock mr-3 text-indigo-600"></i> DTR Management
        </h1>
    </header>

    <div class="bg-white rounded-3xl shadow-xl p-6"> {{-- Consistent card styling --}}
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-xl font-semibold text-gray-700">
                Daily Attendance
                @php
                    $isFiltered = (
                        request()->filled('start_date') ||
                        request()->filled('end_date') ||
                        request()->filled('employee_id') ||
                        request()->filled('status')
                    );
                @endphp

                @if($isFiltered)
                    - <span class="text-indigo-600">{{ $startDate->format('F d, Y') }} to {{ $endDate->format('F d, Y') }}</span>
                @else
                    - <span class="text-indigo-600">{{ Carbon\Carbon::today()->format('F d, Y') }}</span>
                @endif
            </h2>
            <a href="{{ route('dtr.employees.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-users mr-2"></i> List of Employees
            </a>
        </div>

        {{-- Statistics Cards (Consistent, bolder colors) --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6"> {{-- Reduced gap --}}
            {{-- Present Card --}}
            <a href="{{ route('dtr.admin', array_merge(request()->query(), ['status' => 'present'])) }}" class="block bg-green-600 text-white p-4 rounded-3xl shadow-md transition duration-200 hover:shadow-lg">
                <h3 class="text-base font-medium mb-1 opacity-90 flex items-center"><i class="fas fa-user-check mr-2"></i> Present</h3>
                <p class="text-4xl font-extrabold">{{ $presentCount }}</p>
            </a>

            {{-- Late Card --}}
            <a href="{{ route('dtr.admin', array_merge(request()->query(), ['status' => 'late'])) }}" class="block bg-yellow-600 text-white p-4 rounded-3xl shadow-md transition duration-200 hover:shadow-lg">
                <h3 class="text-base font-medium mb-1 opacity-90 flex items-center"><i class="fas fa-hourglass-half mr-2"></i> Late</h3>
                <p class="text-4xl font-extrabold">{{ $lateCount }}</p>
            </a>

            {{-- Half Day Card --}}
            <a href="{{ route('dtr.admin', array_merge(request()->query(), ['status' => 'half_day'])) }}" class="block bg-blue-600 text-white p-4 rounded-3xl shadow-md transition duration-200 hover:shadow-lg">
                <h3 class="text-base font-medium mb-1 opacity-90 flex items-center"><i class="fas fa-sun mr-2"></i> Half Day</h3>
                <p class="text-4xl font-extrabold">{{ $halfDayCount }}</p>
            </a>

            {{-- Absent Card --}}
            <a href="{{ route('dtr.admin', array_merge(request()->query(), ['status' => 'absent'])) }}" class="block bg-red-600 text-white p-4 rounded-3xl shadow-md transition duration-200 hover:shadow-lg">
                <h3 class="text-base font-medium mb-1 opacity-90 flex items-center"><i class="fas fa-user-slash mr-2"></i> Absent</h3>
                <p class="text-4xl font-extrabold">{{ $absentCount }}</p>
            </a>
        </div>

        {{-- Filter Buttons (Moved) --}}
        <div class="flex justify-end items-center mb-4">
            <button id="openFilterModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white {{ $isFiltered ? 'bg-orange-600 hover:bg-orange-700' : 'bg-indigo-600 hover:bg-indigo-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $isFiltered ? 'orange' : 'indigo' }}-500">
                <i class="fas fa-filter mr-2"></i> Open Filter
            </button>
            @if($isFiltered)
                <button id="clearAllFilters" class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-times mr-2"></i> Clear Filters
                </button>
            @endif
        </div>

        {{-- Filter Modal --}}
        <div id="filterModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 p-4 transition-opacity duration-300" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div id="filterModalContent" class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full opacity-0 scale-95 transform transition-all duration-300 ease-out">
                <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-3">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-filter mr-2 text-indigo-600"></i> Filter Daily Time Records
                    </h2>
                    <button id="closeFilterModal" class="text-gray-500 hover:text-gray-800 transition duration-150 p-2 rounded-full hover:bg-gray-100">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <form id="filterForm" action="{{ route('dtr.admin') }}" method="GET" class="space-y-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-black"
                               value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-black"
                               value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}">
                    </div>
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
                        <select name="employee_id" id="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-black">
                            <option value="">All Employees</option>
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}" {{ (string) $emp->id === (string) $selectedEmployeeId ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-black">
                            <option value="">All Statuses</option>
                            <option value="present" {{ $filterStatus === 'present' ? 'selected' : '' }}>Present</option>
                            <option value="late" {{ $filterStatus === 'late' ? 'selected' : '' }}>Late</option>
                            <option value="half_day" {{ $filterStatus === 'half_day' ? 'selected' : '' }}>Half Day</option>
                            <option value="absent" {{ $filterStatus === 'absent' ? 'selected' : '' }}>Absent</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Employee List Table --}}
        <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Employee List @if($filterStatus) ({{ ucfirst(str_replace('_', ' ', $filterStatus)) }}) @endif</h2>
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Employee Name</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Time In 1</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Time Out 1</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Time In 2</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Time Out 2</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">Overtime Time In</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">Overtime Time Out</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Regular Work Hours</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Total Work Hours</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($employeesToDisplay as $employee)
                        <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif">
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                <a href="{{ route('dtr.employee.show', $employee->id) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" class="text-indigo-600 hover:text-indigo-900">{{ $employee->name }}</a>
                            </td>
                            @php
                                $dtrRecord = optional($employee->dtrRecords->first());
                                $timeIn = $dtrRecord->time_in ? \Carbon\Carbon::parse($dtrRecord->time_in) : null;
                                $timeOut = $dtrRecord->time_out ? \Carbon\Carbon::parse($dtrRecord->time_out) : null;
                                $timeIn2 = $dtrRecord->time_in_2 ? \Carbon\Carbon::parse($dtrRecord->time_in_2) : null;
                                $timeOut2 = $dtrRecord->time_out_2 ? \Carbon\Carbon::parse($dtrRecord->time_out_2) : null;

                                $displayTimeIn1 = $timeIn ? TimeHelper::getTimeOfDay($timeIn) . ': ' . $timeIn->format('h:i A') : '-';
                                $displayTimeOut1 = $timeOut ? TimeHelper::getTimeOfDay($timeOut) . ': ' . $timeOut->format('h:i A') : '-';
                                $displayTimeIn2 = $timeIn2 ? TimeHelper::getTimeOfDay($timeIn2) . ': ' . $timeIn2->format('h:i A') : '-';
                                $displayTimeOut2 = $timeOut2 ? TimeHelper::getTimeOfDay($timeOut2) . ': ' . $timeOut2->format('h:i A') : '-';
                            @endphp
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $displayTimeIn1 }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $displayTimeOut1 }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $displayTimeIn2 }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $displayTimeOut2 }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-purple-600 font-semibold text-center">
                                {{ $dtrRecord->overtime_time_in ? \Carbon\Carbon::parse($dtrRecord->overtime_time_in)->format('h:i A') : '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-purple-600 font-semibold text-center">
                                {{ $dtrRecord->overtime_time_out ? \Carbon\Carbon::parse($dtrRecord->overtime_time_out)->format('h:i A') : '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-gray-900 text-center">
                                {{ $dtrRecord->formatted_regular_work_hours ?? '00:00:00' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize
                                    {{ $dtrRecord->status === 'present' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $dtrRecord->status === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $dtrRecord->status === 'absent' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $dtrRecord->status === 'half_day' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ !in_array($dtrRecord->status, ['present', 'late', 'absent', 'half_day']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ str_replace('_', ' ', $dtrRecord->status ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-indigo-700 text-center">
                                {{ $dtrRecord->formatted_total_work_hours ?? '00:00:00' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-4 text-center text-base text-gray-500 bg-white">
                                <i class="fas fa-users-slash mr-2"></i> No employees found for the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterModal = document.getElementById('filterModal');
        const filterModalContent = document.getElementById('filterModalContent');
        const openFilterModalBtn = document.getElementById('openFilterModal');
        const closeFilterModalBtn = document.getElementById('closeFilterModal');
        const clearFiltersBtn = document.getElementById('clearFilters'); // This is the old internal button, will be removed
        const clearAllFiltersBtn = document.getElementById('clearAllFilters'); // New external button
        const filterForm = document.getElementById('filterForm');

        function openModal() {
            filterModal.classList.remove('hidden');
            filterModal.classList.add('flex');
            setTimeout(() => {
                filterModal.classList.add('opacity-100');
                filterModalContent.classList.remove('opacity-0', 'scale-95');
                filterModalContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function closeModal() {
            filterModal.classList.remove('opacity-100');
            filterModalContent.classList.remove('opacity-100', 'scale-100');
            filterModalContent.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                filterModal.classList.add('hidden');
                filterModal.classList.remove('flex');
            }, 300); // Duration matches transition-all duration-300
        }

        openFilterModalBtn.addEventListener('click', openModal);
        closeFilterModalBtn.addEventListener('click', closeModal);

        // Close modal when clicking outside
        filterModal.addEventListener('click', function (event) {
            if (event.target === filterModal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // New external clear filters button event listener
        clearAllFiltersBtn.addEventListener('click', function () {
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.getElementById('employee_id').value = '';
            document.getElementById('status').value = '';
            filterForm.submit();
            // No need to close modal here as it's an external button
        });
    });
</script>
@endpush
