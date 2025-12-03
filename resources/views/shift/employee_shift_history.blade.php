@extends('layouts.app')

@section('title', $employee->name . ' Shift Change Request History')

@section('content')
<div class="container mx-auto px-4 py-6">
    <header class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-calendar-alt mr-3 text-indigo-600"></i>{{ $employee->name }}'s Shift Change Request History
        </h1>
        <div class="flex items-center space-x-2">
            @if($isFiltered)
                <a href="{{ route('employee.shift.history', $employee->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-times mr-2"></i> Clear Filter
                </a>
            @endif
            <button type="button" onclick="document.getElementById('filterModal').classList.remove('hidden')"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $isFiltered ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-filter mr-2"></i> Filter
            </button>
            <a href="{{ route('employees.profile', $employee->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-arrow-left mr-2"></i> Back to Profile
            </a>
        </div>
    </header>

    <!-- Filter Modal -->
    @include('components.filter-modal', [
        'title' => 'Shift Change Requests',
        'route' => route('employee.shift.history', $employee->id),
        'startDate' => $startDate,
        'endDate' => $endDate,
        'isFiltered' => $isFiltered
    ])

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">All Shift Change Requests</h2>
        @if ($records->isEmpty())
            <p class="text-gray-500">No shift change requests found for this employee within the selected date range.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Submitted</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Shift</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Shift</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($records as $shiftRequest)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $shiftRequest->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $shiftRequest->currentShift->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $shiftRequest->newShift->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $shiftRequest->reason }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($shiftRequest->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
