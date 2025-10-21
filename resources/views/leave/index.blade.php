@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Leave Management</h1>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Employee Name
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Current Leave Balance
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-900 whitespace-no-wrap">{{ $employee->name }}</p>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-900 whitespace-no-wrap">{{ $employee->email }}</p>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-900 whitespace-no-wrap">{{ $employee->leave_balance }} days</p>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <a href="#" onclick="event.preventDefault(); openEditLeaveBalanceModal({{ $employee->id }}, {{ $employee->leave_balance }})" class="text-blue-500 hover:text-blue-700" title="Edit Leave Balance">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('modals')
<div id="editLeaveBalanceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-md p-6 max-w-sm w-full mx-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Edit Leave Balance</h2>
            <button onclick="closeEditLeaveBalanceModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editLeaveBalanceForm" method="POST" action="" class="space-y-4">
            @csrf
            <input type="hidden" name="user_id" id="edit_leave_balance_user_id">
            <div>
                <label for="leave_balance_amount" class="block text-gray-700 font-medium mb-2">Leave Balance (days)</label>
                <input type="number" min="0" name="leave_balance" id="leave_balance_amount" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" required>
            </div>
            <div class="flex justify-end gap-4 pt-4">
                <button type="button" onclick="closeEditLeaveBalanceModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Changes
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
        form.action = "{{ route('leave.updateBalance', ['user' => '__USER_ID__']) }}".replace('__USER_ID__', userId); // Dynamic action URL

        document.getElementById('editLeaveBalanceModal').classList.remove('hidden');
        document.getElementById('editLeaveBalanceModal').classList.add('flex');
    }

    function closeEditLeaveBalanceModal() {
        document.getElementById('editLeaveBalanceModal').classList.add('hidden');
        document.getElementById('editLeaveBalanceModal').classList.remove('flex');
    }
</script>
@endpush
