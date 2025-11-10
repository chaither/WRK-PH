@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Change Restday Request</h1>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('attendance.change-restday.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="current_restdays" class="block text-gray-700 text-sm font-bold mb-2">Current Restdays:</label>
                <input type="text" id="current_restdays" name="current_restdays" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ $currentRestdays }}" disabled>
            </div>
            <div class="mb-4">
                <label for="requested_restdays" class="block text-gray-700 text-sm font-bold mb-2">Requested Restdays:</label>
                <select name="requested_restdays" id="requested_restdays" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Select restdays</option>
                    @foreach($daysOfWeek as $day)
                        <option value="{{ $day }}">{{ $day }}</option>
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

    <div class="bg-white shadow-md rounded-lg p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">My Pending Restday Change Requests</h2>
        @if ($changeRestdayRequests->isEmpty())
            <p class="text-gray-600">No pending change restday requests.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Restdays</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Restdays</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($changeRestdayRequests as $request)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ implode(', ', $request->current_restdays) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ implode(', ', $request->requested_restdays) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $request->reason }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($request->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
