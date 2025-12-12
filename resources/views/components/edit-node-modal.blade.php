<div id="editNodeModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col border border-gray-100">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-2xl font-bold text-gray-800">Edit Organizational Node</h3>
            <button type="button" id="closeEditNodeModal" class="text-gray-500 hover:text-gray-700 transition duration-150 p-2 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="editNodeForm" method="POST" enctype="multipart/form-data" class="space-y-6 flex-grow overflow-y-auto pr-1">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="edit_name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter name">
                </div>
                <div>
                    <label for="edit_position" class="block text-sm font-medium text-gray-700 mb-1">Position (locked)</label>
                    <input type="text" name="position" id="edit_position" required readonly
                        class="w-full px-3 py-2 border border-gray-200 bg-gray-50 text-gray-600 rounded-md shadow-sm" placeholder="Enter position">
                </div>
            
                <div>
                    <label for="edit_image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <div class="mt-1 flex items-center space-x-3">
                        <label for="edit_image" class="w-10 h-10 inline-flex items-center justify-center bg-indigo-600 text-white rounded-full cursor-pointer shadow-sm hover:bg-indigo-700 transition duration-150 ease-in-out border border-indigo-500/60">
                            <i class="fas fa-upload text-base"></i>
                        </label>
                        <input type="file" name="image" id="edit_image" accept="image/*" class="hidden">
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Images only (jpeg, png, jpg, gif, svg), max 2MB.</p>
                    <div class="mt-2" id="current_image_preview">
                        <p class="text-sm text-gray-600">Current Image:</p>
                        <img src="" alt="Current Image" class="w-20 h-20 rounded-full object-cover mt-1 hidden" id="edit_current_image_display"
                             onerror="this.onerror=null;this.classList.add('hidden');">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                <button type="button" id="cancelEditNodeModal" class="px-5 py-2 text-sm bg-gray-100 text-gray-800 rounded-md font-semibold hover:bg-gray-200 transition duration-150 border border-gray-200">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-sm bg-blue-600 text-white rounded-md font-semibold hover:bg-blue-700 transition duration-150 shadow-sm shadow-blue-200">
                    <i class="fas fa-save mr-1"></i> Update Node
                </button>
            </div>
        </form>
    </div>
</div>
