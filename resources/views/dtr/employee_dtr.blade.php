@extends('layouts.app')

@section('title', $employee->name . ' - DTR Logs')

@section('content')
<div class="container mx-auto px-6 py-6">
    <header class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-user-clock mr-3 text-indigo-600"></i> {{ $employee->name }}'s DTR Logs
        </h1>
        <a href="{{ route('dtr.employees.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i> Back to Employee List
        </a>
    </header>

    <form method="GET" action="{{ route('dtr.employee.show', ['employee' => $employee->id]) }}" class="mb-6 flex flex-wrap gap-4 items-end p-3 bg-gray-50 rounded-lg">
        {{-- Quick-select buttons for date ranges --}}
        @php
            $now = \Carbon\Carbon::now();
            $firstHalfStart = $now->copy()->startOfMonth()->format('Y-m-d');
            $firstHalfEnd = $now->copy()->day(15)->format('Y-m-d');
            $secondHalfStart = $now->copy()->day(16)->format('Y-m-d');
            $secondHalfEnd = $now->copy()->endOfMonth()->format('Y-m-d');
        @endphp
        <div class="flex gap-2">
            <a href="{{ route('dtr.employee.show', ['employee' => $employee->id, 'start_date' => $firstHalfStart, 'end_date' => $firstHalfEnd]) }}" class="px-3 py-1.5 text-sm bg-indigo-100 text-indigo-700 font-medium rounded-md hover:bg-indigo-200 transition duration-150 border border-indigo-200">
                1st–15th ({{ $now->format('F Y') }})
            </a>
            <a href="{{ route('dtr.employee.show', ['employee' => $employee->id, 'start_date' => $secondHalfStart, 'end_date' => $secondHalfEnd]) }}" class="px-3 py-1.5 text-sm bg-green-100 text-green-700 font-medium rounded-md hover:bg-green-200 transition duration-150 border border-green-200">
                16th–End ({{ $now->format('F Y') }})
            </a>
            <a href="{{ route('dtr.employee.show', ['employee' => $employee->id, 'start_date' => $now->copy()->startOfMonth()->format('Y-m-d'), 'end_date' => $now->copy()->endOfMonth()->format('Y-m-d')]) }}" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 font-medium rounded-md hover:bg-gray-200 transition duration-150 border border-gray-200">
                Whole Month ({{ $now->format('F Y') }})
            </a>
        </div>

        <div>
            <label for="start_date" class="block text-xs font-medium text-gray-600 mb-1">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}" class="border-gray-300 rounded-md px-2 py-1.5 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="end_date" class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}" class="border-gray-300 rounded-md px-2 py-1.5 text-sm shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="attendance_date" class="block text-xs font-medium text-gray-600 mb-1">Specific Date:</label>
            <input type="date" name="date" id="attendance_date" value="{{ $selectedDate->format('Y-m-d') }}" class="border-gray-300 rounded-md px-2 py-1.5 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 text-sm rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
            <i class="fas fa-filter mr-1"></i> Filter
        </button>
    </form>

    <div class="bg-white rounded-lg shadow-xl p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">
            DTR Logs for <span class="text-indigo-600">
                @if($startDate && $endDate)
                    {{ $startDate->format('F d, Y') }} to {{ $endDate->format('F d, Y') }}
                @else
                    {{ $selectedDate->format('F d, Y') }}
                @endif
            </span>
        </h2>

        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Morning Time In</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Morning Time Out</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Afternoon Time In</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Afternoon Time Out</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">Overtime Time In</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">Overtime Time Out</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Regular Work Hours</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">Overtime Hours</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">Total Work Hours</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($records as $record)
                        <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif">
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $record->date->format('M d, Y') }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">{{ $record->time_in_2 ? \Carbon\Carbon::parse($record->time_in_2)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">{{ $record->time_out_2 ? \Carbon\Carbon::parse($record->time_out_2)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">{{ $record->overtime_time_in ? \Carbon\Carbon::parse($record->overtime_time_in)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">{{ $record->overtime_time_out ? \Carbon\Carbon::parse($record->overtime_time_out)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-gray-700 text-center">{{ $record->formatted_regular_work_hours }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-indigo-700 text-center">{{ round($record->overtime_hours, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-indigo-700 text-center">{{ round($record->total_work_hours, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize
                                    {{ $record->status === 'present' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $record->status === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $record->status === 'absent' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $record->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-base text-gray-500 bg-white">
                                <i class="fas fa-calendar-times mr-2"></i> No attendance records found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
