@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Change Shift Request</h1>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('attendance.change-shift.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="current_shift" class="block text-gray-700 text-sm font-bold mb-2">Current Shift:</label>
                <input type="text" id="current_shift" name="current_shift" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ $currentShift }}" disabled>
            </div>
            <div class="mb-4">
                <label for="requested_shift" class="block text-gray-700 text-sm font-bold mb-2">Requested Shift:</label>
                <select name="requested_shift" id="requested_shift" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Select a shift</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-6">
                <label for="reason" class="block text-gray-700 text-sm font-bold mb-2">Reason:</label>
                <textarea name="reason" id="reason" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
