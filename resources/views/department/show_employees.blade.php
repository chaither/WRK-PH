@extends('layouts.app')

@section('title', 'Employees in ' . $department->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Employees in {{ $department->name }} Department</h1>

    <div class="flex justify-end mb-4">
        <button onclick="openAddEmployeeToDepartmentModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-150 shadow-md flex items-center">
            <i class="fas fa-user-plus mr-2"></i> Add Existing Employee
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
                            <form action="{{ route('departments.remove_employee', ['department' => $department->id, 'employee' => $employee->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this employee from the department?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 ml-2"><i class="fas fa-user-minus"></i> Remove</button>
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

{{-- Add Existing Employee to Department Modal --}}
<div id="addEmployeeToDepartmentModal" class="fixed inset-0 bg-gray-600 bg-opacity50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Add Existing Employee to {{ $department->name }}</h3>
        <form action="{{ route('departments.add_employee', $department->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="employee_id" class="block text-sm font-medium text-gray-700">Select Employee</label>
                <select name="employee_id" id="employee_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Select an employee</option>
                    @foreach($availableEmployees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeAddEmployeeToDepartmentModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add Employee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddEmployeeToDepartmentModal() {
        document.getElementById('addEmployeeToDepartmentModal').classList.remove('hidden');
    }

    function closeAddEmployeeToDepartmentModal() {
        document.getElementById('addEmployeeToDepartmentModal').classList.add('hidden');
    }

    // Close modal when clicking outside of it
    window.addEventListener('click', function (event) {
        const modal = document.getElementById('addEmployeeToDepartmentModal');
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
</script>
@endpush
