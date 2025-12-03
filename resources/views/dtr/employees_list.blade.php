@extends('layouts.app')

@section('title', 'Employee List - DTR')

@section('content')
<div class="container mx-auto px-6 py-6">
    <header class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-users mr-3 text-indigo-600"></i> Employee List
        </h1>
        <a href="{{ route('dtr.admin') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i> Back to DTR Management
        </a>
    </header>

    <div class="bg-white rounded-lg shadow-xl p-6">
        <div class="mb-4 flex justify-end">
            <form action="{{ route('dtr.employees.index') }}" method="GET" class="flex items-center space-x-2 w-full md:w-1/4">
                <input type="text" name="search" id="employeeSearchInput" placeholder="Search by name..." class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ $search ?? '' }}">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-search"></i>
                </button>
                @if($search)
                    <a href="{{ route('dtr.employees.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-times mr-2"></i> Clear
                    </a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
            <table id="employeesTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Employee Name</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif">
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $employee->name }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('dtr.employee.show', ['employee' => $employee->id]) }}" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-eye mr-1"></i> View Logs
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-4 text-center text-base text-gray-500 bg-white">
                                <i class="fas fa-users-slash mr-2"></i> No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('input[name="search"]');
        const employeeTableBody = document.querySelector('#employeesTable tbody'); // Assuming an ID for the tbody
        const employeeRows = employeeTableBody ? Array.from(employeeTableBody.querySelectorAll('tr')) : [];

        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();

            employeeRows.forEach(row => {
                const employeeNameCell = row.querySelector('td:first-child'); // Assuming employee name is in the first td
                if (employeeNameCell) {
                    const employeeName = employeeNameCell.textContent.toLowerCase();
                    if (employeeName.includes(searchTerm)) {
                        row.style.display = ''; // Show row
                    } else {
                        row.style.display = 'none'; // Hide row
                    }
                }
            });
        });
    });
</script>
@endpush
