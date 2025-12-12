@extends('layouts.app')

@section('title', 'Organization Chart')

@section('content')
@php
    $today = now()->format('l, F d, Y');
    $ceoNode = $nodes->whereNull('parent_id')->first();
@endphp

<section class="relative bg-gradient-to-b from-[#f5f7fb] via-[#eef2fb] to-[#f7f9ff] min-h-screen py-10 overflow-hidden">
    <!-- Parallax layers -->
    <div class="pointer-events-none absolute inset-0">
        <div class="parallax-layer absolute -left-20 top-10 w-56 h-56 rounded-full bg-indigo-100/60 blur-3xl" data-speed="0.25"></div>
        <div class="parallax-layer absolute right-[-60px] top-24 w-72 h-72 rounded-full bg-blue-100/50 blur-3xl" data-speed="0.18"></div>
        <div class="parallax-layer absolute left-1/3 bottom-10 w-64 h-64 rounded-full bg-purple-100/45 blur-3xl" data-speed="0.12"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 space-y-8">
        <div class="bg-white/90 backdrop-blur border border-indigo-50 rounded-2xl shadow-lg p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">{{ $today }}</p>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">HRIS DASHBOARD</h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-gray-100 rounded-full p-1 flex">
                        <a href="{{ Auth::user()->isAdmin() ? route('dashboard') : '#' }}" class="px-4 py-2 text-sm font-semibold text-gray-500 rounded-full">Graph</a>
                        <button class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-full shadow">Org</button>
                    </div>
                </div>
            </div>
            @if(session('success'))
                <div class="mt-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md text-sm space-y-1">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">Organizational Chart</h2>
            </div>

            <div class="bg-[#f0f1f6] px-4 py-8">
                <div id="orgChartViewport" class="w-full max-w-6xl mx-auto overflow-auto relative h-[85vh]">
                    <div id="orgChartContent" class="relative w-max h-max">
                        @if ($ceoNode)
                            @include('organization.partials.node', ['node' => $ceoNode, 'departments' => $departments])
                        @else
                            <p class="text-center text-gray-500">No organizational chart nodes found. Start by adding a CEO!</p>
                        @endif
                    </div>
                </div>
            </div>
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
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image:</label>
                    <div class="mt-1 flex items-center space-x-4">
                        <label for="image" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-md cursor-pointer hover:bg-blue-700 transition duration-150 ease-in-out">
                            <i class="fas fa-upload text-lg mr-2"></i>
                            <span>Choose File</span>
                        </label>
                        <span id="fileName" class="text-gray-600 text-sm">No file chosen</span>
                        <input type="file" name="image" id="image" accept="image/*" class="hidden" onchange="document.getElementById('fileName').innerText = this.files[0] ? this.files[0].name : 'No file chosen'">
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Images only (jpeg, png, jpg, gif, svg, webp), max 5MB.</p>
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
        const editImage = document.getElementById('edit_image');
        const editCurrentImageDisplay = document.getElementById('edit_current_image_display');
        const closeEditNodeModalBtn = document.getElementById('closeEditNodeModal');
        const cancelEditNodeModalBtn = document.getElementById('cancelEditNodeModal');
        const parallaxLayers = document.querySelectorAll('.parallax-layer');

        // Global functions to close modals
        window.closeAddNodeModal = function() {
            addNodeModal.classList.add('hidden');
            addNodeModal.classList.remove('flex');
        };

        window.closeEditNodeModal = function() {
            editNodeModal.classList.add('hidden');
            editNodeModal.classList.remove('flex');
        };

        if (closeEditNodeModalBtn) {
            closeEditNodeModalBtn.addEventListener('click', window.closeEditNodeModal);
        }
        if (cancelEditNodeModalBtn) {
            cancelEditNodeModalBtn.addEventListener('click', window.closeEditNodeModal);
        }

        // Add Node Modal handlers
        if (addNodeBtn) {
            addNodeBtn.addEventListener('click', function() {
                addNodeModal.classList.remove('hidden');
                addNodeModal.classList.add('flex'); // Use flex to center the modal
            });
        }

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
                if (imagePath && imagePath !== 'null') {
                    editCurrentImageDisplay.src = `/storage/${imagePath}`;
                    editCurrentImageDisplay.classList.remove('hidden');
                } else {
                    editCurrentImageDisplay.src = '';
                    editCurrentImageDisplay.classList.add('hidden');
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
            } else {
                // If no file selected, show current image or hide if none
                const imagePath = editCurrentImageDisplay.dataset.originalPath;
                if (imagePath && imagePath !== 'null') {
                    editCurrentImageDisplay.src = `/storage/${imagePath}`;
                    editCurrentImageDisplay.classList.remove('hidden');
                } else {
                    editCurrentImageDisplay.src = '';
                    editCurrentImageDisplay.classList.add('hidden');
                }
            }
        });

        // Department dropdowns under HR Manager
        document.querySelectorAll('.department-toggle').forEach(function(toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const targetId = this.dataset.target;
                const target = document.getElementById(targetId);
                if (!target) return;
                target.classList.toggle('hidden');

                const icon = this.querySelector('.toggle-icon');
                if (icon) {
                    icon.textContent = target.classList.contains('hidden') ? '▼' : '▲';
                }
            });
        });

        // Parallax (lightweight, respects reduced motion)
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!prefersReducedMotion && parallaxLayers.length) {
            const onScroll = () => {
                const y = window.scrollY;
                parallaxLayers.forEach(layer => {
                    const speed = parseFloat(layer.dataset.speed || '0.2');
                    layer.style.transform = `translateY(${y * speed}px)`;
                });
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        // Organization Chart Zoom and Pan
        const orgChartViewport = document.getElementById('orgChartViewport');
        const orgChartContent = document.getElementById('orgChartContent');

        if (orgChartViewport && orgChartContent) {
            let scale = 1;
            let translateX = 0;
            let translateY = 0;
            let isDragging = false;
            let startX;
            let startY;

            function applyTransform() {
                orgChartContent.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
                orgChartContent.style.transformOrigin = '0 0'; // Set transform origin to top-left
            }

            orgChartViewport.addEventListener('wheel', (event) => {
                event.preventDefault();

                const scaleAmount = -event.deltaY * 0.001; // Adjust zoom sensitivity
                const newScale = scale + scaleAmount;

                // Prevent zooming too far in or out
                if (newScale >= 0.2 && newScale <= 3) { // Min scale 0.2, Max scale 3
                    // Calculate mouse position relative to the orgChartContent
                    const rect = orgChartContent.getBoundingClientRect();
                    const mouseX = event.clientX - rect.left;
                    const mouseY = event.clientY - rect.top;

                    // Adjust translateX and translateY to keep the mouse pointer fixed during zoom
                    translateX -= mouseX * (newScale - scale) / newScale;
                    translateY -= mouseY * (newScale - scale) / newScale;

                    scale = newScale;
                    applyTransform();
                }
            });

            orgChartViewport.addEventListener('mousedown', (event) => {
                isDragging = true;
                startX = event.clientX - translateX;
                startY = event.clientY - translateY;
                orgChartViewport.style.cursor = 'grabbing';
            });

            window.addEventListener('mousemove', (event) => {
                if (!isDragging) return;
                event.preventDefault(); // Prevent text selection during drag
                translateX = event.clientX - startX;
                translateY = event.clientY - startY;
                applyTransform();
            });

            window.addEventListener('mouseup', () => {
                isDragging = false;
                orgChartViewport.style.cursor = 'grab';
            });

            // Initialize cursor style and transform
            orgChartViewport.style.cursor = 'grab';
            applyTransform();
        }
    });
</script>
@endpush
@endsection
