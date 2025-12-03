<div id="addNodeModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 class="text-2xl font-bold text-gray-800">Add New Organizational Node</h3>
            <button type="button" id="closeAddNodeModal" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form action="{{ route('organization.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 flex-grow overflow-y-auto pr-2">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter name">
                </div>
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <input type="text" name="position" id="position" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter position">
                </div>
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Reports To (Parent Node)</label>
                    <select name="parent_id" id="parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">None</option>
                        @foreach($nodes as $node)
                            <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->position }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <input type="file" name="image" id="image" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                <button type="button" id="cancelAddNodeModal" class="px-6 py-2 text-sm bg-gray-300 text-gray-800 rounded-md font-semibold hover:bg-gray-400 transition duration-150">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-lg shadow-indigo-200">
                    <i class="fas fa-plus mr-1"></i> Add Node
                </button>
            </div>
        </form>
    </div>
</div>
