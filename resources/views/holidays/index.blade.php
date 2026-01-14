@extends('layouts.app')

@section('content')
<div class="mx-6 py-6">
    <h1 class="text-3xl font-bold text-white mb-6">Holiday Management</h1>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <button onclick="openImportPhilippineHolidaysModal()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-150">
                <i class="fas fa-download mr-2"></i> Import Philippine Holidays
            </button>
            <button onclick="openAddHolidayModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition duration-150">
                <i class="fas fa-plus mr-2"></i> Add New Holiday
            </button>
        </div>

        <div class="inline-flex rounded-md shadow-sm border border-gray-200 bg-white overflow-hidden" role="group" aria-label="Holiday view toggle">
            <button id="holidayTableToggle"
                type="button"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium text-white bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-list mr-1.5"></i> Table View
            </button>
            <button id="holidayCalendarToggle"
                type="button"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-calendar-alt mr-1.5"></i> Calendar View
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Table view --}}
    <div id="holidayTableView" class="bg-white shadow-md rounded-3xl overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Date
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">
                        Type
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Rate Multiplier
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                @php $currentMonthLabel = null; @endphp
                @forelse ($holidays as $holiday)
                @php
                    $monthLabel = $holiday->date->format('F Y');
                @endphp
                @if ($monthLabel !== $currentMonthLabel)
                    @php $currentMonthLabel = $monthLabel; @endphp
                    <tr class="bg-gray-50">
                        <td colspan="5" class="px-5 py-3 border-b border-gray-200 text-sm font-semibold text-gray-700">
                            {{ $currentMonthLabel }}
                        </td>
                    </tr>
                @endif
                <tr x-data="{ open: false }" class="mobile-accordion hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <button @click="open = !open" class="flex justify-between items-center w-full focus:outline-none sm:cursor-default py-2">
                            <div>
                                <div class="font-medium text-gray-900">{{ $holiday->date->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $holiday->name }}</div>
                            </div>
                            <svg x-show="!open" class="w-4 h-4 sm:hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <svg x-show="open" class="w-4 h-4 sm:hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm hidden sm:table-cell">
                        {{ $holiday->name }}
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm hidden sm:table-cell">
                        {{ ucfirst(str_replace('_', ' ', $holiday->type)) }}
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm hidden sm:table-cell">
                        {{ $holiday->rate_multiplier }}x
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm hidden sm:table-cell">
                        <button onclick="openEditHolidayModal({{ $holiday->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                        <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this holiday?');" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                        No holidays found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Calendar view --}}
    @php
        $holidayEvents = $holidays->map(function ($holiday) {
            return [
                'id' => $holiday->id,
                'date' => $holiday->date->format('Y-m-d'),
                'name' => $holiday->name,
                'type' => $holiday->type,
                'rate_multiplier' => $holiday->rate_multiplier,
            ];
        })->values();
    @endphp

    <div id="holidayCalendarView" class="mt-4 bg-white shadow-md rounded-3xl p-4 hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 id="holidayCalendarMonthLabel" class="text-xl font-semibold text-gray-800">Calendar</h2>
                <p class="text-xs text-gray-500 mt-1">
                    Click on a holiday date to quickly edit it, or on an empty day to add a new holiday.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button id="holidayTodayBtn" type="button" class="px-3 py-1.5 text-xs sm:text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Today
                </button>
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <button id="holidayPrevMonthBtn" type="button" class="px-3 py-1.5 text-xs sm:text-sm rounded-l-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button id="holidayNextMonthBtn" type="button" class="px-3 py-1.5 text-xs sm:text-sm rounded-r-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-7 text-center text-xs font-semibold text-gray-500 border-b pb-2 mb-2">
            <div>Sun</div>
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
        </div>
        <div id="holidayCalendarGrid" class="grid grid-cols-7 gap-1 sm:gap-2 text-xs sm:text-sm">
            {{-- JS will render days here --}}
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-gray-500">
            <div class="flex items-center gap-1">
                <span class="inline-block w-3 h-3 rounded bg-red-100 border border-red-300"></span>
                <span>Regular Holiday</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="inline-block w-3 h-3 rounded bg-blue-100 border border-blue-300"></span>
                <span>Special Non‑Working</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="inline-block w-3 h-3 rounded border border-gray-300"></span>
                <span>Non‑holiday day</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
    @include('components.holiday_modal')
    @include('components.import_philippine_holidays_modal')
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableView = document.getElementById('holidayTableView');
        const calendarView = document.getElementById('holidayCalendarView');
        const tableToggle = document.getElementById('holidayTableToggle');
        const calendarToggle = document.getElementById('holidayCalendarToggle');

        if (!tableView || !calendarView || !tableToggle || !calendarToggle) {
            return;
        }

        function setActiveView(view) {
            const isTable = view === 'table';
            tableView.classList.toggle('hidden', !isTable);
            calendarView.classList.toggle('hidden', isTable);

            if (isTable) {
                tableToggle.classList.remove('bg-white', 'text-gray-700');
                tableToggle.classList.add('bg-blue-600', 'text-white');
                calendarToggle.classList.remove('bg-blue-600', 'text-white');
                calendarToggle.classList.add('bg-white', 'text-gray-700');
            } else {
                calendarToggle.classList.remove('bg-white', 'text-gray-700');
                calendarToggle.classList.add('bg-blue-600', 'text-white');
                tableToggle.classList.remove('bg-blue-600', 'text-white');
                tableToggle.classList.add('bg-white', 'text-gray-700');
            }
        }

        tableToggle.addEventListener('click', function () {
            setActiveView('table');
        });
        calendarToggle.addEventListener('click', function () {
            setActiveView('calendar');
        });

        // --- Calendar rendering ---
        const holidays = @json($holidayEvents);
        const monthLabel = document.getElementById('holidayCalendarMonthLabel');
        const grid = document.getElementById('holidayCalendarGrid');
        const prevBtn = document.getElementById('holidayPrevMonthBtn');
        const nextBtn = document.getElementById('holidayNextMonthBtn');
        const todayBtn = document.getElementById('holidayTodayBtn');

        if (!grid || !monthLabel || !prevBtn || !nextBtn || !todayBtn) {
            // Safety: do not proceed if any calendar element is missing
            setActiveView('table');
            return;
        }

        // Index holidays by date string (YYYY-MM-DD)
        const holidayByDate = holidays.reduce((acc, h) => {
            const date = h.date;
            if (!acc[date]) acc[date] = [];
            acc[date].push(h);
            return acc;
        }, {});

        let current = (function () {
            const d = new Date();
            // Normalize to first of month
            return new Date(d.getFullYear(), d.getMonth(), 1);
        })();

        function formatMonthLabel(date) {
            return date.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        }

        function formatDateKey(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function buildCalendar(date) {
            const year = date.getFullYear();
            const month = date.getMonth(); // 0-based

            const firstOfMonth = new Date(year, month, 1);
            const startDayOfWeek = firstOfMonth.getDay(); // 0 (Sun) - 6 (Sat)

            const daysInMonth = new Date(year, month + 1, 0).getDate();

            // Determine how many days from previous month to show
            const prevMonthDays = startDayOfWeek; // number of leading blanks

            // We render a 6 x 7 grid (42 cells) to keep height stable
            const totalCells = 42;

            grid.innerHTML = '';
            monthLabel.textContent = formatMonthLabel(date);

            // Base date at the first cell
            const firstCellDate = new Date(year, month, 1 - prevMonthDays);

            for (let i = 0; i < totalCells; i++) {
                const cellDate = new Date(firstCellDate);
                cellDate.setDate(firstCellDate.getDate() + i);

                const key = formatDateKey(cellDate);
                const isCurrentMonth = cellDate.getMonth() === month;
                const isToday = (function () {
                    const now = new Date();
                    return cellDate.getFullYear() === now.getFullYear() &&
                        cellDate.getMonth() === now.getMonth() &&
                        cellDate.getDate() === now.getDate();
                })();

                const holidaysForDay = holidayByDate[key] || [];

                const cell = document.createElement('button');
                cell.type = 'button';
                cell.className = [
                    'relative flex flex-col items-start justify-start p-1.5 sm:p-2 rounded-md border text-left transition-colors duration-150 min-h-[3.2rem] sm:min-h-[4rem]',
                    isCurrentMonth ? 'bg-white border-gray-200 hover:bg-blue-50' : 'bg-gray-50 border-gray-100 text-gray-400',
                    isToday ? 'ring-1 ring-blue-500' : '',
                ].join(' ').trim();

                const dayNumber = document.createElement('div');
                dayNumber.className = 'text-xs sm:text-sm font-semibold mb-0.5';
                dayNumber.textContent = cellDate.getDate();
                cell.appendChild(dayNumber);

                if (holidaysForDay.length > 0) {
                    holidaysForDay.forEach(function (h) {
                        const badge = document.createElement('div');
                        const isRegular = h.type === 'regular';
                        badge.className = [
                            'mt-0.5 w-full inline-flex items-center rounded px-1.5 py-0.5 text-[0.65rem] sm:text-[0.7rem] font-medium truncate',
                            isRegular
                                ? 'bg-red-100 text-red-800 border border-red-200'
                                : 'bg-blue-100 text-blue-800 border border-blue-200'
                        ].join(' ');
                        badge.textContent = h.name;
                        cell.appendChild(badge);
                    });

                    // If the day has holidays, clicking opens edit for first one
                    cell.addEventListener('click', function () {
                        const first = holidaysForDay[0];
                        if (first && typeof window.openEditHolidayModal === 'function') {
                            window.openEditHolidayModal(first.id);
                        }
                    });
                } else {
                    // Empty day: clicking opens add modal with preset date
                    cell.addEventListener('click', function () {
                        if (typeof window.openAddHolidayModal === 'function') {
                            window.openAddHolidayModal();
                            const dateInput = document.getElementById('holiday_date');
                            if (dateInput) {
                                dateInput.value = key;
                            }
                        }
                    });
                }

                grid.appendChild(cell);
            }
        }

        prevBtn.addEventListener('click', function () {
            current.setMonth(current.getMonth() - 1);
            current.setDate(1);
            buildCalendar(current);
        });

        nextBtn.addEventListener('click', function () {
            current.setMonth(current.getMonth() + 1);
            current.setDate(1);
            buildCalendar(current);
        });

        todayBtn.addEventListener('click', function () {
            const now = new Date();
            current = new Date(now.getFullYear(), now.getMonth(), 1);
            buildCalendar(current);
        });

        // Initialize default view and calendar
        setActiveView('table');
        buildCalendar(current);
    });
</script>
@endpush
