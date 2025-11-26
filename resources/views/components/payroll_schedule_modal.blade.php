<div id="payrollScheduleModal" class="fixed inset-0 bg-transparent hidden items-center justify-center z-50 p-2" x-data="payrollScheduleData()" x-init="fetchSchedules()">
    <div class="bg-white rounded-xl shadow-2xl p-4 max-w-lg sm:max-w-1xl w-full max-h-screen-70 overflow-y-auto transform transition-all duration-300 scale-100">
        <div class="flex justify-between items-center mb-3 border-b pb-2">
            <h2 class="text-lg font-bold text-gray-800">🗓️ Manage Payroll Schedules</h2>
            <button type="button" @click="closePayrollScheduleModal()" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        <div class="mb-3">
            <h3 class="text-base font-semibold text-gray-700 mb-2">Current Schedules</h3>
            <template x-if="schedules.length === 0">
                <p class="text-gray-600 text-sm">No payroll schedules configured yet.</p>
            </template>
            <template x-for="schedule in schedules" :key="schedule.id">
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mb-2 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <div>
                        <p class="font-medium text-gray-800 text-sm" x-text="formatPayPeriodType(schedule.pay_period_type)"></p>
                        <p class="text-xs text-gray-600">Generation Days: <span x-text="formatGenerationDays(schedule.generation_days)"></span></p>
                    </div>
                    <div class="flex space-x-2 mt-2 sm:mt-0">
                        <button type="button" @click="editSchedule(schedule)" class="text-blue-600 hover:text-blue-800 p-1 rounded-full hover:bg-blue-100 transition duration-150" title="Edit">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button type="button" @click="deleteSchedule(schedule.id)" class="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100 transition duration-150" title="Delete">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div class="border-t pt-3 mt-3">
            <h3 class="text-base font-semibold text-gray-700 mb-2" x-text="isEditMode ? 'Edit Payroll Schedule' : 'Add New Payroll Schedule'"></h3>
            <form @submit.prevent="isEditMode ? updateSchedule() : addSchedule()" class="space-y-3">
                <div>
                    <label for="pay_period_type" class="block text-xs font-medium text-gray-700 mb-1">Pay Period Type</label>
                    <select id="pay_period_type" x-model="form.pay_period_type" required
                        class="w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 text-sm transition-colors duration-200">
                        <option value="" disabled>Select Pay Period Type</option>
                        <option value="semi-monthly">Semi-Monthly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>

                <div x-show="form.pay_period_type === 'semi-monthly'">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Generation Days (Semi-Monthly)</label>
                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-4 sm:space-y-0">
                        <label class="inline-flex items-center text-sm">
                            <input type="checkbox" value="15" x-model="form.generation_days" class="form-checkbox h-4 w-4 text-blue-600 rounded-md">
                            <span class="ml-2 text-gray-700">15th of the month</span>
                        </label>
                        <label class="inline-flex items-center text-sm">
                            <input type="checkbox" value="last_day" x-model="form.generation_days" class="form-checkbox h-4 w-4 text-blue-600 rounded-md">
                            <span class="ml-2 text-gray-700">Last day of the month</span>
                        </label>
                    </div>
                </div>

                <div x-show="form.pay_period_type === 'monthly'">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Generation Day (Monthly)</label>
                    <label class="inline-flex items-center text-sm">
                        <input type="radio" value="last_day" x-model="form.generation_days" name="monthly_generation_day" class="form-radio h-4 w-4 text-blue-600">
                        <span class="ml-2 text-gray-700">Last day of the month</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="closePayrollScheduleModal()" class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                        Cancel
                    </button>
                    <button type="submit" class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
                        <i class="fas fa-save mr-1"></i> <span x-text="isEditMode ? 'Update Schedule' : 'Save Schedule'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openPayrollScheduleModal() {
        document.getElementById('payrollScheduleModal').classList.remove('hidden');
        document.getElementById('payrollScheduleModal').classList.add('flex');
        document.getElementById('payrollScheduleModal')._x_data_payrollScheduleData.fetchSchedules(); // Trigger fetch
    }

    function closePayrollScheduleModal() {
        const modal = document.getElementById('payrollScheduleModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal._x_data_payrollScheduleData.resetForm(); // Reset form when closing
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('payrollScheduleData', () => ({
            schedules: [],
            form: {
                id: null,
                pay_period_type: '',
                generation_days: [],
            },
            isEditMode: false,

            init() {
                // Initialization logic if needed
            },

            resetForm() {
                this.form = {
                    id: null,
                    pay_period_type: '',
                    generation_days: [],
                };
                this.isEditMode = false;
            },

            async fetchSchedules() {
                try {
                    const response = await fetch('/payroll-schedules'); // Adjust API endpoint
                    if (!response.ok) throw new Error('Failed to fetch payroll schedules');
                    this.schedules = await response.json();
                } catch (error) {
                    console.error('Error fetching payroll schedules:', error);
                    alert('Error fetching payroll schedules.');
                }
            },

            async addSchedule() {
                try {
                    // Ensure generation_days is an array for submission
                    let generationDaysToSend = this.form.generation_days;
                    if (this.form.pay_period_type === 'monthly' && typeof this.form.generation_days === 'string') {
                        generationDaysToSend = [this.form.generation_days];
                    } else if (!Array.isArray(this.form.generation_days)) {
                        generationDaysToSend = [];
                    }

                    const response = await fetch('/payroll-schedules', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ ...this.form, generation_days: generationDaysToSend }),
                    });
                    if (!response.ok) throw new Error('Failed to add payroll schedule');
                    Alpine.nextTick(() => this.fetchSchedules());
                    this.resetForm();
                    alert('Payroll schedule added successfully!');
                } catch (error) {
                    console.error('Error adding payroll schedule:', error);
                    alert('Error adding payroll schedule.');
                }
            },

            editSchedule(schedule) {
                this.form.id = schedule.id;
                this.form.pay_period_type = schedule.pay_period_type;
                // Ensure generation_days is an array for semi-monthly, or a single value for monthly radio
                if (schedule.pay_period_type === 'semi-monthly') {
                    this.form.generation_days = Array.isArray(schedule.generation_days) ? schedule.generation_days.map(String) : [];
                } else if (schedule.pay_period_type === 'monthly') {
                    this.form.generation_days = schedule.generation_days.length > 0 ? [String(schedule.generation_days[0])] : [];
                }
                this.isEditMode = true;
                openPayrollScheduleModal(); // Re-open modal for editing
            },

            async updateSchedule() {
                try {
                    // Ensure generation_days is an array for submission
                    let generationDaysToSend = this.form.generation_days;
                    if (this.form.pay_period_type === 'monthly' && typeof this.form.generation_days === 'string') {
                        generationDaysToSend = [this.form.generation_days];
                    } else if (!Array.isArray(this.form.generation_days)) {
                        generationDaysToSend = [];
                    }

                    const response = await fetch(`/payroll-schedules/${this.form.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ ...this.form, generation_days: generationDaysToSend }),
                    });
                    if (!response.ok) throw new Error('Failed to update payroll schedule');
                    Alpine.nextTick(() => this.fetchSchedules());
                    this.resetForm();
                    alert('Payroll schedule updated successfully!');
                } catch (error) {
                    console.error('Error updating payroll schedule:', error);
                    alert('Error updating payroll schedule.');
                }
            },

            async deleteSchedule(id) {
                if (!confirm('Are you sure you want to delete this payroll schedule?')) return;
                try {
                    const response = await fetch(`/payroll-schedules/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    });
                    if (!response.ok) throw new Error('Failed to delete payroll schedule');
                    Alpine.nextTick(() => this.fetchSchedules());
                    alert('Payroll schedule deleted successfully!');
                } catch (error) {
                    console.error('Error deleting payroll schedule:', error);
                    alert('Error deleting payroll schedule.');
                }
            },

            formatPayPeriodType(type) {
                return type.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join('-');
            },

            formatGenerationDays(days) {
                if (!Array.isArray(days) || days.length === 0) return 'None';
                return days.map(day => {
                    if (day === 'last_day') return 'Last Day';
                    return `${day}th`;
                }).join(', ');
            },
        }));
    });
</script>
