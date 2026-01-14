@extends('layouts.app')

@section('content')
<div class="mx-6 py-6">
    <h1 class="text-2xl font-semibold text-white mb-4">No Bio Requests</h1>

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

    <div class="bg-white shadow-md rounded-3xl p-6">
        @if ($noBioRequests->isEmpty())
            <p class="text-gray-600">No pending no bio requests.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Time In</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Time Out</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-gray-900">
                    @forelse ($noBioRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $request->user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ \Carbon\Carbon::parse($request->date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ ucfirst(str_replace(['_in', '_out', 'all_morning', 'all_afternoon', 'whole_day'], [' In', ' Out', 'All Morning', 'All Afternoon', 'Whole Day'], $request->type)) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $request->requested_time_in ? \Carbon\Carbon::parse($request->requested_time_in)->format('h:i A') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $request->requested_time_out ? \Carbon\Carbon::parse($request->requested_time_out)->format('h:i A') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $request->reason }}</td>
                        <td class="px-6 py-4 whitespace-nowrap"><span class="px-3 py-1 rounded-full text-xs font-semibold {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($request->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($request->status) }}</span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            @if ($request->status === 'pending')
                                <form action="{{ route('admin.attendance.no-bio-request.approve', $request->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                </form>
                                <form action="{{ route('admin.attendance.no-bio-request.reject', $request->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                                </form>
                            @else
                                {{ ucfirst($request->status) }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-gray-600">No pending no bio requests.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
