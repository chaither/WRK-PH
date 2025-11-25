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
<div id="addNodeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold mb-4">Add New Organizational Node</h3>
        <form action="{{ route('organization.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="position" class="block text-gray-700 text-sm font-bold mb-2">Position:</label>
                <input type="text" name="position" id="position" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="parent_id" class="block text-gray-700 text-sm font-bold mb-2">Reports To (Parent Node):</label>
                <select name="parent_id" id="parent_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">None</option>
                    @foreach($nodes as $node)
                        <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->position }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Image:</label>
                <input type="file" name="image" id="image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add Node</button>
                <button type="button" id="closeAddNodeModal" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Node Modal -->
<div id="editNodeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold mb-4">Edit Organizational Node</h3>
        <form id="editNodeForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="edit_name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                <input type="text" name="name" id="edit_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="edit_position" class="block text-gray-700 text-sm font-bold mb-2">Position:</label>
                <input type="text" name="position" id="edit_position" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="edit_parent_id" class="block text-gray-700 text-sm font-bold mb-2">Reports To (Parent Node):</label>
                <select name="parent_id" id="edit_parent_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">None</option>
                    @foreach($nodes as $node)
                        <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->position }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="edit_image" class="block text-gray-700 text-sm font-bold mb-2">Image:</label>
                <input type="file" name="image" id="edit_image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <div class="mt-2" id="current_image_preview">
                    <p class="text-sm text-gray-600">Current Image:</p>
                    <img src="" alt="Current Image" class="w-20 h-20 rounded-full object-cover mt-1 hidden" id="edit_current_image_display">
                </div>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Node</button>
                <button type="button" id="closeEditNodeModal" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Cancel</button>
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

        // Add Node Modal handlers
        addNodeBtn.addEventListener('click', function() {
            addNodeModal.classList.remove('hidden');
        });

        closeAddNodeModal.addEventListener('click', function() {
            addNodeModal.classList.add('hidden');
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
                } else {
                    editCurrentImageDisplay.src = '';
                    editCurrentImageDisplay.classList.add('hidden');
                }
                editCurrentImageDisplay.dataset.originalPath = imagePath; // Store original path

                editNodeModal.classList.remove('hidden');
            }
        });

        closeEditNodeModal.addEventListener('click', function() {
            editNodeModal.classList.add('hidden');
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target == addNodeModal) {
                addNodeModal.classList.add('hidden');
            }
            if (event.target == editNodeModal) {
                editNodeModal.classList.add('hidden');
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
            } else {
                // If no file selected, show current image or hide if none
                const imagePath = editCurrentImageDisplay.dataset.originalPath; // Use the stored original path
                if (imagePath && imagePath !== 'null') {
                    editCurrentImageDisplay.src = `/storage/${imagePath}`;
                    editCurrentImageDisplay.classList.remove('hidden');
                } else {
                    editCurrentImageDisplay.src = '';
                    editCurrentImageDisplay.classList.add('hidden');
                }
            }
        });
    });
</script>
@endpush
@endsection
