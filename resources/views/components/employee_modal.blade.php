<div id="employeeModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 sm:p-2 md:p-4"> {{-- Consistent dark overlay --}}
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-auto sm:p-3 md:p-5 transform transition-all duration-300 scale-100 flex flex-col max-h-[95vh] overflow-y-auto mt-14" x-data="{ currentStep: 1, totalSteps: 3 }"> {{-- Sharper modal styling --}}
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 id="modalTitle" class="text-2xl font-bold text-gray-800">Add New Employee</h3>
            <button onclick="closeEmployeeModal()" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="employeeForm" method="POST" action="{{ route('employees.store') }}" class="space-y-4 flex flex-col">
            @csrf
            <input type="hidden" name="_method" value="POST" id="_methodField">
            <div class="flex-grow pr-2">
                {{-- Step 1: Personal Information --}}
                <div x-show="currentStep === 1" class="grid grid-cols-1 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-inner">
                        <h4 class="text-xl font-semibold mb-4 text-indigo-700 flex items-center border-b pb-2">
                            <i class="fas fa-address-card mr-2 text-lg"></i> Personal Information
                        </h4>
                        <div class="space-y-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-1">
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="first_name" id="first_name" autocomplete="given-name" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 text-black">
                                </div>
                                <div class="col-span-1">
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="last_name" id="last_name" autocomplete="family-name" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 text-black">
                                </div>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="email" id="email" autocomplete="email" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 text-black">
                            </div>
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID</label>
                                <input type="number" name="employee_id" id="employee_id" min="1"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 text-black" placeholder="Enter employee number or leave blank">
                                <p class="text-xs text-gray-500 mt-1">Auto-generated if left blank (sequential numbers starting from 101)</p>
                            </div>
                            <div id="passwordFields">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <div class="relative">
                                    <input type="password" name="password" id="password" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md pr-10 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black" placeholder="Enter password">
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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md pr-10 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black" placeholder="Confirm password">
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 cursor-pointer" onclick="togglePasswordVisibility('password_confirmation')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                <input type="text" name="position" id="position" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black" placeholder="Enter job position">
                            </div>
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">System Role</label>
                                <select name="role" id="role" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black bg-white">
                                    <option value="employee" class="text-black">Employee (Default)</option>
                                    <option value="hr" class="text-black">HR Manager</option>
                                    <option value="admin" class="text-black">Administrator</option>
                                </select>
                            </div>
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                <select name="department_id" id="department_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 max-w-full text-black bg-white">
                                    <option value="" class="text-black">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" class="text-black">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Work Schedule --}}
                <div x-show="currentStep === 2" class="grid grid-cols-1 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-inner">
                        <h4 class="text-xl font-semibold mb-4 text-indigo-700 flex items-center border-b pb-2">
                            <i class="fas fa-clock mr-2 text-lg"></i> Work Schedule
                        </h4>
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="start_date" id="start_date" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black">
                        </div>

                        <div class="mt-3">
                            <label for="shift_id" class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                            <select name="shift_id" id="shift_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black bg-white">
                                <option value="" class="text-black">Select Shift</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" class="text-black">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-3" x-data="{ open: false, days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] }" data-working-days-dropdown>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Working Days</label>
                            <div class="relative">
                                <button type="button" @click="open = !open" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-black">
                                    <span x-text="$store.employeeStore.workingDays.length ? $store.employeeStore.workingDays.join(', ') : 'Select working days'" class="block truncate"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                                <ul x-show="open" @click.away="open = false" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-40 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-y-auto scroll-smooth focus:outline-none sm:text-sm list-none" tabindex="-1" role="listbox" aria-labelledby="listbox-label">
                                    <template x-for="(day, index) in days" :key="`working-day-${index}`">
                                        <li class="text-black cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :id="`day-option-${index}`" role="option">
                                            <div class="flex items-center space-x-2">
                                                <input type="checkbox" name="working_days[]" :value="day" x-model="$store.employeeStore.workingDays" @change="Alpine.nextTick(() => calculateRatesModal())" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                <span class="block font-normal" x-text="day"></span>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-3" x-data="{ open: false, days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] }" data-rest-days-dropdown>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rest Days</label>
                            <div class="relative">
                                <button type="button" @click="open = !open" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-black">
                                    <span x-text="$store.employeeStore.restDays.length ? $store.employeeStore.restDays.join(', ') : 'Select rest days'" class="block truncate"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                                <ul x-show="open" @click.away="open = false" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-40 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-y-auto scroll-smooth focus:outline-none sm:text-sm list-none" tabindex="-1" role="listbox" aria-labelledby="listbox-label">
                                    <template x-for="(day, index) in days" :key="`rest-day-${index}`">
                                        <li class="text-black cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :id="`rest-day-option-${index}`" role="option">
                                            <div class="flex items-center space-x-2">
                                                <input type="checkbox" name="rest_days[]" :value="day" x-model="$store.employeeStore.restDays" @change="Alpine.nextTick(() => calculateRatesModal())" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                <span class="block font-normal" x-text="day"></span>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <label for="work_start" class="block text-sm font-medium text-gray-700 mb-1">Work Start Time</label>
                                <input type="time" name="work_start" id="work_start" required step="60"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black">
                            </div>
                            <div>
                                <label for="work_end" class="block text-sm font-medium text-gray-700 mb-1">Work End Time</label>
                                <input type="time" name="work_end" id="work_end" required step="60"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Payroll Details --}}
                <div x-show="currentStep === 3" class="grid grid-cols-1 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-inner">
                        <h4 class="text-xl font-semibold mb-4 text-indigo-700 flex items-center border-b pb-2">
                            <i class="fas fa-money-bill-wave mr-2 text-lg"></i> Payroll Details
                        </h4>
                        <div class="space-y-2">
                            <div>
                                <label for="pay_period_modal" class="block text-sm font-medium text-gray-700 mb-1">Pay Period</label>
                                <select name="pay_period" id="pay_period_modal" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black bg-white">
                                    <option value="" class="text-black">Select pay period</option>
                                    @foreach(['semi-monthly', 'monthly'] as $period)
                                        <option value="{{ $period }}" class="text-black">{{ ucfirst($period) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Monthly Salary Input (initially hidden) --}}
                            <div id="monthlySalaryField" class="hidden">
                                <label for="monthly_salary" class="block text-sm font-medium text-gray-700 mb-1">Monthly Salary</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium">₱</span>
                                    <input type="number" name="monthly_salary" id="monthly_salary"
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                            {{-- Semi-Monthly Salary Input (initially hidden) --}}
                            <div id="semiMonthlySalaryField" class="hidden">
                                <label for="semi_monthly_salary" class="block text-sm font-medium text-gray-700 mb-1">Semi-Monthly Salary</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium">₱</span>
                                    <input type="number" name="semi_monthly_salary" id="semi_monthly_salary"
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-black" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                            {{-- Daily Rate (Auto) --}}
                            <div>
                                <label for="daily_rate_modal" class="block text-sm font-medium text-gray-700 mb-1">Daily Rate (Auto)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium">₱</span>
                                    <input type="number" name="daily_rate" id="daily_rate_modal" required readonly
                                        class="w-full pl-8 pr-3 py-2 border border-dashed border-gray-400 rounded-md shadow-inner bg-gray-100 text-black cursor-not-allowed" step="0.01" value="0.00">
                                </div>
                            </div>
                            {{-- Hourly Rate (Auto) --}}
                            <div>
                                <label for="hourly_rate_modal" class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate (Auto)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium">₱</span>
                                    <input type="number" name="hourly_rate" id="hourly_rate_modal" required readonly
                                        class="w-full pl-8 pr-3 py-2 border border-dashed border-gray-400 rounded-md shadow-inner bg-gray-100 text-black cursor-not-allowed" step="0.01" value="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions (Navigation) --}}
            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 mt-auto">
                <button type="button" onclick="closeEmployeeModal()" class="px-6 py-2 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                    Cancel
                </button>
                <button type="button" x-show="currentStep > 1" @click="currentStep--" class="px-6 py-2 text-sm bg-gray-200 text-gray-700 rounded-md font-medium hover:bg-gray-300 transition duration-150">
                    Previous
                </button>
                <button type="button" x-show="currentStep < totalSteps" @click="currentStep++" class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-lg shadow-indigo-200">
                    Next
                </button>
                <button type="submit" x-show="currentStep === totalSteps" id="saveEmployeeBtn" class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-lg shadow-indigo-200">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
        </div>
</div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dailyRateInputModal = document.getElementById('daily_rate_modal');
        const hourlyRateInputModal = document.getElementById('hourly_rate_modal');
        const payPeriodSelectModal = document.getElementById('pay_period_modal');
        const monthlySalaryField = document.getElementById('monthlySalaryField');
        const semiMonthlySalaryField = document.getElementById('semiMonthlySalaryField');
        const monthlySalaryInput = document.getElementById('monthly_salary');
        const semiMonthlySalaryInput = document.getElementById('semi_monthly_salary');

        const employeeForm = document.getElementById('employeeForm');
        const modalTitle = document.getElementById('modalTitle');
        const methodField = document.getElementById('_methodField');
        const passwordFields = document.getElementById('passwordFields');
        const passwordConfirmationField = document.getElementById('passwordConfirmationField');
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('password_confirmation');
        const saveEmployeeBtn = document.getElementById('saveEmployeeBtn');
        const DAYS_OF_WEEK = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        function normalizeDaysArray(value) {
            if (Array.isArray(value)) {
                return value;
            }

            if (typeof value === 'string' && value.trim() !== '') {
                try {
                    const parsed = JSON.parse(value);
                    if (Array.isArray(parsed)) {
                        return parsed;
                    }
                } catch (error) {
                    console.warn('Unable to parse days array from server response.', error);
                }
            }

            return [];
        }

        function setStoreDays(workingDays, restDays) {
            const store = Alpine.store('employeeStore');
            if (!store) {
                return;
            }
            store.setDays(workingDays, restDays);
        }

        function extractTimePortion(value) {
            if (!value) {
                return '';
            }

            const timeString = String(value);

            if (timeString.includes('T')) {
                const [, time] = timeString.split('T');
                return time ? time.substring(0, 5) : '';
            }

            return timeString.substring(0, 5);
        }

        /**
         * Calculates the number of working days (Mon-Sat) in the current month.
         * Note: This calculation is dynamic but assumes current month and a fixed Mon-Sat schedule.
         * For a robust system, this should ideally be handled on the server side based on pay frequency and fixed working days.
         */
        window.getWorkingDaysInMonth = function(selectedWorkingDaysArr, selectedRestDaysArr) {
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
                
                if (selectedWorkingDaysArr.includes(currentDayName) && !selectedRestDaysArr.includes(currentDayName)) {
                    actualWorkingDays++;
                }
            }
            return actualWorkingDays;
        }

        window.calculateRatesModal = function() {
            // The value from here will be copied to either monthly_salary or semi_monthly_salary before submission.
            // For calculation, we use the value from the currently visible input.
            let basicSalary = 0;
            if (payPeriodSelectModal.value === 'monthly') {
                basicSalary = parseFloat(monthlySalaryInput.value) || 0;
            } else if (payPeriodSelectModal.value === 'semi-monthly') {
                basicSalary = parseFloat(semiMonthlySalaryInput.value) || 0;
            }
            
            const WORKING_HOURS_PER_DAY = 8;
            
            const store = Alpine.store('employeeStore');
            const selectedWorkingDaysArray = store?.workingDays || [];
            const selectedRestDaysArray = store?.restDays || [];
            
            const actualWorkingDaysInMonth = getWorkingDaysInMonth(selectedWorkingDaysArray, selectedRestDaysArray);
            
            let dailyRate = 0;
            let hourlyRate = 0;
            
            console.log('calculateRatesModal - Basic Salary:', basicSalary, 'Actual Working Days:', actualWorkingDaysInMonth);
            if (basicSalary > 0 && actualWorkingDaysInMonth > 0) {
                let effectiveMonthlySalary = basicSalary;
                if (payPeriodSelectModal.value === 'semi-monthly') {
                    effectiveMonthlySalary = basicSalary * 2; // Convert semi-monthly to monthly equivalent
                }
 
                dailyRate = effectiveMonthlySalary / actualWorkingDaysInMonth;
                hourlyRate = dailyRate / WORKING_HOURS_PER_DAY;
            }
            console.log('calculateRatesModal - Calculated Daily Rate:', dailyRate, 'Calculated Hourly Rate:', hourlyRate);

            dailyRateInputModal.value = dailyRate.toFixed(2);
            hourlyRateInputModal.value = hourlyRate.toFixed(2);
        }

        function toggleSalaryInputs() {
            const payPeriod = payPeriodSelectModal.value;
            console.log('Pay period changed to:', payPeriod);
            
            // Hide all salary fields first
            monthlySalaryField.classList.add('hidden');
            semiMonthlySalaryField.classList.add('hidden');
            // Remove 'required' from all to prevent conflicts
            monthlySalaryInput.removeAttribute('required');
            semiMonthlySalaryInput.removeAttribute('required');
            
            // Show and set 'required' for the relevant field
            if (payPeriod === 'monthly') {
                monthlySalaryField.classList.remove('hidden');
                monthlySalaryInput.setAttribute('required', 'required');
                // basicSalaryInputModal.value = monthlySalaryInput.value; // Sync with generic input if needed
            } else if (payPeriod === 'semi-monthly') {
                semiMonthlySalaryField.classList.remove('hidden');
                semiMonthlySalaryInput.setAttribute('required', 'required');
                // basicSalaryInputModal.value = semiMonthlySalaryInput.value; // Sync with generic input if needed
            }
            calculateRatesModal(); // Recalculate rates based on new visible input
        }

        // --- Event Listeners and Modal Functions ---
        
        // Listeners for rate calculation
        payPeriodSelectModal.addEventListener('change', toggleSalaryInputs);
        monthlySalaryInput.addEventListener('input', calculateRatesModal);
        semiMonthlySalaryInput.addEventListener('input', calculateRatesModal);

        // Open/Close Modal
        window.openEmployeeModal = function(employee = null) {
            console.log('openEmployeeModal called with employee:', employee);
            document.getElementById('employeeModal').classList.remove('hidden');
            document.getElementById('employeeModal').classList.add('flex');
            
            if (employee) {
                console.log('Opening modal in EDIT mode. Employee data:', employee);
                // Edit mode
                modalTitle.textContent = 'Edit Employee';
                employeeForm.action = '/employees/' + employee.id;
                methodField.value = 'PUT';
                saveEmployeeBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Update Employee';

                // Populate form fields
                document.getElementById('first_name').value = employee.first_name;
                document.getElementById('last_name').value = employee.last_name;
                document.getElementById('email').value = employee.email;
                document.getElementById('employee_id').value = employee.employee_id || '';
                document.getElementById('position').value = employee.position;
                document.getElementById('role').value = employee.role;
                document.getElementById('department_id').value = employee.department_id; // Populate department_id
                // document.getElementById('basic_salary_modal').value = employee.basic_salary; // This generic field is no longer used for direct input
                document.getElementById('pay_period_modal').value = employee.pay_schedule;
                
                const workStartSource = employee.work_start_time != null ? employee.work_start_time : employee.work_start;
                const workEndSource = employee.work_end_time != null ? employee.work_end_time : employee.work_end;
                const workStartValue = extractTimePortion(workStartSource);
                const workEndValue = extractTimePortion(workEndSource);
                document.getElementById('work_start').value = workStartValue || '00:00';
                document.getElementById('work_end').value = workEndValue || '00:00';
                console.log('Employee Work Start (raw):', employee.work_start, 'Employee Work End (raw):', employee.work_end);
                console.log('Work Start Time (EDIT mode) after setting:', document.getElementById('work_start').value, 'Work End Time (EDIT mode) after setting:', document.getElementById('work_end').value);
                
                document.getElementById('start_date').value = employee.start_date; // Populate start_date
                document.getElementById('shift_id').value = employee.shift_id; // Populate shift_id

                console.log('Raw working days from API:', employee.working_days);
                console.log('Raw rest days from API:', employee.rest_days);
                const normalizedWorkingDays = normalizeDaysArray(employee.working_days);
                const normalizedRestDays = normalizeDaysArray(employee.rest_days);
                console.log('Normalized working days:', normalizedWorkingDays, 'Normalized rest days:', normalizedRestDays);
                employee.working_days = normalizedWorkingDays;
                employee.rest_days = normalizedRestDays;
                employee.work_start = workStartValue;
                employee.work_end = workEndValue;

                // Update Alpine store with current employee data
                Alpine.store('employeeStore').setEmployee(employee);
                setStoreDays(normalizedWorkingDays, normalizedRestDays);

                // Hide password fields for edit mode (password changes can be a separate flow if needed)
                passwordFields.classList.add('hidden');
                passwordConfirmationField.classList.add('hidden');
                passwordInput.removeAttribute('required');
                passwordConfirmationInput.removeAttribute('required');
                
                // Show the correct salary input based on pay period and populate it
                toggleSalaryInputs(); // Call to initially set visibility and required attributes
                if (employee.pay_schedule === 'monthly') {
                    monthlySalaryInput.value = employee.basic_salary;
                } else if (employee.pay_schedule === 'semi-monthly') {
                    semiMonthlySalaryInput.value = employee.basic_salary;
                }
                
                // Recalculate rates for the existing employee within Alpine.nextTick
                Alpine.nextTick(() => {
                    calculateRatesModal();
                    console.log('After calling calculateRatesModal in EDIT mode (Alpine.nextTick). Daily Rate:', dailyRateInputModal.value, 'Hourly Rate:', hourlyRateInputModal.value);
                });
            } else {
                console.log('Opening modal in ADD mode.');
                // Add mode
                modalTitle.textContent = 'Add New Employee';
                employeeForm.action = "{{ route('employees.store') }}";
                methodField.value = 'POST';
                saveEmployeeBtn.innerHTML = '<i class="fas fa-user-plus mr-1"></i> Save Employee';
                
                employeeForm.reset();
                // Show password fields for add mode
                passwordFields.classList.remove('hidden');
                passwordConfirmationField.classList.remove('hidden');
                passwordInput.setAttribute('required', 'required');
                passwordConfirmationInput.setAttribute('required', 'required');
                document.getElementById('shift_id').value = ''; // Reset shift for new employee
                document.getElementById('department_id').value = ''; // Reset department for new employee

                // Set current time for work_start and work_end AFTER form reset
                const now = new Date();
                const currentTime = now.toTimeString().substring(0, 5);
                console.log('Attempting to set Work Start Time (ADD mode) to:', currentTime);
                document.getElementById('work_start').value = currentTime;
                console.log('Work Start Time (ADD mode) immediately after setting:', document.getElementById('work_start').value);
                
                console.log('Attempting to set Work End Time (ADD mode) to:', currentTime);
                document.getElementById('work_end').value = currentTime;
                console.log('Work End Time (ADD mode) immediately after setting:', document.getElementById('work_end').value);
                console.log('Work Start Time (ADD mode) after reset:', document.getElementById('work_start').value, 'Work End Time (ADD mode) after reset:', document.getElementById('work_end').value);

                setStoreDays([], []);

                // Initialize calculated rates on open for add mode
                monthlySalaryInput.value = '';
                semiMonthlySalaryInput.value = '';
                payPeriodSelectModal.value = ''; // No default, force selection
                toggleSalaryInputs(); // Ensure fields are hidden initially
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

        // Initial call to set correct visibility on page load or modal open
        toggleSalaryInputs();

    });
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('employeeStore', {
            currentEmployee: null,
            workingDays: [],
            restDays: [],
            setEmployee(employee) {
                this.currentEmployee = employee;
            },
            setDays(workingDays = [], restDays = []) {
                this.workingDays = Array.isArray(workingDays) ? [...workingDays] : [];
                this.restDays = Array.isArray(restDays) ? [...restDays] : [];
            },
            resetDays() {
                this.setDays([], []);
            }
        });
    });
</script>
