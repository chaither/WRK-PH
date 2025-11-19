<div id="governmentContributionModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50 p-4" x-data="governmentContributionData()" x-init="fetchContributions()">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-2xl w-full transform transition-all duration-300 scale-100">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-xl font-bold text-gray-800">📊 Manage Government Contributions</h2>
            <button type="button" @click="closeGovernmentContributionModal()" class="text-gray-500 hover:text-gray-900 transition duration-150 p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Current Contributions</h3>
            <template x-if="contributions.length === 0">
                <p class="text-gray-600">No government contributions configured yet.</p>
            </template>
            <template x-for="contribution in contributions" :key="contribution.id">
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mb-2 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800" x-text="formatContributionType(contribution.type)"></p>
                        <p class="text-sm text-gray-600">
                            Salary Range: <span x-text="formatSalaryRange(contribution.min_salary, contribution.max_salary)"></span>
                        </p>
                        <p class="text-sm text-gray-600">
                            Employee Share: <span x-text="formatCurrency(contribution.employee_share)"></span>
                            <template x-if="contribution.employer_share !== null">
                                <span> | Employer Share: <span x-text="formatCurrency(contribution.employer_share)"></span></span>
                            </template>
                        </p>
                        <template x-if="contribution.is_percentage">
                            <span class="text-xs text-blue-500">(Percentage)</span>
                        </template>
                        <template x-if="contribution.target_type !== 'all' && contribution.applies_to && contribution.applies_to.length > 0">
                            <span class="text-xs text-purple-500" x-text="`(${formatTargetType(contribution.target_type)}: ${contribution.applies_to.join(', ')})`"></span>
                        </template>
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" @click="editContribution(contribution)" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-full hover:bg-blue-100 transition duration-150" title="Edit">
                            <i class="fas fa-edit text-base"></i>
                        </button>
                        <button type="button" @click="deleteContribution(contribution.id)" class="text-red-600 hover:text-red-800 p-1.5 rounded-full hover:bg-red-100 transition duration-150" title="Delete">
                            <i class="fas fa-trash-alt text-base"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div class="border-t pt-4 mt-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-2" x-text="isEditMode ? 'Edit Contribution' : 'Add New Contribution'"></h3>
            <form @submit.prevent="isEditMode ? updateContribution() : addContribution()" class="space-y-4">
                <div>
                    <label for="contribution_type" class="block text-sm font-medium text-gray-700 mb-1">Contribution Type</label>
                    <select id="contribution_type" x-model="form.type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 transition-colors duration-200">
                        <option value="" disabled>Select Type</option>
                        <option value="sss">SSS</option>
                        <option value="philhealth">PhilHealth</option>
                        <option value="pagibig">Pag-IBIG</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="min_salary" class="block text-sm font-medium text-gray-700 mb-1">Min. Salary (Optional)</label>
                        <input type="number" min="0" step="0.01" id="min_salary" x-model="form.min_salary"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 transition-colors duration-200">
                    </div>
                    <div>
                        <label for="max_salary" class="block text-sm font-medium text-gray-700 mb-1">Max. Salary (Optional)</label>
                        <input type="number" min="0" step="0.01" id="max_salary" x-model="form.max_salary"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 transition-colors duration-200">
                    </div>
                </div>

                <div class="mt-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" x-model="form.is_percentage" @change="form.target_type = 'all'; form.applies_to = []" class="form-checkbox h-5 w-5 text-blue-600 rounded-md">
                        <span class="ml-2 text-gray-700">Is Percentage-based?</span>
                    </label>
                </div>

                <template x-if="!form.is_percentage">
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apply To:</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" value="all" x-model="form.target_type" class="form-radio h-5 w-5 text-blue-600">
                                <span class="ml-2 text-gray-700">All Employees</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" value="employees" x-model="form.target_type" class="form-radio h-5 w-5 text-blue-600">
                                <span class="ml-2 text-gray-700">Specific Employees</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" value="departments" x-model="form.target_type" class="form-radio h-5 w-5 text-blue-600">
                                <span class="ml-2 text-gray-700">Specific Departments</span>
                            </label>
                        </div>
                    </div>

                    <template x-if="form.target_type === 'employees'">
                        <div class="mt-4">
                            <label for="select_employees" class="block text-sm font-medium text-gray-700 mb-1">Select Employees:</label>
                            <select id="select_employees" x-model="form.applies_to" multiple
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 transition-colors duration-200">
                                <template x-if="allEmployees.length === 0">
                                    <option disabled>No employees found</option>
                                </template>
                                <template x-if="allEmployees.length > 0">
                                    <template x-for="employee in allEmployees" :key="employee.id">
                                        <option :value="employee.id" x-text="employee.name"></option>
                                    </template>
                                </template>
                            </select>
                        </div>
                    </template>

                    <template x-if="form.target_type === 'departments'">
                        <div class="mt-4">
                            <label for="select_departments" class="block text-sm font-medium text-gray-700 mb-1">Select Departments:</label>
                            <select id="select_departments" x-model="form.applies_to" multiple
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 transition-colors duration-200">
                                <template x-if="allDepartments.length === 0">
                                    <option disabled>No departments found</option>
                                </template>
                                <template x-if="allDepartments.length > 0">
                                    <template x-for="department in allDepartments" :key="department.id">
                                        <option :value="department.id" x-text="department.name"></option>
                                    </template>
                                </template>
                            </select>
                        </div>
                    </template>
                </template>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="employee_share" class="block text-sm font-medium text-gray-700 mb-1" x-text="form.is_percentage ? 'Employee Share (%)' : 'Employee Share (₱)'"></label>
                        <input type="number" min="0" step="0.01" id="employee_share" x-model="form.employee_share" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 transition-colors duration-200">
                    </div>
                    <div>
                        <label for="employer_share" class="block text-sm font-medium text-gray-700 mb-1" x-text="form.is_percentage ? 'Employer Share (%) (Optional)' : 'Employer Share (₱) (Optional)'"></label>
                        <input type="number" min="0" step="0.01" id="employer_share" x-model="form.employer_share"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 transition-colors duration-200">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="closeGovernmentContributionModal()" class="px-4 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-1.5 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
                        <i class="fas fa-save mr-1"></i> <span x-text="isEditMode ? 'Update Contribution' : 'Save Contribution'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openGovernmentContributionModal() {
        document.getElementById('governmentContributionModal').classList.remove('hidden');
        document.getElementById('governmentContributionModal').classList.add('flex');
    }

    function closeGovernmentContributionModal() {
        const modal = document.getElementById('governmentContributionModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal._x_data_governmentContributionData.resetForm();
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('governmentContributionData', () => ({
            contributions: [],
            allEmployees: [],
            allDepartments: [],
            form: {
                id: null,
                type: '',
                min_salary: '',
                max_salary: '',
                is_percentage: false,
                employee_share: '',
                employer_share: '',
                target_type: 'all',
                applies_to: [],
            },
            isEditMode: false,

            init() {
                this.fetchEmployees();
                this.fetchDepartments();
                this.fetchContributions();
            },

            resetForm() {
                this.form = {
                    id: null,
                    type: '',
                    min_salary: '',
                    max_salary: '',
                    is_percentage: false,
                    employee_share: '',
                    employer_share: '',
                    target_type: 'all',
                    applies_to: [],
                };
                this.isEditMode = false;
            },

            async fetchContributions() {
                try {
                    const response = await fetch('/government-contributions'); // API endpoint
                    if (!response.ok) throw new Error('Failed to fetch contributions');
                    this.contributions = await response.json();
                    console.log('Fetched contributions:', this.contributions);
                } catch (error) {
                    console.error('Error fetching government contributions:', error);
                    alert('Error fetching government contributions.');
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

            async addContribution() {
                try {
                    const response = await fetch('/government-contributions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify(this.form),
                    });
                    if (!response.ok) throw new Error('Failed to add contribution');
                    Alpine.nextTick(() => this.fetchContributions());
                    this.resetForm();
                    alert('Contribution added successfully!');
                } catch (error) {
                    console.error('Error adding contribution:', error);
                    alert('Error adding contribution.');
                }
            },

            editContribution(contribution) {
                this.form.id = contribution.id;
                this.form.type = contribution.type;
                this.form.min_salary = contribution.min_salary;
                this.form.max_salary = contribution.max_salary;
                this.form.is_percentage = contribution.is_percentage === 1; // Convert to boolean
                this.form.target_type = contribution.target_type || 'all';
                this.form.applies_to = contribution.applies_to || [];
                this.form.employee_share = contribution.employee_share;
                this.form.employer_share = contribution.employer_share;
                this.isEditMode = true;
                console.log('Editing contribution. Form data:', this.form);
                openGovernmentContributionModal();
            },

            async updateContribution() {
                try {
                    const response = await fetch(`/government-contributions/${this.form.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify(this.form),
                    });
                    if (!response.ok) throw new Error('Failed to update contribution');
                    Alpine.nextTick(() => this.fetchContributions());
                    this.resetForm();
                    alert('Contribution updated successfully!');
                } catch (error) {
                    console.error('Error updating contribution:', error);
                    alert('Error updating contribution.');
                }
            },

            async deleteContribution(id) {
                if (!confirm('Are you sure you want to delete this contribution?')) return;
                try {
                    const response = await fetch(`/government-contributions/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    });
                    if (!response.ok) throw new Error('Failed to delete contribution');
                    Alpine.nextTick(() => this.fetchContributions());
                    alert('Contribution deleted successfully!');
                } catch (error) {
                    console.error('Error deleting contribution:', error);
                    alert('Error deleting contribution.');
                }
            },

            formatTargetType(type) {
                if (type === 'employees') return 'Employees';
                if (type === 'departments') return 'Departments';
                return 'All';
            },

            formatContributionType(type) {
                return type.toUpperCase();
            },

            formatSalaryRange(min, max) {
                if (min !== null && max !== null) {
                    return `₱${this.formatCurrency(min)} - ₱${this.formatCurrency(max)}`;
                } else if (min !== null) {
                    return `₱${this.formatCurrency(min)} and above`;
                } else if (max !== null) {
                    return `Up to ₱${this.formatCurrency(max)}`;
                } else {
                    return 'All Salaries';
                }
            },

            formatCurrency(amount) {
                return parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            },
        }));
    });
</script>
