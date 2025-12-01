<!-- resources/views/components/overtime_multiplier_modal.blade.php -->
<div id="overtimeMultiplierModal" class="fixed inset-0 bg-transparent hidden items-center justify-center z-50 p-2">
    <div class="bg-white rounded-xl shadow-2xl p-4 max-w-sm w-full max-h-screen-70 overflow-y-auto transform transition-all duration-300 scale-100">
        <div class="flex justify-between items-center mb-3 border-b pb-2">
            <h2 class="text-lg font-bold text-gray-800">⚙️ Set Overtime Multiplier</h2>
            <button onclick="closeOvertimeMultiplierModal()" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm font-medium text-blue-800">Current Global Overtime Multiplier: <span id="currentOvertimeMultiplierDisplay" class="font-bold"></span></p>
        </div>
        <form id="overtimeMultiplierForm" method="POST" action="{{ route('admin.payroll.update-global-overtime-multiplier') }}" class="space-y-3">
            @csrf
            @method('PUT')
            
            <div>
                <label for="overtime_multiplier_value" class="block text-xs font-medium text-gray-700 mb-1">Set New Global Overtime Multiplier</label>
                <input type="number" min="1.0" step="0.1" name="overtime_multiplier" id="overtime_multiplier_value" class="w-full px-2 py-1.5 border border-gray-300 rounded-md text-sm shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeOvertimeMultiplierModal()" class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                    Cancel
                </button>
                <button type="submit" class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openOvertimeMultiplierModal(currentMultiplier) {
        document.getElementById('currentOvertimeMultiplierDisplay').innerText = currentMultiplier;
        document.getElementById('overtime_multiplier_value').value = currentMultiplier || 1.5; // Default to 1.5 if not set

        const form = document.getElementById('overtimeMultiplierForm');
        form.action = "{{ route('admin.payroll.update-global-overtime-multiplier') }}";

        document.getElementById('overtimeMultiplierModal').classList.remove('hidden');
        document.getElementById('overtimeMultiplierModal').classList.add('flex');
    }

    function closeOvertimeMultiplierModal() {
        document.getElementById('overtimeMultiplierModal').classList.add('hidden');
        document.getElementById('overtimeMultiplierModal').classList.remove('flex');
    }
</script>
@endpush
