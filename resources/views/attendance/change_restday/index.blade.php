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
            <div class="mb-4" x-data="{ open: false, selectedRestDays: [], days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] }">
                <label for="requested_restdays" class="block text-gray-700 text-sm font-bold mb-2">Requested Restdays:</label>
                <div class="relative">
                    <button type="button" @click="open = !open" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <span x-text="selectedRestDays.length ? selectedRestDays.join(', ') : 'Select rest days'" class="block truncate"></span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                    <ul x-show="open" @click.away="open = false" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-40 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-y-auto scroll-smooth focus:outline-none sm:text-sm list-none" tabindex="-1" role="listbox" aria-labelledby="listbox-label">
                        <template x-for="(day, index) in days" :key="index">
                            <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white" :id="`requested-day-option-${index}`" role="option">
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="requested_restdays[]" :value="day" x-model="selectedRestDays" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    <span class="block font-normal" x-text="day"></span>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
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
