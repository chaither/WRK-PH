@extends('layouts.app')

@section('title', 'Leave Management')

@section('content')
<div class="mx-6 py-6"> {{-- Consistent compact padding --}}
    <header class="mb-6">
        <h1 class="text-3xl font-bold text-white flex items-center">
            <i class="fas fa-calendar-alt mr-3 text-indigo-600"></i> Leave Balance Management
        </h1>
    </header>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-3xl relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-xl overflow-x-auto border border-gray-200"> {{-- Consistent card styling and border --}}
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider"> {{-- Tighter padding --}}
                        Employee Name
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider">
                        Current Leave Balance
                    </th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($employees as $employee)
                <tr class="hover:bg-indigo-50 transition duration-100 @if ($loop->even) bg-gray-50 @endif"> {{-- Zebra striping --}}
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900"> {{-- Tighter padding --}}
                        {{ $employee->name }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                        {{ $employee->email }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-bold text-indigo-700">
                        {{ $employee->leave_balance }} days
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                        <a href="#" onclick="event.preventDefault(); openEditLeaveBalanceModal({{ $employee->id }}, {{ $employee->leave_balance }})" 
                           class="text-indigo-600 hover:text-indigo-800 p-1.5 inline-flex items-center justify-center rounded-full hover:bg-gray-100 transition duration-150" 
                           title="Edit Leave Balance">
                            <i class="fas fa-edit text-base"></i>
                        </a>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-base text-gray-500 bg-white">
                            <i class="fas fa-users-slash mr-2"></i> No employees found with a manageable leave balance.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('modals')
<div id="editLeaveBalanceModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50 p-4"> {{-- Darker, consistent overlay --}}
    <div class="bg-white rounded-3xl shadow-2xl p-6 max-w-sm w-full transform transition-all duration-300 scale-100"> {{-- Sharper corners, heavy shadow --}}
        <div class="flex justify-between items-center mb-4 border-b pb-2"> {{-- Tighter header --}}
            <h2 class="text-xl font-bold text-gray-800">✍️ Edit Leave Balance</h2>
            <button onclick="closeEditLeaveBalanceModal()" class="text-gray-500 hover:text-gray-900 transition duration-150 p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form id="editLeaveBalanceForm" method="POST" action="" class="space-y-4">
            @csrf
            {{-- Use PUT method if your route requires it, otherwise this may cause issues --}}
            {{-- @method('PUT') --}} 
            <input type="hidden" name="user_id" id="edit_leave_balance_user_id">
            
            <div class="bg-indigo-50 p-4 rounded-3xl border border-indigo-200">
                <label for="leave_balance_amount" class="block text-xs font-medium text-gray-700 mb-1">New Leave Balance (days)</label> {{-- Smaller label --}}
                <input type="number" min="0" name="leave_balance" id="leave_balance_amount" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                       required>
            </div>
            
            <div class="flex justify-end gap-2 pt-2"> {{-- Tighter buttons --}}
                <button type="button" onclick="closeEditLeaveBalanceModal()" 
                        class="px-4 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-100 transition duration-150">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-1.5 text-sm bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openEditLeaveBalanceModal(userId, leaveBalance) {
        document.getElementById('edit_leave_balance_user_id').value = userId;
        document.getElementById('leave_balance_amount').value = leaveBalance;
        
        const form = document.getElementById('editLeaveBalanceForm');
        // Ensure your route is correctly set up in Laravel's web.php to handle this dynamic URL
        // e.g., Route::post('/leave-management/update-balance/{user}', [LeaveController::class, 'updateBalance'])->name('leave.updateBalance');
        form.action = "{{ route('leave.updateBalance', ['user' => '__USER_ID__']) }}".replace('__USER_ID__', userId); 

        document.getElementById('editLeaveBalanceModal').classList.remove('hidden');
        document.getElementById('editLeaveBalanceModal').classList.add('flex');
    }

    function closeEditLeaveBalanceModal() {
        document.getElementById('editLeaveBalanceModal').classList.add('hidden');
        document.getElementById('editLeaveBalanceModal').classList.remove('flex');
    }
</script>
@endpush