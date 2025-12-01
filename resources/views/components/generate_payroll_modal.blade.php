<div id="generatePayrollModal" class="fixed inset-0 bg-transparent bg-opacity-25 hidden items-center justify-center z-50 p-4 transition-opacity duration-300">
    <div id="generatePayrollModalContent" class="bg-white bg-opacity-50 rounded-xl shadow-2xl p-6 max-w-md w-full opacity-0 scale-95 transform transition-all duration-300 ease-out">
        <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-3">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-calculator mr-2 text-indigo-600"></i> Generate Payroll
            </h2>
            <button onclick="closeGeneratePayrollModal()" class="text-gray-500 hover:text-gray-800 transition duration-150 p-2 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form id="generatePayrollForm" method="POST" action="{{ route('payroll.generate.range') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="start_date" id="modal_start_date">
            <input type="hidden" name="end_date" id="modal_end_date">
            <input type="hidden" name="force_regenerate" id="modal_force_regenerate" value="false">

            <div class="space-y-2">
                <label for="department_ids" class="block text-sm font-medium text-gray-700">Select Department(s)</label>
                <div class="relative">
                    <button type="button" id="multiSelectDropdownButton" class="w-full px-3 py-2.5 text-left border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-gray-800 bg-white cursor-pointer flex items-center justify-between">
                        <span id="selectedDepartmentsText">All Departments</span>
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </button>
                    <div id="multiSelectDropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <div class="p-2 border-b border-gray-200">
                            <label class="flex items-center space-x-2 text-sm text-gray-700">
                                <input type="checkbox" id="selectAllDepartments" class="form-checkbox h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500">
                                <span>Select All</span>
                            </label>
                        </div>
                        <div id="departmentCheckboxes" class="p-2 space-y-2">
                            @foreach($departments as $department)
                                <label class="flex items-center space-x-2 text-sm text-gray-700">
                                    <input type="checkbox" name="department_ids[]" value="{{ $department->id }}" class="form-checkbox h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500 department-checkbox">
                                    <span>{{ $department->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeGeneratePayrollModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                    Cancel
                </button>
                <button type="submit" id="generatePayrollSubmitBtn" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition duration-150 shadow-md">
                    <i class="fas fa-calculator mr-1"></i> Generate Payroll
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropdownButton = document.getElementById('multiSelectDropdownButton');
        const dropdown = document.getElementById('multiSelectDropdown');
        const selectAllCheckbox = document.getElementById('selectAllDepartments');
        const departmentCheckboxes = document.querySelectorAll('.department-checkbox');
        const selectedDepartmentsText = document.getElementById('selectedDepartmentsText');

        function updateSelectedDepartmentsText() {
            const checkedCheckboxes = Array.from(departmentCheckboxes).filter(cb => cb.checked);
            if (checkedCheckboxes.length === 0) {
                selectedDepartmentsText.textContent = 'No Department Selected';
            } else if (checkedCheckboxes.length === departmentCheckboxes.length) {
                selectedDepartmentsText.textContent = 'All Departments';
            } else {
                selectedDepartmentsText.textContent = checkedCheckboxes.map(cb => cb.nextElementSibling.textContent).join(', ');
            }
        }

        function toggleAllCheckboxes(checked) {
            departmentCheckboxes.forEach(cb => {
                cb.checked = checked;
            });
            updateSelectedDepartmentsText();
        }

        dropdownButton.addEventListener('click', function () {
            dropdown.classList.toggle('hidden');
        });

        selectAllCheckbox.addEventListener('change', function () {
            toggleAllCheckboxes(this.checked);
        });

        departmentCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (!this.checked) {
                    selectAllCheckbox.checked = false;
                } else {
                    const allChecked = Array.from(departmentCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                }
                updateSelectedDepartmentsText();
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            if (!dropdown.contains(event.target) && !dropdownButton.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        updateSelectedDepartmentsText(); // Initial text update
    });

    function openGeneratePayrollModal(startDate, endDate, isRegenerate = false) {
        document.getElementById('modal_start_date').value = startDate;
        document.getElementById('modal_end_date').value = endDate;
        document.getElementById('modal_force_regenerate').value = isRegenerate;

        const modalTitle = document.querySelector('#generatePayrollModal h2');
        const submitBtn = document.getElementById('generatePayrollSubmitBtn');

        if (isRegenerate) {
            modalTitle.innerHTML = '<i class="fas fa-redo mr-2 text-orange-600"></i> Regenerate Payroll';
            submitBtn.innerHTML = '<i class="fas fa-redo mr-1"></i> Regenerate Payroll';
            submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            submitBtn.classList.add('bg-orange-600', 'hover:bg-orange-700');
        } else {
            modalTitle.innerHTML = '<i class="fas fa-calculator mr-2 text-indigo-600"></i> Generate Payroll';
            submitBtn.innerHTML = '<i class="fas fa-calculator mr-1"></i> Generate Payroll';
            submitBtn.classList.remove('bg-orange-600', 'hover:bg-orange-700');
            submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }

        const modal = document.getElementById('generatePayrollModal');
        const modalContent = document.getElementById('generatePayrollModalContent');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Animate modal in
        setTimeout(() => {
            modal.classList.add('opacity-100');
            modalContent.classList.remove('opacity-0', 'scale-95');
            modalContent.classList.add('opacity-100', 'scale-100');
        }, 10);

        // Reset checkboxes on open - initially select all departments
        const selectAllCheckbox = document.getElementById('selectAllDepartments');
        const departmentCheckboxes = document.querySelectorAll('.department-checkbox');
        selectAllCheckbox.checked = true;
        departmentCheckboxes.forEach(cb => {
            cb.checked = true;
        });
        document.getElementById('selectedDepartmentsText').textContent = 'All Departments';
    }

    function closeGeneratePayrollModal() {
        const modal = document.getElementById('generatePayrollModal');
        const modalContent = document.getElementById('generatePayrollModalContent');

        // Animate modal out
        modal.classList.remove('opacity-100');
        modalContent.classList.remove('opacity-100', 'scale-100');
        modalContent.classList.add('opacity-0', 'scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Reset modal title and button to default generate state when closing
            const modalTitle = document.querySelector('#generatePayrollModal h2');
            const submitBtn = document.getElementById('generatePayrollSubmitBtn');
            modalTitle.innerHTML = '<i class="fas fa-calculator mr-2 text-indigo-600"></i> Generate Payroll';
            submitBtn.innerHTML = '<i class="fas fa-calculator mr-1"></i> Generate Payroll';
            submitBtn.classList.remove('bg-orange-600', 'hover:bg-orange-700');
            submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');

            // Hide the dropdown in case it was open
            document.getElementById('multiSelectDropdown').classList.add('hidden');
        }, 300); // Duration matches transition-all duration-300
    }
</script>
