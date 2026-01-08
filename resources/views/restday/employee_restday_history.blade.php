@extends('layouts.app')

@section('title', $employee->name . ' Rest Day Change Request History')

@section('content')
<div class="container mx-auto px-4 py-6">
    <header class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-sun mr-3 text-indigo-600"></i>{{ $employee->name }}'s Rest Day Change Request History
        </h1>
        <div class="flex items-center space-x-2">
            @if($isFiltered)
                <a href="{{ route('employee.restday.history', $employee->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
        'title' => 'Rest Day Change Requests',
        'route' => route('employee.restday.history', $employee->id),
        'startDate' => $startDate,
        'endDate' => $endDate,
        'isFiltered' => $isFiltered
    ])

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">All Rest Day Change Requests</h2>
        @if ($records->isEmpty())
            <p class="text-gray-500">No rest day change requests found for this employee within the selected date range.</p>
        @else

    <div class="mb-4">
        <input type="text" id="restSearch" placeholder="Search by Date (e.g. Jan 01, 2026)" class="px-4 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full md:w-1/3">
    </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="restTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Submitted</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Rest Days</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Rest Days</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($records as $restdayRequest)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $restdayRequest->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ implode(', ', $restdayRequest->current_restdays) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ implode(', ', $restdayRequest->requested_restdays) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $restdayRequest->reason }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($restdayRequest->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('restSearch');
        const table = document.getElementById('restTable');
        if (!table || !searchInput) return;

        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            rows.forEach(row => {
                const dateCell = row.cells[0]; // Date Submitted is 1st column
                if (dateCell) {
                    const dateText = dateCell.textContent.toLowerCase();
                    if (dateText.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    });
</script>
@endpush
