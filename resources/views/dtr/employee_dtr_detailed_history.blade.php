@extends('layouts.app')

@section('title', $employee->name . ' DTR Logs')

@section('content')
<div class="container mx-auto px-4 py-6">
    <header class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-user-circle mr-3 text-indigo-600"></i>{{ $employee->name }}'s DTR Logs
        </h1>
        <div class="flex items-center space-x-2">
            @if($isFiltered)
                <a href="{{ route('employee.dtr.history', $employee->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
        'title' => 'DTR Logs',
        'route' => route('employee.dtr.history', $employee->id),
        'startDate' => $startDate,
        'endDate' => $endDate,
        'isFiltered' => $isFiltered
    ])

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">DTR Logs for {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}</h2>
        @if ($dtrRecords->isEmpty())
            <p class="text-gray-500">No DTR records found for this employee within the selected date range.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Morning In</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Morning Out</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Afternoon In</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Afternoon Out</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime In</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime Out</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Regular Work Hours</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime Hours</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Work Hours</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($dtrRecords as $dtr)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $dtr->date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $dtr->time_in ? $dtr->time_in->format('h:i A') : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $dtr->time_out ? $dtr->time_out->format('h:i A') : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $dtr->time_in_2 ? $dtr->time_in_2->format('h:i A') : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $dtr->time_out_2 ? $dtr->time_out_2->format('h:i A') : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $dtr->overtime_in ? $dtr->overtime_in->format('h:i A') : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $dtr->overtime_out ? $dtr->overtime_out->format('h:i A') : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($dtr->regular_work_hours, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($dtr->overtime_hours, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($dtr->total_work_hours, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $dtr->status)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
