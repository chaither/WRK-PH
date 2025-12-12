@php
    $isKeyRole = in_array(strtolower($node->position), ['ceo', 'co-ceo', 'hr manager']);
    $cacheBust = $node->updated_at ? $node->updated_at->timestamp : time();
    $imageUrl = $node->image_path
        ? asset('storage/' . $node->image_path) . '?v=' . $cacheBust
        : 'https://i.pravatar.cc/150?img=8';
@endphp

<div class="flex flex-col items-center relative mb-12">
    <div class="bg-white border border-indigo-50 shadow-lg rounded-2xl w-72">
        <div class="p-4 flex items-center gap-4">
            <div class="relative w-16 h-16 flex-shrink-0">
                <div class="absolute inset-0 rounded-full bg-indigo-100 blur-sm"></div>
                <div class="relative w-full h-full rounded-full border-2 border-indigo-500 overflow-hidden shadow-sm bg-white">
                    <img src="{{ $imageUrl }}" alt="{{ $node->position }}" class="w-full h-full object-cover"
                         onerror="this.onerror=null;this.src='https://i.pravatar.cc/150?img=8';">
                </div>
            </div>
            <div class="flex-1">
                <p class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wide">{{ $node->position }}</p>
                <p class="text-lg font-semibold text-gray-900 leading-6">{{ $node->name }}</p>
            </div>
        </div>
    </div>

    @if ($node->children && $node->children->count() > 0)
        <div class="flex justify-center space-x-12 mt-10 relative w-full">
            <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-[3px] h-6 bg-gray-300 rounded-full"></div>
            <div class="absolute -top-6 left-0 w-full border-t-2 border-gray-200"></div>

            @foreach ($node->children as $child)
                <div class="relative">
                    <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-[3px] h-6 bg-gray-200 rounded-full"></div>
                    @include('employee.organization.partials.node', ['node' => $child])
                </div>
            @endforeach
        </div>
    @endif
</div>
