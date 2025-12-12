@php
    $isKeyRole = in_array(strtolower($node->position), ['ceo', 'co-ceo', 'hr manager']);
    $cacheBust = $node->updated_at ? $node->updated_at->timestamp : time();
    $imageUrl = $node->image_path
        ? asset('storage/' . $node->image_path) . '?v=' . $cacheBust
        : 'https://i.pravatar.cc/150?img=8';
    $isHrManager = strtolower($node->position) === 'hr manager';
@endphp

<div class="flex flex-col items-center relative mb-12">
    <div class="relative">
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
                <div class="flex flex-col gap-2">
                    <button class="edit-node-btn text-indigo-600 hover:text-indigo-800 text-sm font-semibold p-2 rounded-full border border-indigo-100 hover:border-indigo-300 transition flex items-center justify-center"
                            data-id="{{ $node->id }}"
                            data-name="{{ $node->name }}"
                            data-position="{{ $node->position }}"
                            data-parent-id="{{ $node->parent_id }}"
                            data-image-path="{{ $node->image_path }}"
                            title="Edit Node">
                        <i class="fas fa-edit"></i>
                    </button>
                    @unless ($isKeyRole)
                        <form action="{{ route('organization.destroy', $node->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this node and all its descendants?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-semibold px-2 py-1 rounded-md border border-red-100 hover:border-red-300 transition" title="Delete Node">
                                Delete
                            </button>
                        </form>
                    @endunless
                </div>
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
                    @include('organization.partials.node', ['node' => $child, 'departments' => $departments ?? null])
                </div>
            @endforeach
        </div>
    @endif

    @if ($isHrManager && isset($departments))
        <div class="flex justify-center flex-wrap gap-6 mt-10 relative w-full">
            <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-[3px] h-6 bg-gray-300 rounded-full"></div>
            <div class="absolute -top-6 left-0 w-full border-t-2 border-gray-200"></div>

            @foreach ($departments as $department)
                <div class="relative w-64">
                    <div class="bg-white border border-gray-200 shadow rounded-xl">
                        <button type="button"
                                class="w-full text-left px-4 py-3 flex items-center justify-between department-toggle hover:bg-indigo-50 transition"
                                data-target="dept-{{ $department->id }}">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $department->name }}</p>
                                <p class="text-xs text-gray-500">{{ $department->employees->count() }} employees</p>
                            </div>
                            <span class="text-gray-500 text-sm toggle-icon">▼</span>
                        </button>
                        <div id="dept-{{ $department->id }}" class="hidden border-t border-gray-100 bg-gray-50 px-4 py-3 space-y-2">
                            @forelse ($department->employees as $employee)
                                <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-lg px-3 py-2 shadow-sm">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-semibold">
                                        {{ strtoupper(mb_substr($employee->full_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $employee->full_name }}</p>
                                        @if ($employee->position)
                                            <p class="text-xs text-gray-500">{{ $employee->position }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No employees yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
