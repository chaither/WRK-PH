<div id="hmoDeductionModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 p-2" x-data="hmoDeductionData()" x-init="fetchDeductions()">
    <div class="bg-white rounded-xl shadow-2xl p-4 max-w-xl sm:max-w-4xl w-full max-h-screen-70 overflow-y-auto transform transition-all duration-300 scale-100">
        <div class="flex justify-between items-center mb-3 border-b pb-2">
            <h2 class="text-lg font-bold text-gray-800">🏥 Manage HMO Deductions</h2>
            <button type="button" @click="closeHmoDeductionModal()" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Left Column: Current Deductions -->
            <div>
                <h3 class="text-base font-semibold text-gray-700 mb-2">Current HMO Deductions</h3>
                <div class="max-h-80 overflow-y-auto pr-1">
                    <template x-if="deductions.length === 0">
                        <p class="text-gray-600 text-sm">No HMO deductions configured yet.</p>
                    </template>
                    <template x-for="deduction in deductions" :key="deduction.id">
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mb-2 ">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 cursor-pointer" @click="expandedDeductionId = (expandedDeductionId === deduction.id ? null : deduction.id)">
                                <div>
                                    <p class="font-medium text-gray-800 text-sm" x-text="deduction.name"></p>
                                    <p class="text-xs text-gray-600">
                                        Employee Share: <span x-text="formatCurrency(deduction.employee_share)"></span>
                                    </p>
                                </div>
                                <div class="flex space-x-2 mt-2 sm:mt-0">
                                    <button type="button" @click.stop="editDeduction(deduction)" class="text-blue-600 hover:text-blue-800 p-1 rounded-full hover:bg-blue-100 transition duration-150" title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button type="button" @click.stop="deleteDeduction(deduction.id)" class="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100 transition duration-150" title="Delete">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Expandable Details Section -->
                            <div x-show="expandedDeductionId === deduction.id" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90" class="mt-3 pt-3 border-t border-gray-200 text-xs text-gray-700">
                                <p class="font-semibold mb-1">Details:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Amount Type: <span x-text="deduction.is_percentage ? 'Percentage Based' : 'Fixed Amount'"></span></li>
                                    <template x-if="!deduction.is_percentage">
                                        <li>Applies To: <span x-text="formatTargetType(deduction.target_type)"></span>
                                            <template x-if="deduction.target_type === 'employees' && deduction.applies_to && deduction.applies_to.length > 0">
                                                <ul class="list-disc list-inside ml-4">
                                                    <template x-for="employeeId in deduction.applies_to" :key="`emp-${employeeId}`">
                                                        <li x-text="getEmployeeNameById(employeeId)"></li>
                                                    </template>
                                                </ul>
                                            </template>
                                            <template x-if="deduction.target_type === 'departments' && deduction.applies_to && deduction.applies_to.length > 0">
                                                <ul class="list-disc list-inside ml-4">
                                                    <template x-for="departmentId in deduction.applies_to" :key="`dept-${departmentId}`">
                                                        <li x-text="getDepartmentNameById(departmentId)"></li>
                                                    </template>
                                                </ul>
                                            </template>
                                        </li>
                                    </template>
                                    <li>Deduction Frequency: <span x-text="deduction.deduction_frequency === 'semi_monthly' ? 'Semi-Monthly Deduction' : 'Full Monthly Deduction (First Half Payroll)'"></span></li>
                                    <li>Frequency Applies To: <span x-text="formatTargetType(deduction.deduction_frequency_target_type)"></span>
                                        <template x-if="deduction.deduction_frequency_target_type === 'employees' && deduction.deduction_frequency_applies_to && deduction.deduction_frequency_applies_to.length > 0">
                                            <ul class="list-disc list-inside ml-4">
                                                <template x-for="employeeId in deduction.deduction_frequency_applies_to" :key="`df-emp-${employeeId}`">
                                                    <li x-text="getEmployeeNameById(employeeId)"></li>
                                                </template>
                                            </ul>
                                        </template>
                                        <template x-if="deduction.deduction_frequency_target_type === 'departments' && deduction.deduction_frequency_applies_to && deduction.deduction_frequency_applies_to.length > 0">
                                            <ul class="list-disc list-inside ml-4">
                                                <template x-for="departmentId in deduction.deduction_frequency_applies_to" :key="`df-dept-${departmentId}`">
                                                    <li x-text="getDepartmentNameById(departmentId)"></li>
                                                </template>
                                            </ul>
                                        </template>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right Column: Add/Edit Deduction Form -->
            <div class="border-t md:border-t-0 md:border-l md:pl-4 pt-4 md:pt-0 mt-4 md:mt-0">
                <h3 class="text-base font-semibold text-gray-700 mb-2" x-text="isEditMode ? 'Edit HMO Deduction' : 'Add New HMO Deduction'"></h3>
                <form @submit.prevent="isEditMode ? updateDeduction() : addDeduction()" class="space-y-3">
                    <div>
                        <label for="deduction_name" class="block text-xs font-medium text-gray-700 mb-1">HMO Plan Name</label>
                        <input type="text" id="deduction_name" x-model="form.name" required
                            placeholder="e.g., Maxicare Premium, Medicare Plus"
                            class="w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 text-sm transition-colors duration-200">
                    </div>

                    <div class="mt-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Amount Type</label>
                        <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-4 sm:space-y-0">
                            <label class="inline-flex items-center text-sm">
                                <input type="radio" value="percentage" x-model="form.amount_type" @change="form.target_type = 'all'; form.applies_to = []" class="form-radio h-4 w-4 text-blue-600" aria-label="Percentage Based">
                                <span class="ml-2 text-gray-700">Percentage Based</span>
                            </label>
                            <label class="inline-flex items-center text-sm">
                                <input type="radio" value="fixed" x-model="form.amount_type" class="form-radio h-4 w-4 text-blue-600" aria-label="Fixed Amount (Non-percentage Based)">
                                <span class="ml-2 text-gray-700">Fixed Amount</span>
                            </label>
                        </div>
                    </div>

                    <template x-if="form.amount_type === 'fixed'">
                        <div x-show="form.amount_type === 'fixed'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
                            <div class="mt-3">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Apply To:</label>
                                <div class="flex flex-col space-y-2 pl-4">
                                    <label class="inline-flex items-center text-sm">
                                        <input type="radio" value="all" x-model="form.target_type" class="form-radio h-4 w-4 text-blue-600" aria-label="All Employees" :disabled="form.amount_type === 'percentage'">
                                        <span class="ml-2 text-gray-700">All Employees</span>
                                    </label>
                                    <label class="inline-flex items-center text-sm">
                                        <input type="radio" value="employees" x-model="form.target_type" class="form-radio h-4 w-4 text-blue-600" aria-label="Specific Employees" :disabled="form.amount_type === 'percentage'">
                                        <span class="ml-2 text-gray-700">Specific Employees</span>
                                    </label>
                                    <label class="inline-flex items-center text-sm">
                                        <input type="radio" value="departments" x-model="form.target_type" class="form-radio h-4 w-4 text-blue-600" aria-label="Specific Departments" :disabled="form.amount_type === 'percentage'">
                                        <span class="ml-2 text-gray-700">Specific Departments</span>
                                    </label>
                                </div>
                            </div>

                            <template x-if="form.target_type === 'employees'">
                                <div x-show="form.target_type === 'employees'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90" class="mt-3 pl-4">
                                    <label for="select_employees" class="block text-xs font-medium text-gray-700 mb-1">Select Employees:</label>
                                    <div class="relative" x-data="{ open: false, search: '', selectedEmployees: [] }" @keydown.escape.stop="open = false" @keydown.tab="open = false">
                                        <button type="button" @click="open = !open" @click.away="open = false" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-8 py-1.5 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm" aria-haspopup="listbox" :aria-expanded="open ? 'true' : 'false'" aria-labelledby="listbox-label-employees">
                                            <span class="block truncate" x-text="selectedEmployees.length ? `${selectedEmployees.length} employee(s) selected` : 'Select employees'"></span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </button>

                                        <ul x-show="open" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-48 rounded-md py-1 text-sm ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="listbox" aria-labelledby="listbox-label-employees">
                                            <div class="px-2 py-1" @click.stop="">
                                                <input type="text" x-model="search" placeholder="Search employees..." class="w-full px-2 py-1 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm" @keydown.escape.stop="open = false" @keydown.tab="open = false" aria-label="Search employees">
                                            </div>
                                            <template x-for="employee in allEmployees.filter(employee => getEmployeeName(employee).toLowerCase().includes(search.toLowerCase()))" :key="employee.id">
                                                <li @click.stop="event => {
                                                        const employeeId = employee.id;
                                                        const index = selectedEmployees.indexOf(employeeId);
                                                        if (index > -1) {
                                                            selectedEmployees.splice(index, 1);
                                                        } else {
                                                            selectedEmployees.push(employeeId);
                                                        }
                                                        form.applies_to = selectedEmployees;
                                                        $dispatch('input', form.applies_to);
                                                    }"
                                                    class="text-gray-900 cursor-default select-none relative py-1.5 pl-3 pr-9 hover:bg-blue-600 hover:text-white"
                                                    :class="{ 'bg-blue-600 text-white': selectedEmployees.includes(employee.id) }"
                                                    id="employee-option-" role="option" :aria-selected="selectedEmployees.includes(employee.id)">
                                                    <div class="flex items-center">
                                                        <input type="checkbox" :checked="selectedEmployees.includes(employee.id)" class="form-checkbox h-4 w-4 text-blue-600 pointer-events-none" aria-hidden="true">
                                                        <span class="font-normal ml-3 block truncate text-sm" :class="{ 'font-semibold': selectedEmployees.includes(employee.id), 'font-normal': !selectedEmployees.includes(employee.id) }" x-text="getEmployeeName(employee)"></span>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-1" x-show="selectedEmployees.length > 0" role="list" aria-label="Selected employees">
                                        <template x-for="employeeId in selectedEmployees" :key="employeeId">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" role="listitem">
                                                <span x-text="getEmployeeNameById(employeeId)"></span>
                                                <button type="button" @click.stop="event => {
                                                    const index = selectedEmployees.indexOf(employeeId);
                                                    if (index > -1) {
                                                        selectedEmployees.splice(index, 1);
                                                    }
                                                    form.applies_to = selectedEmployees;
                                                    $dispatch('input', form.applies_to);
                                                }" class="flex-shrink-0 ml-1 h-3 w-3 rounded-full inline-flex items-center justify-center text-blue-400 hover:bg-blue-200 hover:text-blue-500 focus:outline-none focus:bg-blue-200 focus:text-blue-500" :aria-label="`Remove ${getEmployeeNameById(employeeId)}`">
                                                    <span class="sr-only">Remove employee</span>
                                                    <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                        <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                    <p x-show="form.target_type === 'employees' && selectedEmployees.length === 0 && form.amount_type === 'fixed'" class="text-red-500 text-xs mt-1" id="employee-selection-error">Please select at least one employee.</p>
                                </div>
                            </template>

                            <template x-if="form.target_type === 'departments'">
                                <div x-show="form.target_type === 'departments'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90" class="mt-3 pl-4">
                                    <label for="select_departments" class="block text-xs font-medium text-gray-700 mb-1">Select Department:</label>
                                    <div class="relative" x-data="{ open: false, search: '', selectedDepartment: null }" @keydown.escape.stop="open = false" @keydown.tab="open = false">
                                        <button type="button" @click="open = !open" @click.away="open = false" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-8 py-1.5 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm" aria-haspopup="listbox" :aria-expanded="open ? 'true' : 'false'" aria-labelledby="listbox-label-departments">
                                            <span class="block truncate" x-text="selectedDepartment ? allDepartments.find(d => d.id === selectedDepartment)?.name : 'Select a department'"></span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </button>

                                        <ul x-show="open" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-48 rounded-md py-1 text-sm ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="listbox" aria-labelledby="listbox-label-departments">
                                            <div class="px-2 py-1" @click.stop="">
                                                <input type="text" x-model="search" placeholder="Search departments..." class="w-full px-2 py-1 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm" @keydown.escape.stop="open = false" @keydown.tab="open = false" aria-label="Search departments">
                                            </div>
                                            <template x-for="department in allDepartments.filter(d => d.name.toLowerCase().includes(search.toLowerCase()))" :key="department.id">
                                                <li @click.stop="event => {
                                                        selectedDepartment = department.id;
                                                        form.applies_to = [department.id];
                                                        open = false;
                                                        $dispatch('input', form.applies_to);
                                                    }"
                                                    class="text-gray-900 cursor-default select-none relative py-1.5 pl-3 pr-9 hover:bg-blue-600 hover:text-white"
                                                    :class="{ 'bg-blue-600 text-white': selectedDepartment === department.id }"
                                                    id="department-option-" role="option" :aria-selected="selectedDepartment === department.id">
                                                    <span class="font-normal block truncate text-sm" :class="{ 'font-semibold': selectedDepartment === department.id, 'font-normal': selectedDepartment !== department.id }" x-text="department.name"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <p x-show="form.target_type === 'departments' && !selectedDepartment && form.amount_type === 'fixed'" class="text-red-500 text-xs mt-1" id="department-selection-error">Please select a department.</p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div>
                        <label for="employee_share" class="block text-xs font-medium text-gray-700 mb-1" x-text="form.amount_type === 'percentage' ? 'Employee Share (%)' : 'Employee Share (₱)'"></label>
                        <input type="number" min="0" :max="form.amount_type === 'percentage' ? '100' : null" step="0.01" id="employee_share" x-model.number="form.employee_share" required
                            :placeholder="form.amount_type === 'percentage' ? 'e.g., 5.5' : 'e.g., 500.00'"
                            class="w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 text-sm transition-colors duration-200">
                        <p x-show="form.amount_type === 'percentage' && (form.employee_share < 0 || form.employee_share > 100)" class="text-red-500 text-xs mt-1">Employee share must be between 0 and 100 for percentage-based deductions.</p>
                    </div>

                    <div>
                        <label for="deduction_frequency" class="block text-xs font-medium text-gray-700 mb-1">Deduction Frequency</label>
                        <select id="deduction_frequency" x-model="form.deduction_frequency" required
                            class="w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 text-sm transition-colors duration-200">
                            <option value="semi_monthly">Semi-Monthly Deduction</option>
                            <option value="first_half_monthly">Full Monthly Deduction</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Deduction Frequency Apply To:</label>
                        <div class="flex flex-col space-y-2 pl-4">
                            <label class="inline-flex items-center text-sm">
                                <input type="radio" value="all" x-model="form.deduction_frequency_target_type" class="form-radio h-4 w-4 text-blue-600" aria-label="All Employees for Deduction Frequency">
                                <span class="ml-2 text-gray-700">All Employees</span>
                            </label>
                            <label class="inline-flex items-center text-sm">
                                <input type="radio" value="employees" x-model="form.deduction_frequency_target_type" class="form-radio h-4 w-4 text-blue-600" aria-label="Specific Employees for Deduction Frequency">
                                <span class="ml-2 text-gray-700">Specific Employees</span>
                            </label>
                            <label class="inline-flex items-center text-sm">
                                <input type="radio" value="departments" x-model="form.deduction_frequency_target_type" class="form-radio h-4 w-4 text-blue-600" aria-label="Specific Departments for Deduction Frequency">
                                <span class="ml-2 text-gray-700">Specific Departments</span>
                            </label>
                        </div>
                    </div>

                    <template x-if="form.deduction_frequency_target_type === 'employees'">
                        <div x-show="form.deduction_frequency_target_type === 'employees'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90" class="mt-3 pl-4">
                            <label for="select_df_employees" class="block text-xs font-medium text-gray-700 mb-1">Select Employees for Deduction Frequency:</label>
                            <div class="relative" x-data="{ open: false, search: '', selectedDfEmployees: [] }" @keydown.escape.stop="open = false" @keydown.tab="open = false">
                                <button type="button" @click="open = !open" @click.away="open = false" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-8 py-1.5 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm" aria-haspopup="listbox" :aria-expanded="open ? 'true' : 'false'" aria-labelledby="listbox-label-df-employees">
                                    <span class="block truncate" x-text="selectedDfEmployees.length ? `${selectedDfEmployees.length} employee(s) selected` : 'Select employees'"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>

                                <ul x-show="open" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-48 rounded-md py-1 text-sm ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="listbox" aria-labelledby="listbox-label-df-employees">
                                    <div class="px-2 py-1" @click.stop="">
                                        <input type="text" x-model="search" placeholder="Search employees..." class="w-full px-2 py-1 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm" @keydown.escape.stop="open = false" @keydown.tab="open = false" aria-label="Search employees">
                                    </div>
                                    <template x-for="employee in filteredEmployees.filter(employee => getEmployeeName(employee).toLowerCase().includes(search.toLowerCase()))" :key="employee.id">
                                        <li @click.stop="event => {
                                                const employeeId = employee.id;
                                                const index = selectedDfEmployees.indexOf(employeeId);
                                                if (index > -1) {
                                                    selectedDfEmployees.splice(index, 1);
                                                } else {
                                                    selectedDfEmployees.push(employeeId);
                                                }
                                                form.deduction_frequency_applies_to = selectedDfEmployees;
                                                $dispatch('input', form.deduction_frequency_applies_to);
                                            }"
                                            class="text-gray-900 cursor-default select-none relative py-1.5 pl-3 pr-9 hover:bg-blue-600 hover:text-white"
                                            :class="{ 'bg-blue-600 text-white': selectedDfEmployees.includes(employee.id) }"
                                            id="df-employee-option-" role="option" :aria-selected="selectedDfEmployees.includes(employee.id)">
                                            <div class="flex items-center">
                                                <input type="checkbox" :checked="selectedDfEmployees.includes(employee.id)" class="form-checkbox h-4 w-4 text-blue-600 pointer-events-none" aria-hidden="true">
                                                <span class="font-normal ml-3 block truncate text-sm" :class="{ 'font-semibold': selectedDfEmployees.includes(employee.id), 'font-normal': !selectedDfEmployees.includes(employee.id) }" x-text="getEmployeeName(employee)"></span>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1" x-show="selectedDfEmployees.length > 0" role="list" aria-label="Selected employees for deduction frequency">
                                <template x-for="employeeId in selectedDfEmployees" :key="employeeId">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" role="listitem">
                                        <span x-text="getEmployeeNameById(employeeId)"></span>
                                        <button type="button" @click.stop="event => {
                                            const index = selectedDfEmployees.indexOf(employeeId);
                                            if (index > -1) {
                                                selectedDfEmployees.splice(index, 1);
                                            }
                                            form.deduction_frequency_applies_to = selectedDfEmployees;
                                            $dispatch('input', form.deduction_frequency_applies_to);
                                        }" class="flex-shrink-0 ml-1 h-3 w-3 rounded-full inline-flex items-center justify-center text-blue-400 hover:bg-blue-200 hover:text-blue-500 focus:outline-none focus:bg-blue-200 focus:text-blue-500" :aria-label="`Remove ${getEmployeeNameById(employeeId)}`">
                                            <span class="sr-only">Remove employee</span>
                                            <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <p x-show="form.deduction_frequency_target_type === 'employees' && selectedDfEmployees.length === 0" class="text-red-500 text-xs mt-1">Please select at least one employee for deduction frequency targeting.</p>
                        </div>
                    </template>

                    <template x-if="form.deduction_frequency_target_type === 'departments'">
                        <div x-show="form.deduction_frequency_target_type === 'departments'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90" class="mt-3 pl-4">
                            <label for="select_df_departments" class="block text-xs font-medium text-gray-700 mb-1">Select Departments for Deduction Frequency:</label>
                            <div class="relative" x-data="{ open: false, search: '', selectedDfDepartments: [] }" @keydown.escape.stop="open = false" @keydown.tab="open = false">
                                <button type="button" @click="open = !open" @click.away="open = false" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-8 py-1.5 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm" aria-haspopup="listbox" :aria-expanded="open ? 'true' : 'false'" aria-labelledby="listbox-label-df-departments">
                                    <span class="block truncate" x-text="selectedDfDepartments.length ? `${selectedDfDepartments.length} department(s) selected` : 'Select departments'"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>

                                <ul x-show="open" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-48 rounded-md py-1 text-sm ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="listbox" aria-labelledby="listbox-label-df-departments">
                                    <div class="px-2 py-1" @click.stop="">
                                        <input type="text" x-model="search" placeholder="Search departments..." class="w-full px-2 py-1 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm" @keydown.escape.stop="open = false" @keydown.tab="open = false" aria-label="Search departments">
                                    </div>
                                    <template x-for="department in filteredDepartments.filter(d => d.name.toLowerCase().includes(search.toLowerCase()))" :key="department.id">
                                        <li @click.stop="event => {
                                                const departmentId = department.id;
                                                const index = selectedDfDepartments.indexOf(departmentId);
                                                if (index > -1) {
                                                    selectedDfDepartments.splice(index, 1);
                                                } else {
                                                    selectedDfDepartments.push(departmentId);
                                                }
                                                form.deduction_frequency_applies_to = selectedDfDepartments;
                                                $dispatch('input', form.deduction_frequency_applies_to);
                                            }"
                                            class="text-gray-900 cursor-default select-none relative py-1.5 pl-3 pr-9 hover:bg-blue-600 hover:text-white"
                                            :class="{ 'bg-blue-600 text-white': selectedDfDepartments.includes(department.id) }"
                                            id="df-department-option-" role="option" :aria-selected="selectedDfDepartments.includes(department.id)">
                                            <div class="flex items-center">
                                                <input type="checkbox" :checked="selectedDfDepartments.includes(department.id)" class="form-checkbox h-4 w-4 text-blue-600 pointer-events-none" aria-hidden="true">
                                                <span class="font-normal ml-3 block truncate text-sm" :class="{ 'font-semibold': selectedDfDepartments.includes(department.id), 'font-normal': !selectedDfDepartments.includes(department.id) }" x-text="department.name"></span>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1" x-show="selectedDfDepartments.length > 0" role="list" aria-label="Selected departments for deduction frequency">
                                <template x-for="departmentId in selectedDfDepartments" :key="departmentId">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" role="listitem">
                                        <span x-text="getDepartmentNameById(departmentId)"></span>
                                        <button type="button" @click.stop="event => {
                                            const index = selectedDfDepartments.indexOf(departmentId);
                                            if (index > -1) {
                                                selectedDfDepartments.splice(index, 1);
                                            }
                                            form.deduction_frequency_applies_to = selectedDfDepartments;
                                            $dispatch('input', form.deduction_frequency_applies_to);
                                        }" class="flex-shrink-0 ml-1 h-3 w-3 rounded-full inline-flex items-center justify-center text-blue-400 hover:bg-blue-200 hover:text-blue-500 focus:outline-none focus:bg-blue-200 focus:text-blue-500" :aria-label="`Remove ${getDepartmentNameById(departmentId)}`">
                                            <span class="sr-only">Remove department</span>
                                            <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <p x-show="form.deduction_frequency_target_type === 'departments' && selectedDfDepartments.length === 0" class="text-red-500 text-xs mt-1">Please select at least one department for deduction frequency targeting.</p>
                        </div>
                    </template>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeHmoDeductionModal()" class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                            Cancel
                        </button>
                        <button type="submit" class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
                            <i class="fas fa-save mr-1"></i> <span x-text="isEditMode ? 'Update Deduction' : 'Save Deduction'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function openHmoDeductionModal() {
        document.getElementById('hmoDeductionModal').classList.remove('hidden');
        document.getElementById('hmoDeductionModal').classList.add('flex');
    }

    function closeHmoDeductionModal() {
        const modal = document.getElementById('hmoDeductionModal');
        if (!modal) {
            return;
        }
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        const alpineComponent = modal.__x;
        if (alpineComponent && typeof alpineComponent.$data.resetForm === 'function') {
            alpineComponent.$data.resetForm();
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('hmoDeductionData', () => ({
            deductions: [],
            allEmployees: [],
            allDepartments: [],
            form: {
                id: null,
                name: '',
                amount_type: 'percentage',
                employee_share: '',
                target_type: 'all',
                applies_to: [],
                deduction_frequency: 'semi_monthly',
                deduction_frequency_target_type: 'all',
                deduction_frequency_applies_to: [],
            },
            isEditMode: false,
            expandedDeductionId: null,

            get filteredEmployees() {
                if (this.form.deduction_frequency === 'semi_monthly' || this.form.deduction_frequency === 'first_half_monthly') {
                    return this.allEmployees.filter(employee => employee.pay_schedule === 'semi-monthly');
                }
                return this.allEmployees;
            },

            get filteredDepartments() {
                if (this.form.deduction_frequency === 'semi_monthly' || this.form.deduction_frequency === 'first_half_monthly') {
                    const semiMonthlyDepartmentIds = new Set(this.allEmployees
                        .filter(employee => employee.pay_schedule === 'semi-monthly')
                        .map(employee => employee.department_id)
                        .filter(id => id !== null)
                    );
                    return this.allDepartments.filter(department => semiMonthlyDepartmentIds.has(department.id));
                }
                return this.allDepartments;
            },

            init() {
                this.fetchEmployees();
                this.fetchDepartments();
                this.fetchDeductions();
                this.$watch('form.amount_type', (value) => {
                    if (value === 'percentage') {
                        this.form.target_type = 'all';
                        this.form.applies_to = [];
                    }
                });

                this.$watch('form.deduction_frequency_target_type', (value) => {
                    if (value === 'all') {
                        this.form.deduction_frequency_applies_to = [];
                    }
                });

                this.$watch('form.deduction_frequency', (value) => {
                    this.form.deduction_frequency_applies_to = [];
                    this.form.deduction_frequency_target_type = 'all';
                });

                this.$nextTick(() => {
                    this.selectedEmployees = this.form.applies_to;
                    this.selectedDepartment = this.form.applies_to[0] || null;
                    this.selectedDfEmployees = this.form.deduction_frequency_applies_to;
                    this.selectedDfDepartments = this.form.deduction_frequency_applies_to || [];
                });
            },

            resetForm() {
                this.form = {
                    id: null,
                    name: '',
                    amount_type: 'percentage',
                    employee_share: '',
                    target_type: 'all',
                    applies_to: [],
                    deduction_frequency: 'semi_monthly',
                    deduction_frequency_target_type: 'all',
                    deduction_frequency_applies_to: [],
                };
                this.isEditMode = false;
            },

            async fetchDeductions() {
                try {
                    const response = await fetch('/hmo-deductions');
                    if (!response.ok) throw new Error('Failed to fetch HMO deductions');
                    this.deductions = await response.json();
                    console.log('Fetched HMO deductions:', this.deductions);
                } catch (error) {
                    console.error('Error fetching HMO deductions:', error);
                    alert('Error fetching HMO deductions.');
                }
            },

            async fetchEmployees() {
                try {
                    const response = await fetch('/api/employees', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    });
                    if (!response.ok) throw new Error('Failed to fetch employees');
                    this.allEmployees = await response.json();
                    console.log('Fetched employees:', this.allEmployees);
                } catch (error) {
                    console.error('Error fetching employees:', error);
                }
            },

            async fetchDepartments() {
                try {
                    const response = await fetch('/api/departments', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    });
                    if (!response.ok) throw new Error('Failed to fetch departments');
                    this.allDepartments = await response.json();
                    console.log('Fetched departments:', this.allDepartments);
                } catch (error) {
                    console.error('Error fetching departments:', error);
                }
            },

            async addDeduction() {
                if (this.form.amount_type === 'percentage' && (this.form.employee_share < 0 || this.form.employee_share > 100)) {
                    alert('Employee share must be between 0 and 100 for percentage-based deductions.');
                    return;
                }
                if (this.form.amount_type === 'fixed' && this.form.target_type === 'employees' && this.form.applies_to.length === 0) {
                    alert('Please select at least one employee for specific employee targeting.');
                    return;
                }
                if (this.form.amount_type === 'fixed' && this.form.target_type === 'departments' && this.form.applies_to.length === 0) {
                    alert('Please select a department for specific department targeting.');
                    return;
                }

                if (this.form.deduction_frequency_target_type === 'employees' && this.form.deduction_frequency_applies_to.length === 0) {
                    alert('Please select at least one employee for deduction frequency targeting.');
                    return;
                }
                if (this.form.deduction_frequency_target_type === 'departments' && this.form.deduction_frequency_applies_to.length === 0) {
                    alert('Please select at least one department for deduction frequency targeting.');
                    return;
                }

                try {
                    const payload = {
                        ...this.form,
                        is_percentage: this.form.amount_type === 'percentage' ? 1 : 0,
                        target_type: this.form.amount_type === 'fixed' ? this.form.target_type : 'all',
                        applies_to: this.form.amount_type === 'fixed' ? this.form.applies_to : [],
                        deduction_frequency_target_type: this.form.deduction_frequency_target_type === 'all' ? 'all' : this.form.deduction_frequency_target_type,
                        deduction_frequency_applies_to: this.form.deduction_frequency_target_type === 'all' ? [] : this.form.deduction_frequency_applies_to,
                    };

                    const response = await fetch('/hmo-deductions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify(payload),
                    });
                    if (!response.ok) throw new Error('Failed to add HMO deduction');
                    Alpine.nextTick(() => this.fetchDeductions());
                    this.resetForm();
                    alert('HMO deduction added successfully!');
                } catch (error) {
                    console.error('Error adding HMO deduction:', error);
                    alert('Error adding HMO deduction.');
                }
            },

            editDeduction(deduction) {
                this.form.id = deduction.id;
                this.form.name = deduction.name;
                this.form.amount_type = deduction.is_percentage === 1 ? 'percentage' : 'fixed';
                this.form.target_type = deduction.target_type || 'all';
                this.form.applies_to = deduction.applies_to || [];
                this.form.employee_share = deduction.employee_share;
                this.form.deduction_frequency = deduction.deduction_frequency || 'semi_monthly';
                this.form.deduction_frequency_target_type = deduction.deduction_frequency_target_type || 'all';
                this.form.deduction_frequency_applies_to = deduction.deduction_frequency_applies_to || [];
                this.isEditMode = true;
                console.log('Editing HMO deduction. Form data:', this.form);
                openHmoDeductionModal();

                this.$nextTick(() => {
                    this.selectedEmployees = this.form.applies_to;
                    this.selectedDepartment = this.form.applies_to[0] || null;
                    this.selectedDfEmployees = this.form.deduction_frequency_applies_to;
                    this.selectedDfDepartments = this.form.deduction_frequency_applies_to || [];
                });
            },

            async updateDeduction() {
                if (this.form.amount_type === 'percentage' && (this.form.employee_share < 0 || this.form.employee_share > 100)) {
                    alert('Employee share must be between 0 and 100 for percentage-based deductions.');
                    return;
                }
                if (this.form.amount_type === 'fixed' && this.form.target_type === 'employees' && this.form.applies_to.length === 0) {
                    alert('Please select at least one employee for specific employee targeting.');
                    return;
                }
                if (this.form.amount_type === 'fixed' && this.form.target_type === 'departments' && this.form.applies_to.length === 0) {
                    alert('Please select a department for specific department targeting.');
                    return;
                }

                if (this.form.deduction_frequency_target_type === 'employees' && this.form.deduction_frequency_applies_to.length === 0) {
                    alert('Please select at least one employee for deduction frequency targeting.');
                    return;
                }
                if (this.form.deduction_frequency_target_type === 'departments' && this.form.deduction_frequency_applies_to.length === 0) {
                    alert('Please select at least one department for deduction frequency targeting.');
                    return;
                }

                try {
                    const payload = {
                        ...this.form,
                        is_percentage: this.form.amount_type === 'percentage' ? 1 : 0,
                        target_type: this.form.amount_type === 'fixed' ? this.form.target_type : 'all',
                        applies_to: this.form.amount_type === 'fixed' ? this.form.applies_to : [],
                        deduction_frequency_target_type: this.form.deduction_frequency_target_type === 'all' ? 'all' : this.form.deduction_frequency_target_type,
                        deduction_frequency_applies_to: this.form.deduction_frequency_target_type === 'all' ? [] : this.form.deduction_frequency_applies_to,
                    };

                    const response = await fetch(`/hmo-deductions/${this.form.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify(payload),
                    });
                    if (!response.ok) throw new Error('Failed to update HMO deduction');
                    Alpine.nextTick(() => this.fetchDeductions());
                    this.resetForm();
                    alert('HMO deduction updated successfully!');
                } catch (error) {
                    console.error('Error updating HMO deduction:', error);
                    alert('Error updating HMO deduction.');
                }
            },

            async deleteDeduction(id) {
                if (!confirm('Are you sure you want to delete this HMO deduction?')) return;
                try {
                    const response = await fetch(`/hmo-deductions/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    });
                    if (!response.ok) throw new Error('Failed to delete HMO deduction');
                    Alpine.nextTick(() => this.fetchDeductions());
                    alert('HMO deduction deleted successfully!');
                } catch (error) {
                    console.error('Error deleting HMO deduction:', error);
                    alert('Error deleting HMO deduction.');
                }
            },

            formatTargetType(type) {
                if (type === 'employees') return 'Employees';
                if (type === 'departments') return 'Departments';
                return 'All';
            },

            formatCurrency(amount) {
                return parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            },
            getEmployeeName(employee) {
                if (!employee) {
                    return '';
                }
                if (employee.name) {
                    return employee.name;
                }
                const first = employee.first_name ?? '';
                const last = employee.last_name ?? '';
                return `${first} ${last}`.trim();
            },
            getEmployeeNameById(id) {
                const employee = this.allEmployees.find(e => Number(e.id) === Number(id));
                return this.getEmployeeName(employee);
            },
            getDepartmentNameById(id) {
                const department = this.allDepartments.find(d => Number(d.id) === Number(id));
                return department ? department.name : '';
            }
        }));
    });
</script>
