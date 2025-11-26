<div id="holidayModal" class="fixed inset-0 bg-transparent hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 id="holidayModalTitle" class="text-2xl font-bold text-gray-800">Add New Holiday</h3>
            <button onclick="closeHolidayModal()" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="holidayForm" method="POST" action="{{ route('holidays.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="_method" value="POST" id="holidayMethodField">
            <input type="hidden" name="holiday_id" id="holidayIdField">

            <div class="flex-grow overflow-y-auto pr-2">
                <div class="space-y-4">
                    <div class="mb-4">
                        <label for="holiday_date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" id="holiday_date" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 text-gray-800 bg-gray-50 transition-colors duration-200">
                    </div>

                    <div class="mb-4">
                        <label for="holiday_name" class="block text-sm font-medium text-gray-700 mb-1">Holiday Name</label>
                        <input type="text" name="name" id="holiday_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 text-gray-800 bg-gray-50 transition-colors duration-200" placeholder="Enter holiday name">
                    </div>

                    <div x-data="{ holidayType: 'regular', rateMultiplier: {{ config('payroll.holidays.default_rate_multipliers.regular') ?? 1.00 }} }" x-init="
                        $watch('holidayType', value => {
                            if (value === 'regular') {
                                rateMultiplier = {{ config('payroll.holidays.default_rate_multipliers.regular') ?? 1.00 }};
                            } else if (value === 'special_non_working') {
                                rateMultiplier = {{ config('payroll.holidays.default_rate_multipliers.special_non_working') ?? 1.00 }};
                            }
                        })
                    ">
                        <label for="holiday_type" class="block text-sm font-medium text-gray-700 mb-1">Holiday Type</label>
                        <select name="type" id="holiday_type" x-model="holidayType" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-800 transition-colors duration-200">
                            <option value="regular">Regular Holiday</option>
                            <option value="special_non_working">Special Non-Working Holiday</option>
                        </select>

                        <div class="mt-4">
                            <label for="holiday_rate_multiplier" class="block text-sm font-medium text-gray-700 mb-1">Rate Multiplier</label>
                            <input type="number" name="rate_multiplier" id="holiday_rate_multiplier" x-model.number="rateMultiplier" step="0.01" min="0" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 text-gray-800 bg-gray-50 transition-colors duration-200">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeHolidayModal()" class="px-5 py-2 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-200 transition duration-150 shadow-sm">
                    Cancel
                </button>
                <button type="submit" id="saveHolidayBtn" class="px-5 py-2 text-sm bg-blue-600 text-white rounded-md font-semibold hover:bg-blue-700 transition duration-150 shadow-lg shadow-blue-200">
                    <i class="fas fa-save mr-1"></i> Save Holiday
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const holidayModal = document.getElementById('holidayModal');
        const holidayForm = document.getElementById('holidayForm');
        const holidayModalTitle = document.getElementById('holidayModalTitle');
        const holidayMethodField = document.getElementById('holidayMethodField');
        const holidayIdField = document.getElementById('holidayIdField');
        const holidayDateInput = document.getElementById('holiday_date');
        const holidayNameInput = document.getElementById('holiday_name');
        const holidayTypeSelect = document.getElementById('holiday_type');
        const holidayRateMultiplierInput = document.getElementById('holiday_rate_multiplier');
        const saveHolidayBtn = document.getElementById('saveHolidayBtn');

        window.openAddHolidayModal = function() {
            holidayModalTitle.textContent = 'Add New Holiday';
            holidayForm.action = '{{ route('holidays.store') }}';
            holidayMethodField.value = 'POST';
            holidayIdField.value = '';
            holidayForm.reset();
            saveHolidayBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Holiday';

            // Set default values for new holiday
            holidayTypeSelect.value = 'regular';
            holidayRateMultiplierInput.value = `{{ config('payroll.holidays.default_rate_multipliers.regular') ?? 1.00 }}`;

            // Manually trigger Alpine.js update if needed (though x-model should handle this)
            holidayTypeSelect.dispatchEvent(new Event('change'));

            holidayModal.classList.remove('hidden');
            holidayModal.classList.add('flex');
        };

        window.openEditHolidayModal = async function(holidayId) {
            holidayModalTitle.textContent = 'Edit Holiday';
            holidayForm.action = '/holidays/' + holidayId; // Will be updated via AJAX for PUT
            holidayMethodField.value = 'PUT';
            holidayIdField.value = holidayId;
            saveHolidayBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Update Holiday';

            try {
                const response = await fetch('/holidays/' + holidayId);
                const holiday = await response.json();

                holidayDateInput.value = holiday.date; // Assuming date is in YYYY-MM-DD format
                holidayNameInput.value = holiday.name;
                holidayTypeSelect.value = holiday.type;
                holidayRateMultiplierInput.value = holiday.rate_multiplier;

                // Manually trigger Alpine.js update if needed
                holidayTypeSelect.dispatchEvent(new Event('change'));

                holidayModal.classList.remove('hidden');
                holidayModal.classList.add('flex');
            } catch (error) {
                console.error('Error fetching holiday data:', error);
                alert('Could not load holiday data.');
            }
        };

        window.closeHolidayModal = function() {
            holidayModal.classList.add('hidden');
            holidayModal.classList.remove('flex');
        });
    });
</script>
@endpush
