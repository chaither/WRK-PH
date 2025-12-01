@extends('layouts.app')

@section('title', 'Organization Chart')

@section('content')
<section class="py-16 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-end mb-4">
            <button id="addNodeBtn" class="bg-green-500 text-white px-4 py-2 rounded-md font-semibold">Add New Node</button>
        </div>

        <!-- Organizational Chart Content -->
        <div class="space-y-12 w-full max-w-6xl mx-auto py-8">
            @php
                $ceoNode = $nodes->whereNull('parent_id')->first();
            @endphp

            @if ($ceoNode)
                @include('organization.partials.node', ['node' => $ceoNode])
            @else
                <p class="text-center text-gray-500">No organizational chart nodes found. Start by adding a CEO!</p>
            @endif
        </div>
    </div>
</section>

<!-- Add Node Modal -->
<div id="addNodeModal" class="fixed inset-0 bg-transparent hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b pb-4">
            <h3 class="text-2xl font-bold text-gray-800">Add New Organizational Node</h3>
            <button onclick="closeAddNodeModal()" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form action="{{ route('organization.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 flex-grow overflow-y-auto pr-2">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name:</label>
                    <input type="text" name="name" id="name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter name" required>
                </div>
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position:</label>
                    <input type="text" name="position" id="position" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter position" required>
                </div>
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Reports To (Parent Node):</label>
                    <select name="parent_id" id="parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">None</option>
                        @foreach($nodes as $node)
                            <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->position }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image:</label>
                    <div class="mt-1 flex items-center space-x-4">
                        <label for="image" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-md cursor-pointer hover:bg-blue-700 transition duration-150 ease-in-out">
                            <i class="fas fa-upload text-lg mr-2"></i>
                            <span>Choose File</span>
                        </label>
                        <span id="fileName" class="text-gray-600 text-sm">No file chosen</span>
                        <input type="file" name="image" id="image" class="hidden" onchange="document.getElementById('fileName').innerText = this.files[0] ? this.files[0].name : 'No file chosen'">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-plus mr-2"></i>Add Node
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Node Modal -->
<div id="editNodeModal" class="fixed inset-0 bg-transparent hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b pb-4">
            <h3 class="text-2xl font-bold text-gray-800">Edit Organizational Node</h3>
            <button onclick="closeEditNodeModal()" class="text-red-500 hover:text-red-700 transition duration-150 p-1 rounded-full hover:bg-red-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form id="editNodeForm" method="POST" enctype="multipart/form-data" class="space-y-6 flex-grow overflow-y-auto pr-2">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Name:</label>
                    <input type="text" name="name" id="edit_name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div>
                    <label for="edit_position" class="block text-sm font-medium text-gray-700 mb-1">Position:</label>
                    <input type="text" name="position" id="edit_position" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div>
                    <label for="edit_parent_id" class="block text-sm font-medium text-gray-700 mb-1">Reports To (Parent Node):</label>
                    <select name="parent_id" id="edit_parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">None</option>
                        @foreach($nodes as $node)
                            <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->position }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_image" class="block text-sm font-medium text-gray-700 mb-1">Image:</label>
                    <div class="mt-1 flex items-center space-x-4">
                        <label for="edit_image" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-md cursor-pointer hover:bg-blue-700 transition duration-150 ease-in-out">
                            <i class="fas fa-upload text-lg mr-2"></i>
                            <span>Choose File</span>
                        </label>
                        <span id="editFileName" class="text-gray-600 text-sm">No file chosen</span>
                        <input type="file" name="image" id="edit_image" class="hidden" onchange="document.getElementById('editFileName').innerText = this.files[0] ? this.files[0].name : 'No file chosen'">
                    </div>
                    <div class="mt-2" id="current_image_preview">
                        <p class="text-sm text-gray-600">Current Image:</p>
                        <img src="" alt="Current Image" class="w-20 h-20 rounded-full object-cover mt-1 hidden" id="edit_current_image_display">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-save mr-2"></i>Update Node
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addNodeBtn = document.getElementById('addNodeBtn');
        const addNodeModal = document.getElementById('addNodeModal');
        const closeAddNodeModal = document.getElementById('closeAddNodeModal');

        const editNodeModal = document.getElementById('editNodeModal');
        const closeEditNodeModal = document.getElementById('closeEditNodeModal');
        const editNodeForm = document.getElementById('editNodeForm');
        const editName = document.getElementById('edit_name');
        const editPosition = document.getElementById('edit_position');
        const editParentId = document.getElementById('edit_parent_id');
        const editImage = document.getElementById('edit_image');
        const editCurrentImageDisplay = document.getElementById('edit_current_image_display');
        const editFileName = document.getElementById('editFileName'); // Get the edit file name span

        // Global functions to close modals
        window.closeAddNodeModal = function() {
            addNodeModal.classList.add('hidden');
            addNodeModal.classList.remove('flex');
        };

        window.closeEditNodeModal = function() {
            editNodeModal.classList.add('hidden');
            editNodeModal.classList.remove('flex');
        };

        // Add Node Modal handlers
        addNodeBtn.addEventListener('click', function() {
            addNodeModal.classList.remove('hidden');
            addNodeModal.classList.add('flex'); // Use flex to center the modal
        });

        // Edit Node Modal handlers
        document.addEventListener('click', function(event) {
            if (event.target.closest('.edit-node-btn')) {
                const button = event.target.closest('.edit-node-btn');
                const id = button.dataset.id;
                const name = button.dataset.name;
                const position = button.dataset.position;
                const parentId = button.dataset.parentId;
                const imagePath = button.dataset.imagePath;

                editNodeForm.action = `/organization/${id}`;
                editName.value = name;
                editPosition.value = position;
                editParentId.value = parentId;

                if (imagePath && imagePath !== 'null') {
                    editCurrentImageDisplay.src = `/storage/${imagePath}`;
                    editCurrentImageDisplay.classList.remove('hidden');
                    editFileName.innerText = imagePath.split('/').pop(); // Display only the file name
                } else {
                    editCurrentImageDisplay.src = '';
                    editCurrentImageDisplay.classList.add('hidden');
                    editFileName.innerText = 'No file chosen';
                }
                editCurrentImageDisplay.dataset.originalPath = imagePath; // Store original path

                editNodeModal.classList.remove('hidden');
                editNodeModal.classList.add('flex'); // Use flex to center the modal
            }
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target == addNodeModal) {
                addNodeModal.classList.add('hidden');
                addNodeModal.classList.remove('flex');
            }
            if (event.target == editNodeModal) {
                editNodeModal.classList.add('hidden');
                editNodeModal.classList.remove('flex');
            }
        });

        // Image preview for edit modal
        editImage.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    editCurrentImageDisplay.src = e.target.result;
                    editCurrentImageDisplay.classList.remove('hidden');
                };
                reader.readAsDataURL(this.files[0]);
                editFileName.innerText = this.files[0].name; // Display new file name
            } else {
                // If no file selected, show current image or hide if none
                const imagePath = editCurrentImageDisplay.dataset.originalPath;
                if (imagePath && imagePath !== 'null') {
                    editCurrentImageDisplay.src = `/storage/${imagePath}`;
                    editCurrentImageDisplay.classList.remove('hidden');
                    editFileName.innerText = imagePath.split('/').pop(); // Display original file name
                } else {
                    editCurrentImageDisplay.src = '';
                    editCurrentImageDisplay.classList.add('hidden');
                    editFileName.innerText = 'No file chosen';
                }
            }
        });
    });
</script>
@endpush
@endsection
