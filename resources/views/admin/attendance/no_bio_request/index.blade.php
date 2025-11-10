@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">No Bio Requests</h1>

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
        @if ($noBioRequests->isEmpty())
            <p class="text-gray-600">No pending no bio requests.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($noBioRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($request->date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $request->type)) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->reason }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($request->status) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
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
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-gray-600">No pending no bio requests.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
