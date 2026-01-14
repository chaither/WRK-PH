<div id="shiftModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 p-4 transition-opacity duration-300" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div id="shiftModalContent" class="bg-white rounded-3xl shadow-2xl p-6 max-w-2xl w-full opacity-0 scale-95 transform transition-all duration-300 ease-out">
        <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-3">
            <h2 id="shiftModalTitle" class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-clock mr-2 text-indigo-600"></i> <span id="shiftModalTitleText">Add Shift</span>
            </h2>
            <button onclick="closeShiftModal()" class="text-gray-500 hover:text-gray-800 transition duration-150 p-2 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <form id="shiftForm" action="{{ route('shifts.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="shiftFormMethod" value="POST">
            <input type="hidden" name="shift_id" id="shift_id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Shift Name -->
                <div class="md:col-span-2">
                    <label for="shift_name" class="block text-sm font-medium text-gray-700 mb-1">Shift Name *</label>
                    <input type="text" name="name" id="shift_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-black">
                </div>
                
                <!-- Start Time -->
                <div>
                    <label for="shift_start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                    <input type="time" name="start_time" id="shift_start_time" required step="60"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-black">
                </div>
                
                <!-- End Time -->
                <div>
                    <label for="shift_end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time *</label>
                    <input type="time" name="end_time" id="shift_end_time" required step="60"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-black">
                </div>
                
                <!-- Is Night Shift Checkbox -->
                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_night_shift" id="is_night_shift" value="1" onchange="toggleNightShiftMultiplier()"
                               class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">This is a Night Shift</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-7">Check this if the shift qualifies for night differential pay</p>
                </div>
                
                <!-- Night Shift Multiplier -->
                <div id="nightShiftMultiplierDiv" class="md:col-span-2 hidden">
                    <label for="night_shift_multiplier" class="block text-sm font-medium text-gray-700 mb-1">
                        Night Shift Pay Multiplier *
                        <span class="text-xs text-gray-500">(e.g., 1.10 for 10% increase)</span>
                    </label>
                    <input type="number" name="night_shift_multiplier" id="night_shift_multiplier" 
                           min="1.00" max="3.00" step="0.01" value="1.10"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-black">
                    <p class="text-xs text-gray-500 mt-1">Common values: 1.10 (10% increase), 1.15 (15% increase), 1.20 (20% increase)</p>
                </div>
                
                <!-- Lunch Break Section -->
                <div class="md:col-span-2 border-t pt-4 mt-2">
                    <h3 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-utensils mr-2 text-orange-500"></i> Lunch Break Configuration
                    </h3>
                    <p class="text-xs text-gray-500 mb-3">Configure lunch break times for this shift. This helps track Time In/Out 1 and Time In/Out 2 on biometric devices.</p>
                </div>
                
                <!-- Lunch Break Start Time -->
                <div>
                    <label for="lunch_break_start" class="block text-sm font-medium text-gray-700 mb-1">Lunch Break Start</label>
                    <input type="time" name="lunch_break_start" id="lunch_break_start" step="60"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-black">
                    <p class="text-xs text-gray-500 mt-1">When employees clock out for lunch</p>
                </div>
                
                <!-- Lunch Break End Time -->
                <div>
                    <label for="lunch_break_end" class="block text-sm font-medium text-gray-700 mb-1">Lunch Break End</label>
                    <input type="time" name="lunch_break_end" id="lunch_break_end" step="60"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-black">
                    <p class="text-xs text-gray-500 mt-1">When employees clock in after lunch</p>
                </div>
                
                <!-- Lunch Break Duration (Auto-calculated) -->
                <div>
                    <label for="lunch_break_duration" class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                    <input type="number" name="lunch_break_duration" id="lunch_break_duration" 
                           min="0" max="180" step="15" value="60" readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-black">
                    <p class="text-xs text-gray-500 mt-1">Auto-calculated from start and end times</p>
                </div>
                
                <!-- Is Lunch Paid -->
                <div>
                    <label class="flex items-center mt-6">
                        <input type="checkbox" name="is_lunch_paid" id="is_lunch_paid" value="1"
                               class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Lunch Break is Paid</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-7">Check if employees are paid during lunch break</p>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeShiftModal()" 
                        class="px-5 py-2.5 text-sm border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition duration-150">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 text-sm bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
                    <i class="fas fa-save mr-2"></i> Save Shift
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openShiftModal(shift = null) {
    const modal = document.getElementById('shiftModal');
    const modalContent = document.getElementById('shiftModalContent');
    const form = document.getElementById('shiftForm');
    const titleText = document.getElementById('shiftModalTitleText');
    const methodInput = document.getElementById('shiftFormMethod');
    
    // Reset form
    form.reset();
    
    if (shift) {
        // Edit mode
        titleText.textContent = 'Edit Shift';
        form.action = `/shifts/${shift.id}`;
        methodInput.value = 'PUT';
        document.getElementById('shift_id').value = shift.id;
        document.getElementById('shift_name').value = shift.name;
        document.getElementById('shift_start_time').value = shift.start_time.substring(0, 5);
        document.getElementById('shift_end_time').value = shift.end_time.substring(0, 5);
        document.getElementById('is_night_shift').checked = shift.is_night_shift == 1;
        document.getElementById('night_shift_multiplier').value = shift.night_shift_multiplier || 1.10;
        
        // Lunch break fields
        if (shift.lunch_break_start) {
            document.getElementById('lunch_break_start').value = shift.lunch_break_start.substring(0, 5);
        }
        if (shift.lunch_break_end) {
            document.getElementById('lunch_break_end').value = shift.lunch_break_end.substring(0, 5);
        }
        document.getElementById('lunch_break_duration').value = shift.lunch_break_duration || 60;
        document.getElementById('is_lunch_paid').checked = shift.is_lunch_paid == 1;
        
        toggleNightShiftMultiplier();
        calculateLunchDuration();
    } else {
        // Add mode
        titleText.textContent = 'Add Shift';
        form.action = '{{ route("shifts.store") }}';
        methodInput.value = 'POST';
        document.getElementById('is_night_shift').checked = false;
        toggleNightShiftMultiplier();
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modal.classList.add('opacity-100');
        modalContent.classList.remove('opacity-0', 'scale-95');
        modalContent.classList.add('opacity-100', 'scale-100');
    }, 10);
}

function closeShiftModal() {
    const modal = document.getElementById('shiftModal');
    const modalContent = document.getElementById('shiftModalContent');
    
    modal.classList.remove('opacity-100');
    modalContent.classList.remove('opacity-100', 'scale-100');
    modalContent.classList.add('opacity-0', 'scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

function toggleNightShiftMultiplier() {
    const isNightShift = document.getElementById('is_night_shift').checked;
    const multiplierDiv = document.getElementById('nightShiftMultiplierDiv');
    const multiplierInput = document.getElementById('night_shift_multiplier');
    
    if (isNightShift) {
        multiplierDiv.classList.remove('hidden');
        multiplierInput.required = true;
    } else {
        multiplierDiv.classList.add('hidden');
        multiplierInput.required = false;
        multiplierInput.value = 1.00;
    }
}

function calculateLunchDuration() {
    const startInput = document.getElementById('lunch_break_start');
    const endInput = document.getElementById('lunch_break_end');
    const durationInput = document.getElementById('lunch_break_duration');
    
    if (startInput.value && endInput.value) {
        const start = new Date(`2000-01-01T${startInput.value}:00`);
        const end = new Date(`2000-01-01T${endInput.value}:00`);
        
        const diffMs = end - start;
        const diffMins = Math.round(diffMs / 60000);
        
        if (diffMins > 0) {
            durationInput.value = diffMins;
        } else {
            durationInput.value = 0;
        }
    }
}

// Add event listeners for lunch break time inputs
document.addEventListener('DOMContentLoaded', function() {
    const lunchStart = document.getElementById('lunch_break_start');
    const lunchEnd = document.getElementById('lunch_break_end');
    
    if (lunchStart) {
        lunchStart.addEventListener('change', calculateLunchDuration);
    }
    if (lunchEnd) {
        lunchEnd.addEventListener('change', calculateLunchDuration);
    }
});

// Close modal when clicking outside
document.getElementById('shiftModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeShiftModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeShiftModal();
    }
});
</script>
