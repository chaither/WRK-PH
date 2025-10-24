@extends('layouts.app')

@section('title', 'DTR Management')

@section('content')
<div class="container mx-auto px-6 py-6"> {{-- Consistent compact padding --}}
    <header class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-clock mr-3 text-indigo-600"></i> DTR Management
        </h1>
    </header>

    <div class="bg-white rounded-lg shadow-xl p-6"> {{-- Consistent card styling --}}
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-xl font-semibold text-gray-700">
                Daily Attendance - <span class="text-indigo-600">
                    {{ $today->format('F d, Y') }}
                </span>
            </h2>
            <a href="{{ route('dtr.employees.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-users mr-2"></i> List of Employees
            </a>
        </div>

        {{-- Statistics Cards (Consistent, bolder colors) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6"> {{-- Reduced gap --}}
            {{-- Present Card --}}
            <a href="{{ route('dtr.admin', ['status' => 'present']) }}" class="block bg-green-600 text-white p-4 rounded-lg shadow-md transition duration-200 hover:shadow-lg">
                <h3 class="text-base font-medium mb-1 opacity-90 flex items-center"><i class="fas fa-user-check mr-2"></i> Present</h3>
                <p class="text-4xl font-extrabold">{{ $presentCount }}</p>
            </a>

            {{-- Late Card --}}
            <a href="{{ route('dtr.admin', ['status' => 'late']) }}" class="block bg-yellow-600 text-white p-4 rounded-lg shadow-md transition duration-200 hover:shadow-lg">
                <h3 class="text-base font-medium mb-1 opacity-90 flex items-center"><i class="fas fa-hourglass-half mr-2"></i> Late</h3>
                <p class="text-4xl font-extrabold">{{ $lateCount }}</p>
            </a>

            {{-- Absent Card --}}
            <a href="{{ route('dtr.admin', ['status' => 'absent']) }}" class="block bg-red-600 text-white p-4 rounded-lg shadow-md transition duration-200 hover:shadow-lg">
                <h3 class="text-base font-medium mb-1 opacity-90 flex items-center"><i class="fas fa-user-slash mr-2"></i> Absent</h3>
                <p class="text-4xl font-extrabold">{{ $absentCount }}</p>
            </a>
        </div>

        {{-- Employee List Table --}}
        <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Employee List @if($filterStatus) ({{ ucfirst($filterStatus) }}) @endif</h2>
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Employee Name</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Morning Time In</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Morning Time Out</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Afternoon Time In</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Afternoon Time Out</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Work Hours</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif">
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $employee->name }}
                            </td>
                            @php
                                $dtrRecord = optional($employee->dtrRecords->first());
                            @endphp
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">
                                {{ $dtrRecord->time_in ? \Carbon\Carbon::parse($dtrRecord->time_in)->format('h:i A') : '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">
                                {{ $dtrRecord->time_out ? \Carbon\Carbon::parse($dtrRecord->time_out)->format('h:i A') : '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">
                                {{ $dtrRecord->time_in_2 ? \Carbon\Carbon::parse($dtrRecord->time_in_2)->format('h:i A') : '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">
                                {{ $dtrRecord->time_out_2 ? \Carbon\Carbon::parse($dtrRecord->time_out_2)->format('h:i A') : '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize
                                    {{ $dtrRecord->status === 'present' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $dtrRecord->status === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $dtrRecord->status === 'absent' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $dtrRecord->status ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-indigo-700 text-center">
                                {{ round($dtrRecord->work_hours ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-base text-gray-500 bg-white">
                                <i class="fas fa-users-slash mr-2"></i> No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection