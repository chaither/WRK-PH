@php
    $isKeyRole = in_array(strtolower($node->position), ['ceo', 'co-ceo', 'hr manager']);
    $isHrManager = strtolower($node->position) === 'hr manager';
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
            {{-- Vertical line from bottom of parent --}}
            <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 w-[2px] h-10 bg-indigo-200"></div>
            
            {{-- Horizontal line spanning the children (hidden if only 1 child) --}}
            @if($node->children->count() > 1)
                <div class="absolute -top-10 left-0 right-0 h-[2px] bg-indigo-200 mx-auto" style="width: calc(100% - 18rem);"></div>
            @endif

            @foreach ($node->children as $child)
                <div class="relative">
                    {{-- Vertical line above child --}}
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 w-[2px] h-10 bg-indigo-200"></div>
                    @include('employee.organization.partials.node', ['node' => $child, 'departments' => $departments ?? null])
                </div>
            @endforeach
        </div>
    @endif

    {{-- Render Departments under HR Manager --}}
    @if ($isHrManager && isset($departments) && $departments->count() > 0)
        <div class="flex justify-center flex-wrap gap-6 mt-10 relative w-full">
            {{-- Vertical line from bottom of parent --}}
            <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 w-[2px] h-10 bg-indigo-200"></div>
            
            {{-- Horizontal line spanning the departments (hidden if only 1 department) --}}
            @if($departments->count() > 1)
                <div class="absolute -top-10 left-0 right-0 h-[2px] bg-indigo-200 mx-auto" style="width: calc(100% - 16rem);"></div>
            @endif

            @foreach ($departments as $department)
                <div class="relative w-64">
                     {{-- Vertical line above department --}}
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 w-[2px] h-10 bg-indigo-200"></div>
                    
                    <div class="bg-white border border-gray-200 shadow rounded-xl" x-data="{ open: false }">
                        <button type="button" 
                                @click="open = !open"
                                class="w-full text-left px-4 py-3 flex items-center justify-between hover:bg-indigo-50 transition rounded-xl">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $department->name }}</p>
                                <p class="text-xs text-gray-500">{{ $department->employees->count() }} employees</p>
                            </div>
                            <span class="text-gray-500 text-sm transition-transform duration-200" :class="{ 'rotate-180': open }">▼</span>
                        </button>
                        <div x-show="open" 
                             x-transition
                             class="border-t border-gray-100 bg-gray-50 px-4 py-3 space-y-2 max-h-60 overflow-y-auto">
                            @forelse ($department->employees as $employee)
                                <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-lg px-3 py-2 shadow-sm">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-semibold shrink-0">
                                        {{ strtoupper(mb_substr($employee->first_name, 0, 1)) }}{{ strtoupper(mb_substr($employee->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $employee->first_name }} {{ $employee->last_name }}</p>
                                        @if ($employee->role)
                                            <p class="text-xs text-gray-500 capitalize">{{ $employee->role }}</p>
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
