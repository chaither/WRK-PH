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
@include('components.add-node-modal')

<!-- Edit Node Modal -->
@include('components.edit-node-modal')

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
            addNodeModal.classList.add('flex');
        });

        closeAddNodeModal.addEventListener('click', function() {
            addNodeModal.classList.add('hidden');
            addNodeModal.classList.remove('flex');
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
                editNodeModal.classList.add('flex');
            }
        });

        closeEditNodeModal.addEventListener('click', function() {
            editNodeModal.classList.add('hidden');
            editNodeModal.classList.remove('flex');
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
