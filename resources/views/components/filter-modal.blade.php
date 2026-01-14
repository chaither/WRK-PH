<div id="filterModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 p-4">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/4 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Filter {{ $title }}</h3>
            <button type="button" class="text-red-500 hover:text-red-700 text-xl" onclick="document.getElementById('filterModal').classList.add('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="filterForm" action="{{ $route }}" method="GET" class="space-y-4">
            {{-- @csrf --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="modal_start_date" class="block text-sm font-bold text-gray-900">Start Date</label>
                    <input type="date" name="start_date" id="modal_start_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md text-gray-900" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}">
                </div>
                <div>
                    <label for="modal_end_date" class="block text-sm font-bold text-gray-900">End Date</label>
                    <input type="date" name="end_date" id="modal_end_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md text-gray-900" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}">
                </div>
            </div>
            <div>
                <label for="modal_specific_date" class="block text-sm font-bold text-gray-900">Specific Date</label>
                <input type="date" name="specific_date" id="modal_specific_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md text-gray-900" value="{{ request('specific_date') }}">
            </div>

            <div class="flex justify-end space-x-2">
                @if($isFiltered)
                    <a href="{{ $route }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Clear Filter
                    </a>
                @endif
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Apply Filter
                </button>
            </div>
        </form>
    </div>
</div>
