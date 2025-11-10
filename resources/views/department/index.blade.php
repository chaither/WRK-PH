@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Departments</h1>

    <div class="flex justify-end mb-4">
        <button id="openCreateDepartmentModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Add Department
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
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                {{-- Department data will be loaded here by JavaScript or passed from the controller --}}
                @forelse ($departments as $department)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('employees.index', ['department_id' => $department->id]) }}" class="text-blue-600 hover:text-blue-900 font-semibold">
                                {{ $department->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="#" class="text-indigo-600 hover:text-indigo-900 edit-department-btn" data-id="{{ $department->id }}" data-name="{{ $department->name }}"><i class="fas fa-edit"></i></a>
                            <a href="#" class="text-red-600 hover:text-red-900 delete-department-btn" data-id="{{ $department->id }}" data-name="{{ $department->name }}"><i class="fas fa-trash"></i></a>
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

    <!-- Create Department Modal -->
    <div id="createDepartmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Department</h3>
            <form action="{{ route('department.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Department Name</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeCreateDepartmentModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Create Department
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div id="editDepartmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Department</h3>
            <form id="editDepartmentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_name" class="block text-sm font-medium text-gray-700">Department Name</label>
                    <input type="text" name="name" id="edit_name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeEditDepartmentModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Department
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Department Modal -->
    <div id="deleteDepartmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Department</h3>
            <p class="mb-4">Are you sure you want to delete department "<span id="deleteDepartmentName" class="font-semibold"></span>"?</p>
            <div class="flex justify-end">
                <button type="button" id="closeDeleteDepartmentModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                    Cancel
                </button>
                <form id="deleteDepartmentForm" method="POST" class="inline-block">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const openModalButton = document.getElementById('openCreateDepartmentModal');
        const closeModalButton = document.getElementById('closeCreateDepartmentModal');
        const createDepartmentModal = document.getElementById('createDepartmentModal');

        openModalButton.addEventListener('click', function () {
            createDepartmentModal.classList.remove('hidden');
        });

        closeModalButton.addEventListener('click', function () {
            createDepartmentModal.classList.add('hidden');
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function (event) {
            if (event.target === createDepartmentModal) {
                createDepartmentModal.classList.add('hidden');
            }
        });

        // Edit Department Modal Logic
        const editDepartmentModal = document.getElementById('editDepartmentModal');
        const closeEditDepartmentModal = document.getElementById('closeEditDepartmentModal');
        const editDepartmentForm = document.getElementById('editDepartmentForm');
        const editDepartmentNameInput = document.getElementById('edit_name');

        document.querySelectorAll('.edit-department-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const departmentId = this.dataset.id;
                const departmentName = this.dataset.name;
                
                editDepartmentForm.action = `/department/${departmentId}`;
                editDepartmentNameInput.value = departmentName;
                editDepartmentModal.classList.remove('hidden');
            });
        });

        closeEditDepartmentModal.addEventListener('click', function () {
            editDepartmentModal.classList.add('hidden');
        });

        window.addEventListener('click', function (event) {
            if (event.target === editDepartmentModal) {
                editDepartmentModal.classList.add('hidden');
            }
        });

        // Delete Department Modal Logic
        const deleteDepartmentModal = document.getElementById('deleteDepartmentModal');
        const closeDeleteDepartmentModal = document.getElementById('closeDeleteDepartmentModal');
        const deleteDepartmentForm = document.getElementById('deleteDepartmentForm');
        const deleteDepartmentNameSpan = document.getElementById('deleteDepartmentName');

        document.querySelectorAll('.delete-department-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const departmentId = this.dataset.id;
                const departmentName = this.dataset.name;

                deleteDepartmentForm.action = `/department/${departmentId}`;
                deleteDepartmentNameSpan.textContent = departmentName;
                deleteDepartmentModal.classList.remove('hidden');
            });
        });

        closeDeleteDepartmentModal.addEventListener('click', function () {
            deleteDepartmentModal.classList.add('hidden');
        });

        window.addEventListener('click', function (event) {
            if (event.target === deleteDepartmentModal) {
                deleteDepartmentModal.classList.add('hidden');
            }
        });

    });
</script>
@endpush
