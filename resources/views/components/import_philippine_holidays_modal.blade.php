<div id="importPhilippineHolidaysModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 transform transition-all duration-300 scale-100">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 class="text-2xl font-bold text-gray-800">Import Philippine Holidays</h3>
            <button onclick="closeImportPhilippineHolidaysModal()" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form action="{{ route('holidays.import-philippines') }}" method="POST" class="space-y-4">
            @csrf
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-4">
                    This will automatically import all Philippine holidays (regular and special non-working) for the selected year. 
                    Existing holidays will be skipped.
                </p>
                <label for="import_year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <input type="number" name="year" id="import_year" min="2000" max="2100" 
                    value="{{ date('Y') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 text-gray-800 bg-gray-50 transition-colors duration-200">
                <p class="text-xs text-gray-500 mt-1">Select the year to import holidays for (2000-2100)</p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
                <p class="text-xs text-blue-800 font-semibold mb-1">What will be imported:</p>
                <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
                    <li><strong>Regular Holidays</strong> (2.00x rate): New Year, Maundy Thursday, Good Friday, Araw ng Kagitingan, Labor Day, Independence Day, National Heroes Day, Bonifacio Day, Rizal Day, Christmas</li>
                    <li><strong>Special Non-Working Holidays</strong> (1.30x rate): Chinese New Year, EDSA Revolution, Black Saturday, Ninoy Aquino Day, All Saints' Day, All Souls' Day, Last Day of the Year</li>
                </ul>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeImportPhilippineHolidaysModal()" 
                    class="px-5 py-2 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-200 transition duration-150 shadow-sm">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-5 py-2 text-sm bg-green-600 text-white rounded-md font-semibold hover:bg-green-700 transition duration-150 shadow-lg shadow-green-200">
                    <i class="fas fa-download mr-1"></i> Import Holidays
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openImportPhilippineHolidaysModal() {
        const modal = document.getElementById('importPhilippineHolidaysModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Set current year as default
        document.getElementById('import_year').value = new Date().getFullYear();
    }

    function closeImportPhilippineHolidaysModal() {
        const modal = document.getElementById('importPhilippineHolidaysModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
