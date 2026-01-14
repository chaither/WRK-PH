@extends('layouts.app')

@section('title', 'Shift Management')

@section('content')
<div class="mx-6 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-white flex items-center">
            <i class="fas fa-clock mr-3 text-indigo-400"></i> Shift Management
        </h1>
        <button onclick="openShiftModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-150 shadow-md flex items-center">
            <i class="fas fa-plus mr-2"></i> Add Shift
        </button>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-3xl relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-3xl relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Shifts Table -->
    <div class="bg-white shadow-md rounded-3xl overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Night Shift</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pay Multiplier</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Employees</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-gray-900">
                @forelse ($shifts as $shift)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-2 text-indigo-500"></i>
                                <span class="font-semibold">{{ $shift->name }}</span>
                                @if($shift->is_night_shift)
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        <i class="fas fa-moon mr-1"></i>Night
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                            <i class="fas fa-hourglass-start mr-2 text-green-500"></i>
                            {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                            <i class="fas fa-hourglass-end mr-2 text-red-500"></i>
                            {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($shift->is_night_shift)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                    <i class="fas fa-check mr-1"></i> Yes
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <i class="fas fa-times mr-1"></i> No
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900">
                            @if($shift->is_night_shift)
                                <span class="font-bold text-indigo-600">{{ number_format($shift->night_shift_multiplier, 2) }}x</span>
                                <span class="text-xs text-gray-500">(+{{ number_format(($shift->night_shift_multiplier - 1) * 100, 0) }}%)</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                {{ $shift->users()->count() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <button onclick='openShiftModal(@json($shift))' class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="openDeleteShiftModal({{ $shift->id }}, '{{ $shift->name }}', {{ $shift->users()->count() }})" 
                                    class="text-red-600 hover:text-red-900" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No shifts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Shift Modal -->
<div id="deleteShiftModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 p-6 transform transition-all duration-300 scale-100 flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
            <h3 class="text-xl font-bold text-gray-800">Delete Shift</h3>
            <button onclick="closeDeleteShiftModal()" class="text-gray-400 hover:text-gray-600 transition duration-150 p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <div class="mb-6">
            <p class="text-gray-600 text-base">Are you sure you want to delete the shift <span id="deleteShiftName" class="font-bold text-gray-900"></span>?</p>
            <p id="deleteShiftWarning" class="text-sm text-red-500 mt-3 flex items-center bg-red-50 p-3 rounded-lg border border-red-100 hidden">
                <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i> 
                <span id="deleteShiftWarningText"></span>
            </p>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button onclick="closeDeleteShiftModal()" type="button" class="px-5 py-2.5 text-sm border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition duration-150">
                Cancel
            </button>
            <form id="deleteShiftForm" action="" method="POST" class="inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-5 py-2.5 text-sm bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition duration-150 shadow-md flex items-center">
                    <i class="fas fa-trash-alt mr-2"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('modals')
    @include('components.shift_modal')
@endpush

@push('scripts')
<script>
    function openDeleteShiftModal(id, name, employeeCount) {
        const modal = document.getElementById('deleteShiftModal');
        const nameSpan = document.getElementById('deleteShiftName');
        const form = document.getElementById('deleteShiftForm');
        const warning = document.getElementById('deleteShiftWarning');
        const warningText = document.getElementById('deleteShiftWarningText');

        nameSpan.textContent = name;
        form.action = `/shifts/${id}`;
        
        if (employeeCount > 0) {
            warning.classList.remove('hidden');
            warningText.textContent = `This shift is assigned to ${employeeCount} employee(s). You cannot delete it.`;
            form.querySelector('button[type="submit"]').disabled = true;
            form.querySelector('button[type="submit"]').classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warning.classList.add('hidden');
            form.querySelector('button[type="submit"]').disabled = false;
            form.querySelector('button[type="submit"]').classList.remove('opacity-50', 'cursor-not-allowed');
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteShiftModal() {
        const modal = document.getElementById('deleteShiftModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
