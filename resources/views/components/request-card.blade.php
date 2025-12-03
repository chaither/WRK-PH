<div class="bg-white shadow-md rounded-lg p-6 flex flex-col items-start space-y-4">
    <div class="flex items-center space-x-3">
        <i class="{{ $icon }} text-3xl text-indigo-600"></i>
        <h2 class="text-2xl font-semibold text-gray-700">{{ $title }}</h2>
    </div>
    @if ($count > 0)
        <p class="text-gray-600">Total: <span class="font-bold">{{ $count }}</span></p>
        @if ($latest)
            <div class="w-full">
                <p class="text-gray-600 font-semibold">Latest Request:</p>
                @if ($title === 'Daily Time Records')
                    <p class="text-gray-600 text-sm">Date: {{ $latest->date->format('M d, Y') }}</p>
                    <p class="text-gray-600 text-sm">Status: {{ ucfirst(str_replace('_', ' ', $latest->status)) }}</p>
                @elseif ($title === 'Overtime Requests')
                    <p class="text-gray-600 text-sm">Date: {{ $latest->date->format('M d, Y') }}</p>
                    <p class="text-gray-600 text-sm">Status: {{ ucfirst($latest->status) }}</p>
                @elseif ($title === 'Leave Requests')
                    <p class="text-gray-600 text-sm">Type: {{ ucfirst(str_replace('_', ' ', $latest->leave_type)) }}</p>
                    <p class="text-gray-600 text-sm">Status: {{ ucfirst($latest->status) }}</p>
                @elseif ($title === 'Shift Change Requests')
                    <p class="text-gray-600 text-sm">New Shift: {{ $latest->requestedShift->name ?? 'N/A' }}</p>
                    <p class="text-gray-600 text-sm">Status: {{ ucfirst($latest->status) }}</p>
                @elseif ($title === 'Rest Day Change Requests')
                    <p class="text-gray-600 text-sm">New Rest Day: {{ implode(', ', $latest->requested_restdays ?? []) }}</p>
                    <p class="text-gray-600 text-sm">Status: {{ ucfirst($latest->status) }}</p>
                @elseif ($title === 'No Bio Requests')
                    <p class="text-gray-600 text-sm">Date: {{ $latest->date->format('M d, Y') }}</p>
                    <p class="text-gray-600 text-sm">Status: {{ ucfirst($latest->status) }}</p>
                @endif
            </div>
        @endif
        <a href="{{ $route }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            View History <i class="fas fa-arrow-right ml-2"></i>
        </a>
    @else
        <p class="text-gray-500">{{ $emptyMessage }}</p>
        <a href="{{ $route }}" class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            View History <i class="fas fa-arrow-right ml-2"></i>
        </a>
    @endif
</div>
