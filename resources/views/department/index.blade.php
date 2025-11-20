@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Departments</h1>

    <div class="flex justify-end mb-4">
        <button onclick="openEmployeeModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-150 shadow-md flex items-center mr-2">
            <i class="fas fa-plus mr-2"></i> Add Employee
        </button>
        <button onclick="openDepartmentModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-plus mr-2"></i> Add Department
        </button>
    </div>

    <!-- Department List (Table) -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        #
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Department Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                {{-- Department data will be loaded here by JavaScript or passed from the controller --}}
                @forelse ($departments as $department)
                    <tr x-data="{ open: false }" class="mobile-accordion hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button @click="open = !open" class="flex justify-between items-center w-full focus:outline-none sm:cursor-default py-2">
                                <a href="{{ route('departments.show_employees', $department->id) }}" class="text-blue-600 hover:text-blue-900 font-semibold">
                                    {{ $department->name }}
                                </a>
                                <svg x-show="!open" class="w-4 h-4 sm:hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="open" class="w-4 h-4 sm:hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2 hidden sm:table-cell">
                            <button onclick="openDepartmentModal({{ json_encode($department) }})" class="text-indigo-600 hover:text-indigo-900 edit-department-btn"><i class="fas fa-edit"></i></button>
                            <button @click="window.openDeleteDepartmentModal({{ $department->id }}, '{{ $department->name }}')" class="text-red-600 hover:text-red-900 delete-department-btn"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="sm:hidden">
                        <td colspan="3" class="px-6 py-4 bg-white text-sm">
                            <div class="flex justify-end space-x-2">
                                <button onclick="openDepartmentModal({{ json_encode($department) }})" class="text-indigo-600 hover:text-indigo-900 edit-department-btn"><i class="fas fa-edit"></i> Edit</button>
                                <button @click="window.openDeleteDepartmentModal({{ $department->id }}, '{{ $department->name }}')" class="text-red-600 hover:text-red-900 delete-department-btn"><i class="fas fa-trash"></i> Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No departments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Delete Department Modal -->
    <div id="deleteDepartmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Department</h3>
            <p class="mb-4">Are you sure you want to delete department "<span x-text="departmentName" class="font-semibold"></span>"?</p>
            <div class="flex justify-end">
                <button @click="open = false" type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                    Cancel
                </button>
                <form :action="`/department/${departmentId}`" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('modals')
    @include('components.employee_modal', ['departments' => $departments, 'shifts' => $shifts])
    @include('components.department_modal')
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // No more imperative JavaScript for delete modal, Alpine.js handles it.
        // The window.openDeleteDepartmentModal function will be replaced by direct Alpine.js calls.
    });
</script>
@endpush
