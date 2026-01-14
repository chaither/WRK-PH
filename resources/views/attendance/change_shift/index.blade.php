@extends('layouts.app')

@section('content')
<div class="mx-6 py-6">
    <h1 class="text-2xl font-semibold text-white mb-4">Change Shift Request</h1>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-3xl p-6">
        <form action="{{ route('attendance.change-shift.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="current_shift" class="block text-gray-700 text-sm font-bold mb-2">Current Shift:</label>
                <input type="text" id="current_shift" name="current_shift" class="shadow appearance-none border rounded w-full py-2 px-3 text-black leading-tight focus:outline-none focus:shadow-outline" value="{{ $currentShift }}" disabled>
            </div>
            <div class="mb-4">
                <label for="requested_shift" class="block text-gray-700 text-sm font-bold mb-2">Requested Shift:</label>
                <select name="requested_shift" id="requested_shift" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Select a shift</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-6">
                <label for="reason" class="block text-gray-700 text-sm font-bold mb-2">Reason:</label>
                <textarea name="reason" id="reason" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-black leading-tight focus:outline-none focus:shadow-outline" required></textarea>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Submit Request
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow-md rounded-3xl p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">My Pending Shift Change Requests</h2>
        @if ($changeShiftRequests->isEmpty())
            <p class="text-gray-600">No pending change shift requests.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Shift</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Shift</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-gray-900">
                    @foreach ($changeShiftRequests as $request)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $request->currentShift->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $request->requestedShift->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $request->reason }}</td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-3 py-1 rounded-full text-xs font-semibold {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($request->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($request->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
