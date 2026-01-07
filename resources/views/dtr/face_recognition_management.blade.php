@extends('layouts.app')

@section('title', 'Face Recognition Management')

@section('content')
<div class="container mx-auto px-6 py-6">
    <header class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-user-shield mr-3 text-indigo-600"></i> Face Recognition Management
        </h1>
        <p class="text-gray-600 mt-2">Enable or disable face recognition access for employees to use clock in/out features.</p>
    </header>

    <div class="bg-white rounded-lg shadow-xl p-6">
        {{-- Filters --}}
        <form action="{{ route('dtr.face-recognition.management') }}" method="GET" class="mb-6 border-b pb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department_id" id="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $selectedDepartmentId == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Employee</label>
                    <input type="text" name="search" id="search" placeholder="Search by name or ID..." 
                           value="{{ $search ?? '' }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 shadow-sm">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                    @if($selectedDepartmentId || $search)
                        <a href="{{ route('dtr.face-recognition.management') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150">
                            <i class="fas fa-times mr-2"></i> Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Employee Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Face Registered</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Face Recognition Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50 transition duration-100"
                            data-employee-row
                            data-name="{{ strtolower($employee->name) }}"
                            data-emp-id="{{ strtolower($employee->employee_id ?? '') }}"
                            data-dept="{{ strtolower($employee->department->name ?? '') }}"
                            data-position="{{ strtolower($employee->position ?? '') }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $employee->position }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $employee->employee_id ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $employee->department->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($employee->face_embedding)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Registered
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-times-circle mr-1"></i> Not Registered
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span id="status-badge-{{ $employee->id }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $employee->face_recognition_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="fas {{ $employee->face_recognition_enabled ? 'fa-check' : 'fa-times' }} mr-1"></i>
                                    {{ $employee->face_recognition_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           class="sr-only peer" 
                                           data-employee-id="{{ $employee->id }}"
                                           {{ $employee->face_recognition_enabled ? 'checked' : '' }}
                                           onchange="toggleFaceRecognition({{ $employee->id }}, this.checked)">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </td>
                        </tr>
                    @empty
                        <tr id="no-results-row" class="hidden">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-users-slash text-4xl mb-2 block"></i>
                                No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Summary Statistics --}}
        @if($employees->count() > 0)
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 pt-6 border-t">
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-green-800">Enabled</div>
                    <div class="text-2xl font-bold text-green-900" id="enabled-count">{{ $employees->where('face_recognition_enabled', true)->count() }}</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-red-800">Disabled</div>
                    <div class="text-2xl font-bold text-red-900" id="disabled-count">{{ $employees->where('face_recognition_enabled', false)->count() }}</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-blue-800">Total Employees</div>
                    <div class="text-2xl font-bold text-blue-900">{{ $employees->count() }}</div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function toggleFaceRecognition(employeeId, enabled) {
    // Show loading state
    const checkbox = document.querySelector(`input[data-employee-id="${employeeId}"]`);
    checkbox.disabled = true;

    fetch(`/dtr/face-recognition/${employeeId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            enabled: enabled
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update status badge
            const badge = document.getElementById(`status-badge-${employeeId}`);
            if (enabled) {
                badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                badge.innerHTML = '<i class="fas fa-check mr-1"></i> Enabled';
            } else {
                badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                badge.innerHTML = '<i class="fas fa-times mr-1"></i> Disabled';
            }

            // Update statistics
            updateStatistics();
        } else {
            // Revert checkbox on error
            checkbox.checked = !enabled;
            alert(data.message || 'An error occurred. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        checkbox.checked = !enabled;
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        checkbox.disabled = false;
    });
}

// Live filter rows as the user types
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search');
    const rows = Array.from(document.querySelectorAll('[data-employee-row]'));
    const noResultsRow = document.getElementById('no-results-row');

    function filterRows() {
        const term = (searchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const empId = row.dataset.empId || '';
            const dept = row.dataset.dept || '';
            const position = row.dataset.position || '';

            const matches = !term || name.includes(term) || empId.includes(term) || dept.includes(term) || position.includes(term);
            row.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });

        if (noResultsRow) {
            noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
        filterRows(); // initial apply on load
    }
});

function updateStatistics() {
    const checkboxes = document.querySelectorAll('input[data-employee-id]');
    let enabledCount = 0;
    let disabledCount = 0;

    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            enabledCount++;
        } else {
            disabledCount++;
        }
    });

    const enabledCountEl = document.getElementById('enabled-count');
    const disabledCountEl = document.getElementById('disabled-count');

    if (enabledCountEl) enabledCountEl.textContent = enabledCount;
    if (disabledCountEl) disabledCountEl.textContent = disabledCount;
}
</script>
@endpush
@endsection

