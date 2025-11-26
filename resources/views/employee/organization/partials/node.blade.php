<div class="flex flex-col items-center relative mb-12">
    
    <!-- Node -->
    <img src="{{ $node->image_path ? asset('storage/' . $node->image_path) : 'https://i.pravatar.cc/150?img=8' }}" alt="{{ $node->position }}" class="w-24 h-24 rounded-full border-4 border-indigo-600 shadow-lg mb-3">
    <div class="bg-indigo-600 text-white text-center px-6 py-3 rounded-xl shadow-lg w-56">
        <h2 class="font-bold text-lg">{{ $node->position }}</h2>
        <p>{{ $node->name }}</p>
    </div>

    <!-- Check for children -->
    @if ($node->children && $node->children->count() > 0)
        <div class="flex justify-center space-x-12 mt-12 relative">

            <!-- Draw vertical line from parent node -->
            <div class="absolute top-0 left-1/2 w-px h-6 bg-gray-400"></div>

            <!-- Draw horizontal line connecting children -->
            <div class="absolute top-6 left-0 w-full border-t-2 border-gray-400"></div>

            @foreach ($node->children as $child)
                <div class="relative">
                    @include('employee.organization.partials.node', ['node' => $child])
                </div>
            @endforeach
        </div>
    @endif

</div>
