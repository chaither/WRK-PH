@extends('layouts.app')

@section('title', 'DTR Management')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold mb-6">DTR Management - {{ now()->format('F d, Y') }}</h2>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-green-100 p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-green-800">Present</h3>
                <p class="text-3xl font-bold text-green-600">{{ $presentCount }}</p>
            </div>

            <div class="bg-yellow-100 p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-yellow-800">Late</h3>
                <p class="text-3xl font-bold text-yellow-600">{{ $lateCount }}</p>
            </div>

            <div class="bg-red-100 p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-red-800">Absent</h3>
                <p class="text-3xl font-bold text-red-600">{{ $absentCount }}</p>
            </div>
        </div>

        <!-- DTR Records Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Employee</th>
                        <th class="px-4 py-2">Time In</th>
                        <th class="px-4 py-2">Time Out</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Work Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $record->user->name }}</td>
                            <td class="px-4 py-2 text-center">{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 text-center">{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-1 rounded-full text-xs capitalize
                                    {{ $record->status === 'present' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $record->status === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $record->status === 'absent' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $record->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">{{ $record->calculateWorkHours() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center text-gray-500">No records found for today</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection