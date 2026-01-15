<div id="departmentModal"  class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 id="departmentModalTitle" class="text-2xl font-bold text-gray-800">Create New Department</h3>
            <button onclick="closeDepartmentModal()" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="departmentForm" method="POST" action="{{ route('department.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="_method" value="POST" id="departmentMethodField">
            <div class="flex-grow overflow-y-auto pr-2">
                <div class="space-y-4">
                    <div>
                        <label for="department_name" class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
                        <input type="text" name="name" id="department_name" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white text-black" placeholder="Enter department name">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeDepartmentModal()" class="px-6 py-2 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                    Cancel
                </button>
                <button type="submit" id="saveDepartmentBtn" class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-lg shadow-indigo-200">
                    <i class="fas fa-save mr-1"></i> Save Department
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const departmentForm = document.getElementById('departmentForm');
        const departmentModalTitle = document.getElementById('departmentModalTitle');
        const departmentMethodField = document.getElementById('departmentMethodField');
        const departmentNameInput = document.getElementById('department_name');
        const saveDepartmentBtn = document.getElementById('saveDepartmentBtn');

        function syncDepartmentDropdowns(department) {
            if (!department) {
                return;
            }

            document.querySelectorAll('select[name="department_id"]').forEach(select => {
                let option = select.querySelector(`option[value="${department.id}"]`);
                if (!option) {
                    option = document.createElement('option');
                    option.value = department.id;
                    select.appendChild(option);
                }
                option.textContent = department.name;
            });
        }

        // New code for handling AJAX submission
        departmentForm.addEventListener('submit', async function(event) {
            event.preventDefault(); // Prevent default form submission

            const formData = new FormData(this);
            const url = this.action;
            const methodToUse = document.getElementById('departmentMethodField').value; // Get the actual method from the hidden field

            try {
                const response = await fetch(url, {
                    method: 'POST', // Always use POST for FormData to allow Laravel to interpret _method field correctly
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!response.ok) {
                    if (response.status === 422) {
                        const errorData = await response.json();
                        let errorMessage = 'Validation Error:\n';
                        if (errorData.errors) {
                            for (const [key, messages] of Object.entries(errorData.errors)) {
                                errorMessage += `${messages.join('\n')}\n`;
                            }
                        } else {
                            errorMessage += errorData.message || 'Unknown validation error.';
                        }
                        alert(errorMessage);
                        return; // Stop further execution
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {

                    syncDepartmentDropdowns(data.department);

                    // Assuming `addDepartmentToTable` and `updateDepartmentInTable` functions exist globally or are passed somehow
                    if (methodToUse === 'POST') {
                        // This function will be defined in department.index.blade.php
                        window.addDepartmentToTable(data.department);
                    } else {
                        // This function will be defined in department.index.blade.php
                        window.updateDepartmentInTable(data.department);
                    }
                    closeDepartmentModal();
                    // Optionally display a success message to the user
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error submitting department form:', error);
                // If we already alerted the validation error, don't alert again
                if (!error.message.includes('HTTP error! status: 422')) {
                     alert('An error occurred while saving the department.\n' + error.message);
                }
            }
        });

        window.openDepartmentModal = function(department = null) {
            document.getElementById('departmentModal').classList.remove('hidden');
            document.getElementById('departmentModal').classList.add('flex');
            
            if (department) {
                departmentModalTitle.textContent = 'Edit Department';
                departmentForm.action = '/department/' + department.id;
                departmentMethodField.value = 'PUT';
                saveDepartmentBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Update Department';
                departmentNameInput.value = department.name;
            } else {
                departmentModalTitle.textContent = 'Create New Department';
                departmentForm.action = "{{ route('department.store') }}";
                departmentMethodField.value = 'POST';
                saveDepartmentBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Department';
                departmentForm.reset();
            }
        };

        window.closeDepartmentModal = function() {
            document.getElementById('departmentModal').classList.add('hidden');
            document.getElementById('departmentModal').classList.remove('flex');
        };
    });
</script>
@endpush
