@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Departments</h1>

    <div class="flex justify-end mb-4">
        <button onclick="openEmployeeModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-150 shadow-md flex items-center mr-2">
            <i class="fas fa-plus mr-2"></i> Add Employee
        </button>
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
                            <a href="{{ route('departments.show_employees', $department->id) }}" class="text-blue-600 hover:text-blue-900 font-semibold">
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
                    <label for="create_department_name" class="block text-sm font-medium text-gray-700">Department Name</label>
                    <input type="text" name="name" id="create_department_name" required
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

@push('modals')
<div id="employeeModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50 p-4"> {{-- Consistent dark overlay --}}
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl mx-4 p-6 transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col"> {{-- Sharper modal styling --}}
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 id="modalTitle" class="text-2xl font-bold text-gray-800">Add New Employee</h3>
            <button onclick="closeEmployeeModal()" class="text-gray-500 hover:text-gray-900 transition duration-150 p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="employeeForm" method="POST" action="{{ route('employees.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="_method" value="POST" id="_methodField">
            <div class="flex-grow overflow-y-auto pr-2">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Personal Information --}}
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-inner">
                        <h4 class="text-xl font-semibold mb-4 text-indigo-700 flex items-center border-b pb-2">
                            <i class="fas fa-address-card mr-2 text-lg"></i> Personal Information
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" name="name" id="name" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter full name">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="email" id="email" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter email address">
                            </div>
                            <div id="passwordFields">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <div class="relative">
                                    <input type="password" name="password" id="password" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md pr-10 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter password">
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 cursor-pointer" onclick="togglePasswordVisibility('password')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                            </div>
                            <div id="passwordConfirmationField">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <div class="relative">
                                    <input type="password" name="password_confirmation" id="password_confirmation" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md pr-10 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Confirm password">
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 cursor-pointer" onclick="togglePasswordVisibility('password_confirmation')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                <input type="text" name="position" id="position" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter job position">
                            </div>
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">System Role</label>
                                <select name="role" id="role" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="employee">Employee (Default)</option>
                                    <option value="hr">HR Manager</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                <select name="department_id" id="department_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                           
                        </div>
                    </div>
                    
                    {{-- Schedule --}}
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-inner">
                        <h4 class="text-xl font-semibold mb-4 text-indigo-700 flex items-center border-b pb-2">
                            <i class="fas fa-clock mr-2 text-lg"></i> Work Schedule
                        </h4>
                         <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" name="start_date" id="start_date" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        
                        <div class="mt-4">
                                <label for="shift" class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                                <select name="shift" id="shift" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="morning">Morning Shift</option>
                                    <option value="night">Night Shift</option>
                                </select>
                            </div>

                        <div class="mt-4" x-data="{ open: false, selectedDays: [], days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] }">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Working Days</label>
                            <div class="relative">
                                <button type="button" @click="open = !open" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <span x-text="selectedDays.length ? selectedDays.join(', ') : 'Select working days'" class="block truncate"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                                <ul x-show="open" @click.away="open = false" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-40 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-y-auto scroll-smooth focus:outline-none sm:text-sm list-none" tabindex="-1" role="listbox" aria-labelledby="listbox-label">
                                    <template x-for="(day, index) in days" :key="index">
                                        <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :id="`day-option-${index}`" role="option">
                                            <div class="flex items-center space-x-2">
                                                <input type="checkbox" name="working_days[]" :value="day" x-model="selectedDays" @change="Alpine.nextTick(() => calculateRatesModal())" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                <span class="block font-normal" x-text="day"></span>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-8 mt-4">
                            <div>
                                <label for="work_start" class="block text-sm font-medium text-gray-700 mb-1">Work Start Time</label>
                                <input type="time" name="work_start" id="work_start" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="09:00">
                            </div>
                            <div>
                                <label for="work_end" class="block text-sm font-medium text-gray-700 mb-1">Work End Time</label>
                                <input type="time" name="work_end" id="work_end" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="18:00">
                            </div>
                        </div>
                    </div>
                    
                    {{-- Salary --}}
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-inner">
                        <h4 class="text-xl font-semibold mb-4 text-indigo-700 flex items-center border-b pb-2">
                            <i class="fas fa-money-bill-wave mr-2 text-lg"></i> Payroll Details
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label for="basic_salary_modal" class="block text-sm font-medium text-gray-700 mb-1">Monthly Basic Salary</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium">₱</span>
                                    <input type="number" name="basic_salary" id="basic_salary_modal" required 
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                            <div>
                                <label for="pay_period_modal" class="block text-sm font-medium text-gray-700 mb-1">Pay Period</label>
                                <select name="pay_period" id="pay_period_modal" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select pay period</option>
                                    <option value="semi-monthly">Semi-monthly (15th and 30th)</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                            {{-- Daily Rate (Auto) --}}
                            <div>
                                <label for="daily_rate_modal" class="block text-sm font-medium text-gray-700 mb-1">Daily Rate (Auto)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium">₱</span>
                                    <input type="number" name="daily_rate" id="daily_rate_modal" required readonly 
                                        class="w-full pl-8 pr-3 py-2 border border-dashed border-gray-400 rounded-md shadow-inner bg-gray-100 text-gray-700 cursor-not-allowed" step="0.01" value="0.00">
                                </div>
                            </div>
                            {{-- Hourly Rate (Auto) --}}
                            <div>
                                <label for="hourly_rate_modal" class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate (Auto)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium">₱</span>
                                    <input type="number" name="hourly_rate" id="hourly_rate_modal" required readonly 
                                        class="w-full pl-8 pr-3 py-2 border border-dashed border-gray-400 rounded-md shadow-inner bg-gray-100 text-gray-700 cursor-not-allowed" step="0.01" value="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Form Actions --}}
                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeEmployeeModal()" class="px-6 py-2 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                        Cancel
                    </button>
                    <button type="submit" id="saveEmployeeBtn" class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-lg shadow-indigo-200">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const basicSalaryInputModal = document.getElementById('basic_salary_modal');
        const dailyRateInputModal = document.getElementById('daily_rate_modal');
        const hourlyRateInputModal = document.getElementById('hourly_rate_modal');
        const payPeriodSelectModal = document.getElementById('pay_period_modal');

        const employeeForm = document.getElementById('employeeForm');
        const modalTitle = document.getElementById('modalTitle');
        const methodField = document.getElementById('_methodField');
        const passwordFields = document.getElementById('passwordFields');
        const passwordConfirmationField = document.getElementById('passwordConfirmationField');
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('password_confirmation');
        const saveEmployeeBtn = document.getElementById('saveEmployeeBtn');

        /**
         * Calculates the number of working days (Mon-Sat) in the current month.
         * Note: This calculation is dynamic but assumes current month and a fixed Mon-Sat schedule.
         * For a robust system, this should ideally be handled on the server side based on pay frequency and fixed working days.
         */
        window.getWorkingDaysInMonth = function(selectedWorkingDaysArr) {
            const today = new Date();
            const year = today.getFullYear();
            const month = today.getMonth();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            let actualWorkingDays = 0;

            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dayOfWeek = date.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
                const currentDayName = dayNames[dayOfWeek];
                
                if (selectedWorkingDaysArr.includes(currentDayName)) {
                    actualWorkingDays++;
                }
            }
            return actualWorkingDays;
        }

        window.calculateRatesModal = function() {
            const basicSalary = parseFloat(basicSalaryInputModal.value) || 0;
            const WORKING_HOURS_PER_DAY = 8;
            
            // Get selected working days directly from checked checkboxes
            const selectedWorkingDayElements = document.querySelectorAll('input[name="working_days[]"]:checked');
            const selectedWorkingDaysArray = Array.from(selectedWorkingDayElements).map(el => el.value);
            
            const actualWorkingDaysInMonth = getWorkingDaysInMonth(selectedWorkingDaysArray);
            
            let dailyRate = 0;
            let hourlyRate = 0;
            
            if (basicSalary > 0 && actualWorkingDaysInMonth > 0) {
                dailyRate = basicSalary / actualWorkingDaysInMonth;
                hourlyRate = dailyRate / WORKING_HOURS_PER_DAY;
            }

            dailyRateInputModal.value = dailyRate.toFixed(2);
            hourlyRateInputModal.value = hourlyRate.toFixed(2);
        }

        // --- Event Listeners and Modal Functions ---
        
        // Listeners for rate calculation
        basicSalaryInputModal.addEventListener('input', calculateRatesModal);
        // Pay period doesn't affect the Daily/Hourly rate calculation based on monthly salary (as simplified above)
        // payPeriodSelectModal.addEventListener('change', calculateRatesModal); 

        // Open/Close Modal
        window.openEmployeeModal = function(employee = null) {
            document.getElementById('employeeModal').classList.remove('hidden');
            document.getElementById('employeeModal').classList.add('flex');
            
            if (employee) {
                // Edit mode
                modalTitle.textContent = 'Edit Employee';
                employeeForm.action = '/employees/' + employee.id;
                methodField.value = 'PUT';
                saveEmployeeBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Update Employee';

                // Populate form fields
                document.getElementById('name').value = employee.name;
                document.getElementById('email').value = employee.email;
                document.getElementById('position').value = employee.position;
                document.getElementById('role').value = employee.role;
                document.getElementById('department_id').value = employee.department_id; // Populate department_id
                document.getElementById('basic_salary_modal').value = employee.basic_salary;
                document.getElementById('pay_period_modal').value = employee.pay_period;
                document.getElementById('work_start').value = employee.work_start.substring(0, 5);
                document.getElementById('work_end').value = employee.work_end.substring(0, 5);
                document.getElementById('start_date').value = employee.start_date; // Populate start_date
                document.getElementById('shift').value = employee.shift; // Populate shift

                // Populate working days multi-select
                // Access the Alpine.js component and set its selectedDays
                const workingDaysDropdown = document.querySelector('[x-data*="selectedDays"]');
                if (workingDaysDropdown && workingDaysDropdown.__alpine) {
                    workingDaysDropdown.__alpine.data.selectedDays = employee.working_days || [];
                }

                // Hide password fields for edit mode (password changes can be a separate flow if needed)
                passwordFields.classList.add('hidden');
                passwordConfirmationField.classList.add('hidden');
                passwordInput.removeAttribute('required');
                passwordConfirmationInput.removeAttribute('required');
                
                calculateRatesModal(); // Recalculate rates for the existing employee
            } else {
                // Add mode
                modalTitle.textContent = 'Add New Employee';
                employeeForm.action = '{{ route('employees.store') }}';
                methodField.value = 'POST';
                saveEmployeeBtn.innerHTML = '<i class="fas fa-user-plus mr-1"></i> Save Employee';
                
                employeeForm.reset();
                // Show password fields for add mode
                passwordFields.classList.remove('hidden');
                passwordConfirmationField.classList.remove('hidden');
                passwordInput.setAttribute('required', 'required');
                passwordConfirmationInput.setAttribute('required', 'required');
                document.getElementById('shift').value = 'morning'; // Set default shift for new employee
                document.getElementById('department_id').value = ''; // Reset department for new employee

                // Reset working days multi-select
                const workingDaysDropdown = document.querySelector('[x-data*="selectedDays"]');
                if (workingDaysDropdown && workingDaysDropdown.__alpine) {
                    workingDaysDropdown.__alpine.data.selectedDays = [];
                }

                // Initialize calculated rates on open for add mode
                basicSalaryInputModal.value = ''; // Ensure rates start at 0.00
                payPeriodSelectModal.value = 'semi-monthly'; // Set a default value
                calculateRatesModal();
            }
        };

        window.closeEmployeeModal = function() {
            document.getElementById('employeeModal').classList.add('hidden');
            document.getElementById('employeeModal').classList.remove('flex');
        };

        // Password visibility toggle
        window.togglePasswordVisibility = function(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        };

        // Client-side password confirmation validation
        employeeForm.addEventListener('submit', function(e) {
            // Only validate password if the fields are visible (i.e., in add mode)
            if (!passwordFields.classList.contains('hidden')) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password and Confirm Password do not match.');
            }
            }
        });

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const createDepartmentModal = document.getElementById('createDepartmentModal');
        const openCreateDepartmentModalBtn = document.getElementById('openCreateDepartmentModal');
        const closeCreateDepartmentModalBtn = document.getElementById('closeCreateDepartmentModal');
        const createDepartmentForm = createDepartmentModal.querySelector('form');
        const createDepartmentNameInput = document.getElementById('create_department_name');

        // Open Create Department Modal
        openCreateDepartmentModalBtn.addEventListener('click', function () {
            createDepartmentModal.classList.remove('hidden');
            createDepartmentNameInput.value = ''; // Clear input on open
        });

        // Close Create Department Modal
        closeCreateDepartmentModalBtn.addEventListener('click', function () {
            createDepartmentModal.classList.add('hidden');
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function (event) {
            if (event.target === createDepartmentModal) {
                createDepartmentModal.classList.add('hidden');
            }
        });

        // Handle Create Department Form Submission with AJAX
        createDepartmentForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message); // Or display a more elegant success message
                    createDepartmentModal.classList.add('hidden');
                    location.reload(); // Reload page to show new department and clear errors
                } else if (data.errors) {
                    // Handle validation errors
                    let errorMessages = '';
                    for (const field in data.errors) {
                        errorMessages += data.errors[field].join('\n') + '\n';
                    }
                    alert('Validation Error:\n' + errorMessages);
                } else {
                    alert('An unexpected error occurred.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the department.');
            });
        });

        // Edit Department Modal Logic (existing code, ensure proper IDs are used)
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

        // Delete Department Modal Logic (existing code)
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
