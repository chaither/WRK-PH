<div class="flex flex-col items-center relative mb-12">
    
    <!-- Node -->
    <img src="{{ $node->image_path ? asset('storage/' . $node->image_path) : 'https://i.pravatar.cc/150?img=8' }}" alt="{{ $node->position }}" class="w-24 h-24 rounded-full border-4 border-indigo-600 shadow-lg mb-3">
    <div class="bg-indigo-600 text-white text-center px-6 py-3 rounded-xl shadow-lg w-56 transform transition-transform hover:scale-105 group relative">
        <h2 class="font-bold text-lg">{{ $node->position }}</h2>
        <p>{{ $node->name }}</p>
        <div class="absolute top-2 right-2 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <button class="edit-node-btn text-white hover:text-blue-200" 
                    data-id="{{ $node->id }}"
                    data-name="{{ $node->name }}"
                    data-position="{{ $node->position }}"
                    data-parent-id="{{ $node->parent_id }}"
                    data-image-path="{{ $node->image_path }}"
                    title="Edit Node">
                <i class="fas fa-edit"></i>
            </button>
            <form action="{{ route('organization.destroy', $node->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this node and all its descendants?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-white hover:text-red-200" title="Delete Node">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
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
                    @include('organization.partials.node', ['node' => $child])
                </div>
            @endforeach
        </div>
    @endif

</div>
