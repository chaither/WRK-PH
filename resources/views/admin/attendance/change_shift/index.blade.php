@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Review Change Shift Requests</h1>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        @if (empty($changeShiftRequests))
            <p class="text-gray-600">No pending change shift requests.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Shift</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Shift</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($changeShiftRequests as $request)
                        <tr class="mobile-accordion" x-data="{ open: false, requestId: '{{ $request->id }}', employeeName: '{{ $request->user->name }}', currentShift: '{{ $request->currentShift->name }}', requestedShift: '{{ $request->requestedShift->name }}', reason: '{{ $request->reason }}' }" @click="open = !open">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-blue-600 hover:text-blue-800 hover:underline cursor-pointer flex justify-between items-center">
                                {{ $request->user->name }}
                                <button class="sm:hidden text-gray-500 focus:outline-none" @click="open = !open">
                                    <svg x-show="!open" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    <svg x-show="open" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">{{ $request->currentShift->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">{{ $request->requestedShift->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">{{ $request->reason }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium hidden sm:table-cell">
                                <form action="{{ route('admin.attendance.change-shift.approve', $request->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                </form>
                                <form action="{{ route('admin.attendance.change-shift.reject', $request->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                                </form>
                            </td>
                        </tr>
                        <tr x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="sm:hidden">
                            <td colspan="5" class="px-6 py-4">
                                <div class="space-y-2">
                                    <p><span class="font-medium">Current Shift:</span> {{ $request->currentShift->name }}</p>
                                    <p><span class="font-medium">Requested Shift:</span> {{ $request->requestedShift->name }}</p>
                                    <p><span class="font-medium">Reason:</span> {{ $request->reason }}</p>
                                    <div class="flex justify-end mt-4">
                                        <form action="{{ route('admin.attendance.change-shift.approve', $request->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                        </form>
                                        <form action="{{ route('admin.attendance.change-shift.reject', $request->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
