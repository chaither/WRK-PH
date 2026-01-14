@extends('layouts.app')

@section('content')
<div class="mx-6 py-6">
    <h1 class="text-2xl font-semibold text-white mb-4">My No Bio Requests</h1>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li><span class="block sm:inline">{{ $error }}</span></li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-3xl p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Submit New No Bio Request</h2>
        <form action="{{ route('attendance.no-bio-request.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="date" class="block text-gray-700 text-sm font-bold mb-2">Date</label>
                <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-black leading-tight focus:outline-none focus:shadow-outline" id="date" name="date" value="{{ old('date') }}" required>
            </div>
            <div class="mb-4">
                <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Type</label>
                <select class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="type" name="type" required>
                    <option value="">Select Type</option>
                    <option value="morning_in" {{ old('type') == 'morning_in' ? 'selected' : '' }}>Morning Time In</option>
                    <option value="morning_out" {{ old('type') == 'morning_out' ? 'selected' : '' }}>Morning Time Out</option>
                    <option value="afternoon_in" {{ old('type') == 'afternoon_in' ? 'selected' : '' }}>Afternoon Time In</option>
                    <option value="afternoon_out" {{ old('type') == 'afternoon_out' ? 'selected' : '' }}>Afternoon Time Out</option>
                    <option value="all_morning" {{ old('type') == 'all_morning' ? 'selected' : '' }}>All Morning</option>
                    <option value="all_afternoon" {{ old('type') == 'all_afternoon' ? 'selected' : '' }}>All Afternoon</option>
                    <option value="whole_day" {{ old('type') == 'whole_day' ? 'selected' : '' }}>Whole Day</option>
                </select>
            </div>
            <div id="time_in_field" class="mb-4 hidden">
                <label for="requested_time_in" class="block text-gray-700 text-sm font-bold mb-2">Requested Time In</label>
                <input type="time" class="shadow appearance-none border rounded w-full py-2 px-3 text-black leading-tight focus:outline-none focus:shadow-outline" id="requested_time_in" name="requested_time_in" value="{{ old('requested_time_in') }}">
            </div>
            <div id="time_out_field" class="mb-4 hidden">
                <label for="requested_time_out" class="block text-gray-700 text-sm font-bold mb-2">Requested Time Out</label>
                <input type="time" class="shadow appearance-none border rounded w-full py-2 px-3 text-black leading-tight focus:outline-none focus:shadow-outline" id="requested_time_out" name="requested_time_out" value="{{ old('requested_time_out') }}">
            </div>
            <div class="mb-6">
                <label for="reason" class="block text-gray-700 text-sm font-bold mb-2">Reason</label>
                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-black leading-tight focus:outline-none focus:shadow-outline" id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Submit Request
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow-md rounded-3xl p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">My Pending Requests</h2>
        @if ($noBioRequests->isEmpty())
            <p class="text-gray-600">No requests found.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Time In</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Time Out</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-gray-900">
                    @foreach ($noBioRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ \Carbon\Carbon::parse($request->date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ ucfirst(str_replace(['_in', '_out', 'all_', 'whole_'], [' In', ' Out', 'All ', 'Whole '], $request->type)) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $request->requested_time_in ? \Carbon\Carbon::parse($request->requested_time_in)->format('h:i A') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $request->requested_time_out ? \Carbon\Carbon::parse($request->requested_time_out)->format('h:i A') : '-' }}</td>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('type');
        const timeInField = document.getElementById('time_in_field');
        const timeOutField = document.getElementById('time_out_field');
        const requestedTimeInInput = document.getElementById('requested_time_in');
        const requestedTimeOutInput = document.getElementById('requested_time_out');

        function toggleTimeFields() {
            const selectedType = typeSelect.value;

            // Hide all and remove required attributes by default
            timeInField.classList.add('hidden');
            timeOutField.classList.add('hidden');
            requestedTimeInInput.removeAttribute('required');
            requestedTimeOutInput.removeAttribute('required');

            if (selectedType === 'morning_in' || selectedType === 'afternoon_in') {
                timeInField.classList.remove('hidden');
                requestedTimeInInput.setAttribute('required', 'required');
            } else if (selectedType === 'morning_out' || selectedType === 'afternoon_out') {
                timeOutField.classList.remove('hidden');
                requestedTimeOutInput.setAttribute('required', 'required');
            } else if (selectedType === 'all_morning') {
                timeInField.classList.remove('hidden');
                timeOutField.classList.remove('hidden');
                // These will be auto-filled by the backend based on shift, not user input
                requestedTimeInInput.removeAttribute('required'); 
                requestedTimeOutInput.removeAttribute('required'); 
            } else if (selectedType === 'all_afternoon') {
                timeInField.classList.remove('hidden');
                timeOutField.classList.remove('hidden');
                // These will be auto-filled by the backend based on shift, not user input
                requestedTimeInInput.removeAttribute('required'); 
                requestedTimeOutInput.removeAttribute('required'); 
            } else if (selectedType === 'whole_day') {
                timeInField.classList.remove('hidden');
                timeOutField.classList.remove('hidden');
                // These will be auto-filled by the backend based on shift, not user input
                requestedTimeInInput.removeAttribute('required'); 
                requestedTimeOutInput.removeAttribute('required'); 
            }
        }

        typeSelect.addEventListener('change', toggleTimeFields);

        // Initial call to set the correct state based on old('type') value
        toggleTimeFields();
    });
</script>
@endpush
