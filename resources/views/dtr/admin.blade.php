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
        <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">
            Daily Attendance - <span class="text-indigo-600">{{ now()->format('F d, Y') }}</span>
        </h2>

        {{-- Statistics Cards (Consistent, bolder colors) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6"> {{-- Reduced gap --}}
            {{-- Present Card --}}
            <div class="bg-green-600 text-white p-4 rounded-lg shadow-md transition duration-200 hover:shadow-lg"> {{-- Bolder background, reduced padding --}}
                <h3 class="text-base font-medium mb-1 opacity-90 flex items-center"><i class="fas fa-user-check mr-2"></i> Present</h3>
                <p class="text-4xl font-extrabold">{{ $presentCount }}</p>
            </div>

            {{-- Late Card --}}
            <div class="bg-yellow-600 text-white p-4 rounded-lg shadow-md transition duration-200 hover:shadow-lg"> {{-- Bolder background, reduced padding --}}
                <h3 class="text-base font-medium mb-1 opacity-90 flex items-center"><i class="fas fa-hourglass-half mr-2"></i> Late</h3>
                <p class="text-4xl font-extrabold">{{ $lateCount }}</p>
            </div>

            {{-- Absent Card --}}
            <div class="bg-red-600 text-white p-4 rounded-lg shadow-md transition duration-200 hover:shadow-lg"> {{-- Bolder background, reduced padding --}}
                <h3 class="text-base font-medium mb-1 opacity-90 flex items-center"><i class="fas fa-user-slash mr-2"></i> Absent</h3>
                <p class="text-4xl font-extrabold">{{ $absentCount }}</p>
            </div>
        </div>

        {{-- DTR Records Table --}}
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"> {{-- Light gray header --}}
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Employee</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Time In</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Time Out</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">Work Hours</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($records as $record)
                        <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif"> {{-- Zebra striping and hover effect --}}
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $record->user->name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center">{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize
                                    {{ $record->status === 'present' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $record->status === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $record->status === 'absent' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $record->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-indigo-700 text-center">{{ $record->calculateWorkHours() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-base text-gray-500 bg-white">
                                <i class="fas fa-calendar-times mr-2"></i> No attendance records found for today.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection