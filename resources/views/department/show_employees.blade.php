@extends('layouts.app')

@section('title', 'Employees in ' . $department->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Employees in {{ $department->name }} Department</h1>

    <div class="flex justify-end mb-4">
        <button onclick="openEmployeeModal(null, {{ $department->id }})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-150 shadow-md flex items-center mr-2">
            <i class="fas fa-plus mr-2"></i> Add Employee
        </button>
    </div>

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

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        #
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Position
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($employees as $employee)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $employee->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $employee->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $employee->position ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button type="button" onclick="editEmployee({{ $employee->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2" title="Edit Employee">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('departments.remove_employee', ['department' => $department->id, 'employee' => $employee->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to permanently delete this employee account?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 ml-2" title="Delete Employee">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No employees found in this department.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


@endsection

@push('modals')
    @include('components.employee_modal', ['departments' => $departments, 'shifts' => $shifts])
@endpush

@push('scripts')
<script>
    // Function to handle editing an employee
    window.editEmployee = async function(employeeId) {
        try {
            const response = await fetch(`/employees/${employeeId}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const employee = await response.json();
            
            // Assuming openEmployeeModal is available globally from department/index.blade.php
            if (typeof openEmployeeModal === 'function') {
                openEmployeeModal(employee);
            } else {
                console.error('openEmployeeModal function not found. Ensure department/index.blade.php scripts are loaded.');
                alert('Error: Edit functionality not fully loaded. Please refresh the page.');
            }
        } catch (error) {
            console.error('Error fetching employee data:', error);
            alert('Failed to load employee data for editing.');
        }
    };
</script>
@endpush
