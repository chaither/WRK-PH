@extends('layouts.app')

@section('title', 'Leave Request Review')

@section('content')
<div class="mx-6 py-6"> {{-- Consistent compact padding --}}
    <header class="mb-6">
        <h1 class="text-3xl font-bold text-white flex items-center">
            <i class="fas fa-clipboard-list mr-3 text-indigo-600"></i> Leave Request Review
        </h1>
    </header>

    {{-- Session Messages --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-3xl relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-3xl relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Leave Requests Table --}}
    <div class="bg-white rounded-3xl shadow-xl overflow-x-auto border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Employee
                    </th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Start Date
                    </th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                        End Date
                    </th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Reason
                    </th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($leaveRequests as $request)
                <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $request->user->name }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center">
                        {{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center">
                        {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate text-center">
                        <a href="{{ route('leave.reason.pdf', $request->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center" title="View Reason as PDF">
                            <i class="fas fa-file-pdf mr-1 text-sm"></i> View Letter
                        </a>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                        @php
                            $status_class = [
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                            ][$request->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full capitalize {{ $status_class }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                        <div class="flex justify-center items-center space-x-2">
                            @if ($request->status === 'pending')
                                {{-- Approve Button --}}
                                <form action="{{ route('leave.approve', $request) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-150 shadow-sm flex items-center">
                                        <i class="fas fa-check-circle mr-1"></i> Approve
                                    </button>
                                </form>
                                {{-- Reject Button --}}
                                <form action="{{ route('leave.reject', $request) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-150 shadow-sm flex items-center">
                                        <i class="fas fa-times-circle mr-1"></i> Reject
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i> Finalized
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-base text-gray-500 bg-white">
                            <i class="fas fa-clipboard-check mr-2"></i> No pending leave requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection