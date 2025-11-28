<div id="editNodeModal" class="fixed inset-0 bg-black bg-opacity-25 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 class="text-2xl font-bold text-gray-800">Edit Organizational Node</h3>
            <button type="button" id="closeEditNodeModal" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="editNodeForm" method="POST" enctype="multipart/form-data" class="space-y-6 flex-grow overflow-y-auto pr-2">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="edit_name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter name">
                </div>
                <div>
                    <label for="edit_position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <input type="text" name="position" id="edit_position" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter position">
                </div>
                <div>
                    <label for="edit_parent_id" class="block text-sm font-medium text-gray-700 mb-1">Reports To (Parent Node)</label>
                    <select name="parent_id" id="edit_parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">None</option>
                        @foreach($nodes as $node)
                            <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->position }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <input type="file" name="image" id="edit_image" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <div class="mt-2" id="current_image_preview">
                        <p class="text-sm text-gray-600">Current Image:</p>
                        <img src="" alt="Current Image" class="w-20 h-20 rounded-full object-cover mt-1 hidden" id="edit_current_image_display">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                <button type="button" id="cancelEditNodeModal" class="px-6 py-2 text-sm bg-gray-300 text-gray-800 rounded-md font-semibold hover:bg-gray-400 transition duration-150">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 text-sm bg-blue-500 text-white rounded-md font-semibold hover:bg-blue-600 transition duration-150 shadow-lg shadow-blue-200">
                    <i class="fas fa-save mr-1"></i> Update Node
                </button>
            </div>
        </form>
    </div>
</div>
