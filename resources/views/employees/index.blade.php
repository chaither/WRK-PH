@extends('layouts.app')

@section('title', 'Employee Management')

@section('content')
<div class="container mx-auto px-6 py-6"> {{-- Consistent compact padding --}}
    <header class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-users-cog mr-3 text-indigo-600"></i> Employee Management
        </h1>
        {{-- Use the route for the separate Create Employee Page if the modal wasn't required --}}
        {{-- For now, we'll keep the modal trigger as requested by the original code. --}}
        <button onclick="openEmployeeModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-150 shadow-md flex items-center">
            <i class="fas fa-plus mr-2"></i> Add Employee
        </button>
    </header>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Employee List Table --}}
    <div class="bg-white rounded-xl shadow-xl overflow-x-auto border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">
                        Role
                    </th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Joined
                    </th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($employees as $employee)
                    <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $employee->name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                            {{ $employee->email }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            @php
                                $role_class = [
                                    'admin' => 'bg-red-100 text-red-800',
                                    'hr' => 'bg-indigo-100 text-indigo-800',
                                    'employee' => 'bg-green-100 text-green-800',
                                ][$employee->role] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full capitalize {{ $role_class }}">
                                {{ $employee->role }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-600">
                            {{ $employee->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex justify-center space-x-2">
                                <a href="#" onclick="openEmployeeModal({{ $employee->toJson() }})" 
                                   class="text-indigo-600 hover:text-indigo-800 p-1.5 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150" 
                                   title="Edit Employee">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete {{ $employee->name }}? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-800 p-1.5 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150" 
                                            title="Delete Employee">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-base text-gray-500 bg-white">
                            <i class="fas fa-users-slash mr-2"></i> No employee records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
                                                <input type="checkbox" name="working_days[]" :value="day" x-model="selectedDays" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
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
        const payPeriodSelectModal = document.getElementById('pay_period_modal'); // Renamed ID for clarity
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
        function getWorkingDaysInMonth() {
            const today = new Date();
            const year = today.getFullYear();
            const month = today.getMonth();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            let workingDays = 0;

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dayOfWeek = date.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
                if (dayOfWeek >= 1 && dayOfWeek <= 6) { 
                    workingDays++;
                }
            }
            return workingDays;
        }

        function calculateRatesModal() {
            const basicSalary = parseFloat(basicSalaryInputModal.value) || 0;
            const WORKING_HOURS_PER_DAY = 8;
            
            // Fallback: Use the fixed 22 days if the calculation logic fails or if a simple factor is preferred
            const DEFAULT_WORKING_DAYS = 22; 
            
            // Use server-side value if available, otherwise use a safe default
            // Since this is client-side JS, we'll use a simplified factor of 22/8 
            // for consistency, as the date loop in the original code is overly complex for UI-side estimation.

            let dailyRate = 0;
            let hourlyRate = 0;
            
            if (basicSalary > 0) {
                // Simplified estimate: Monthly Salary / 22 working days
                dailyRate = basicSalary / DEFAULT_WORKING_DAYS;
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
                document.getElementById('basic_salary_modal').value = employee.basic_salary;
                document.getElementById('pay_period_modal').value = employee.pay_period;
                document.getElementById('work_start').value = employee.work_start.substring(0, 5);
                document.getElementById('work_end').value = employee.work_end.substring(0, 5);
                document.getElementById('start_date').value = employee.start_date; // Populate start_date

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
@endpush