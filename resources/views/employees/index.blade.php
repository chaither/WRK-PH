@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Employee List</h2>
            <button onclick="openEmployeeModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <i class="fas fa-plus mr-2"></i>Add Employee
            </button>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Role</th>
                        <th class="px-4 py-2">Created At</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $employee->name }}</td>
                            <td class="px-4 py-2">{{ $employee->email }}</td>
                            <td class="px-4 py-2 capitalize">{{ $employee->role }}</td>
                            <td class="px-4 py-2">{{ $employee->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('employees.edit', $employee->id) }}" class="text-blue-500 hover:text-blue-700 mr-2">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center text-gray-500">No employees found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div id="employeeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg w-full max-w-4xl mx-4 p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold">Add New Employee</h3>
            <button onclick="closeEmployeeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="employeeForm" method="POST" action="{{ route('employees.store') }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-lg font-semibold mb-4 text-blue-800">Personal Information</h4>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-gray-700 font-medium mb-2">Full Name</label>
                            <input type="text" name="name" id="name" required class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="Enter full name">
                        </div>
                        <div>
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                            <input type="email" name="email" id="email" required class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="Enter email address">
                        </div>
                        <div>
                            <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                            <input type="password" name="password" id="password" required class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="Enter password">
                            <p class="text-sm text-gray-500 mt-1">Minimum 8 characters</p>
                        </div>
                        <div>
                            <label for="position" class="block text-gray-700 font-medium mb-2">Position</label>
                            <input type="text" name="position" id="position" required class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="Enter job position">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-lg font-semibold mb-4 text-blue-800">Salary Information</h4>
                    <div class="space-y-4">
                        <div>
                            <label for="basic_salary" class="block text-gray-700 font-medium mb-2">Basic Salary</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-600">₱</span>
                                <input type="number" name="basic_salary" id="basic_salary" required class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label for="pay_period" class="block text-gray-700 font-medium mb-2">Pay Period</label>
                            <select name="pay_period" id="pay_period" required class="w-full px-3 py-2 border border-gray-300 rounded">
                                <option value="">Select pay period</option>
                                <option value="semi-monthly">Semi-monthly (15th and 30th)</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label for="daily_rate" class="block text-gray-700 font-medium mb-2">Daily Rate</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-600">₱</span>
                                <input type="number" name="daily_rate" id="daily_rate" required class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label for="hourly_rate" class="block text-gray-700 font-medium mb-2">Hourly Rate</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-600">₱</span>
                                <input type="number" name="hourly_rate" id="hourly_rate" required class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-lg font-semibold mb-4 text-blue-800">Schedule Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="work_start" class="block text-gray-700 font-medium mb-2">Work Start Time</label>
                        <input type="time" name="work_start" id="work_start" required class="w-full px-3 py-2 border border-gray-300 rounded" value="09:00">
                    </div>
                    <div>
                        <label for="work_end" class="block text-gray-700 font-medium mb-2">Work End Time</label>
                        <input type="time" name="work_end" id="work_end" required class="w-full px-3 py-2 border border-gray-300 rounded" value="18:00">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-4 pt-6">
                <button type="button" onclick="closeEmployeeModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Employee</button>
            </div>
        </form>
    </div>
</div>
@endpush