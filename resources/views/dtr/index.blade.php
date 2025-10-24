@extends('layouts.app')

@section('title', 'Daily Time Record')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Clock In/Out Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Time Record - {{ now()->format('F d, Y') }}</h2>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="text-center">
                <form action="{{ route('dtr.clock-in') }}" method="POST">
                    @csrf
                    <button type="submit" 
                        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 w-full 
                            {{ ($dtrRecord && $dtrRecord->time_in && $dtrRecord->time_out && $dtrRecord->time_in_2) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ ($dtrRecord && $dtrRecord->time_in && $dtrRecord->time_out && $dtrRecord->time_in_2) ? 'disabled' : '' }}>
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Clock In
                    </button>
                    @if($dtrRecord)
                        @if($dtrRecord->time_in)
                            <p class="mt-2 text-gray-600">Morning Clock In: {{ \Carbon\Carbon::parse($dtrRecord->time_in)->format('h:i A') }}</p>
                        @endif
                        @if($dtrRecord->time_in_2)
                            <p class="mt-2 text-gray-600">Afternoon Clock In: {{ \Carbon\Carbon::parse($dtrRecord->time_in_2)->format('h:i A') }}</p>
                        @endif
                    @endif
                </form>
            </div>

            <div class="text-center">
                <form action="{{ route('dtr.clock-out') }}" method="POST">
                    @csrf
                    <button type="submit" 
                        class="bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600 w-full 
                            {{ (!$dtrRecord || (!$dtrRecord->time_in && !$dtrRecord->time_in_2) || ($dtrRecord->time_out && $dtrRecord->time_out_2)) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ (!$dtrRecord || (!$dtrRecord->time_in && !$dtrRecord->time_in_2) || ($dtrRecord->time_out && $dtrRecord->time_out_2)) ? 'disabled' : '' }}>
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Clock Out
                    </button>
                    @if($dtrRecord)
                        @if($dtrRecord->time_out)
                            <p class="mt-2 text-gray-600">Morning Clock Out: {{ \Carbon\Carbon::parse($dtrRecord->time_out)->format('h:i A') }}</p>
                        @endif
                        @if($dtrRecord->time_out_2)
                            <p class="mt-2 text-gray-600">Afternoon Clock Out: {{ \Carbon\Carbon::parse($dtrRecord->time_out_2)->format('h:i A') }}</p>
                        @endif
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Monthly Records Table -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold mb-4">Monthly Records - {{ now()->format('F Y') }}</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Morning Time In</th>
                        <th class="px-4 py-2">Morning Time Out</th>
                        <th class="px-4 py-2">Afternoon Time In</th>
                        <th class="px-4 py-2">Afternoon Time Out</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Work Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyRecords as $record)
                        <tr class="border-b">
                            <td class="px-4 py-2 text-center">{{ $record->date->format('M d, Y') }}</td>
                            <td class="px-4 py-2 text-center">{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 text-center">{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 text-center">{{ $record->time_in_2 ? \Carbon\Carbon::parse($record->time_in_2)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 text-center">{{ $record->time_out_2 ? \Carbon\Carbon::parse($record->time_out_2)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-1 rounded-full text-xs capitalize
                                    {{ $record->status === 'present' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $record->status === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $record->status === 'absent' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $record->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">{{ round($record->work_hours, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-2 text-center text-gray-500">No records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection