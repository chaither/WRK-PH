@extends('layouts.app')

@section('title', 'Employee Dashboard')

@section('content')
<div class="min-h-screen bg-transparent relative">
    <!-- Header Section -->
    <div class="bg-navy-800/80 backdrop-blur-sm shadow-lg border-b border-navy-700/50 p-6 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    Welcome, {{ auth()->user()->name }}
                </h1>
                
            </div>
            <div class="flex items-center space-x-4">
                @if(auth()->user()->shift)
                    <span class="text-sm text-gray-700"><i class="fas fa-clock mr-1"></i> Shift: {{ auth()->user()->shift->name }} ({{ \Illuminate\Support\Carbon::parse(auth()->user()->shift->start_time)->format('h:i A') }} - {{ \Illuminate\Support\Carbon::parse(auth()->user()->shift->end_time)->format('h:i A') }})</span>
                @else
                    <span class="text-sm text-gray-700">Shift: Not Assigned</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-6">
        @if(auth()->user()->role === 'employee')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Quick Stats Card -->
            <div class="bg-white rounded-lg shadow-md p-6 h-full">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Quick Stats</h2>
                <div class="space-y-3">
                    <p class="text-gray-600 flex justify-between items-center">
                        <span><i class="fas fa-user-tie mr-2 text-blue-500"></i>Position:</span>
                        <span class="font-medium text-gray-800">{{ auth()->user()->position }}</span>
                    </p>
                    <p class="text-gray-600 flex justify-between items-center">
                        <span><i class="fas fa-briefcase mr-2 text-indigo-500"></i>Department:</span>
                        <span class="font-medium text-gray-800">{{ auth()->user()->department->name ?? 'N/A' }}</span>
                    </p>
                    <p class="text-gray-600 flex justify-between items-center">
                        <span><i class="fas fa-calendar-alt mr-2 text-green-500"></i>Start Date:</span>
                        <span class="font-medium text-gray-800">{{ \Illuminate\Support\Carbon::parse(auth()->user()->start_date)->format('M d, Y') }}</span>
                    </p>
                    <p class="text-gray-600 flex justify-between items-center">
                        <span><i class="fas fa-calendar-check mr-2 text-purple-500"></i>Working Days:</span>
                        <span class="font-medium text-gray-800">{{ implode(', ', auth()->user()->working_days ?? ['N/A']) }}</span>
                    </p>
                    <p class="text-gray-600 flex justify-between items-center">
                        <span><i class="fas fa-couch mr-2 text-red-500"></i>Rest Days:</span>
                        <span class="font-medium text-gray-800">{{ implode(', ', auth()->user()->rest_days ?? ['N/A']) }}</span>
                    </p>
                </div>
            </div>

            <!-- Leave Balance Card -->
            <div class="bg-white rounded-lg shadow-md p-6 h-full">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Leave Balance</h2>
                <div class="flex items-center justify-between">
                    <p class="text-5xl font-bold text-blue-600">{{ auth()->user()->leave_balance ?? 0 }}</p>
                    <span class="text-gray-500">Days Remaining</span>
                </div>
                <a href="{{ route('employee.leave.index') }}" class="mt-4 inline-block text-blue-500 hover:text-blue-700 text-sm font-medium">
                    Request Leave <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <!-- Organization Chart Link Card -->
            <div class="bg-white rounded-lg shadow-md p-6 h-full">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Organization Chart</h2>
                <p class="text-gray-600 mb-4">View the company's organizational structure.</p>
                <a href="{{ route('employee.organization.index') }}" class="inline-block bg-indigo-500 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    View Chart <i class="fas fa-sitemap ml-2"></i>
                </a>
            </div>

            <!-- Absences List Card -->
            <div class="bg-white rounded-lg shadow-md p-6 h-full">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">My Absences</h2>
                @if($absences->count() > 0)
                    <ul class="list-disc pl-5 space-y-2 text-gray-700 max-h-40 overflow-y-auto pr-2">
                        @foreach($absences as $absence)
                            <li>{{ \Illuminate\Support\Carbon::parse($absence->date)->format('M d, Y') }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-600">No absences recorded.</p>
                @endif
            </div>

            <!-- Next Shift Card (Optional, if you want to display this prominently) -->
            <div class="bg-white rounded-lg shadow-md p-6 h-full">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Your Shift Details</h2>
                @if(auth()->user()->shift)
                    <div class="space-y-3">
                        <p class="text-gray-600 flex justify-between items-center">
                            <span><i class="fas fa-clock mr-2 text-indigo-500"></i>Shift Name:</span>
                            <span class="font-medium text-gray-800">{{ auth()->user()->shift->name }}</span>
                        </p>
                        <p class="text-gray-600 flex justify-between items-center">
                            <span><i class="fas fa-hourglass-start mr-2 text-green-500"></i>Start Time:</span>
                            <span class="font-medium text-gray-800">{{ \Illuminate\Support\Carbon::parse(auth()->user()->shift->start_time)->format('h:i A') }}</span>
                        </p>
                        <p class="text-gray-600 flex justify-between items-center">
                            <span><i class="fas fa-hourglass-end mr-2 text-red-500"></i>End Time:</span>
                            <span class="font-medium text-gray-800">{{ \Illuminate\Support\Carbon::parse(auth()->user()->shift->end_time)->format('h:i A') }}</span>
                        </p>
                    </div>
                @else
                    <p class="text-gray-600">No shift assigned.</p>
                @endif
            </div>

            <!-- Salary Notification Update Card -->
            <div class="bg-white rounded-lg shadow-md p-6 h-full">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">General Notifications</h2>
                @if($generalNotifications->count() > 0)
                    <div class="flex items-center justify-between mb-3">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="selectAllNotifications" class="form-checkbox text-blue-600 rounded"/>
                            <span class="ml-2 text-sm text-gray-700">Select All</span>
                        </label>
                        <button id="deleteSelectedNotifications" class="bg-red-500 text-white px-3 py-1 rounded-md text-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300" style="display: none;">
                            Delete Selected
                        </button>
                    </div>
                    <ul class="space-y-3 max-h-60 overflow-y-auto pr-2">
                        @foreach($generalNotifications as $notification)
                            <li class="bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-3 rounded-md flex justify-between items-center" id="notification-{{ $notification->id ?? 'salary' }}">
                                <div class="flex items-center">
                                    @if(isset($notification->id))
                                        <input type="checkbox" name="selected_notifications[]" value="{{ $notification->id }}" class="notification-checkbox form-checkbox text-blue-600 rounded mr-2"/>
                                    @endif
                                    <div>
                                        @if(isset($notification->link))
                                            <a href="{{ $notification->link }}" class="font-semibold text-blue-800 hover:text-blue-600" onclick="markNotificationAsReadAndRedirect('{{ $notification->id }}', '{{ $notification->link }}')">
                                                {{ $notification->message }}
                                            </a>
                                        @else
                                            <p class="font-semibold">{{ $notification->message }}</p>
                                        @endif
                                        <p class="text-xs text-blue-600 mt-1">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @if(isset($notification->id))
                                    <form action="{{ route('employee.notifications.destroy', $notification->id) }}" method="POST" class="delete-notification-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 focus:outline-none">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-600">No new notifications at this time.</p>
                @endif
                <div class="mt-4 text-center">
                    <a href="{{ route('employee.notifications.history') }}" class="inline-block text-blue-600 hover:text-blue-800 font-medium">
                        View all notifications
                    </a>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 mt-8">
            <h2 class="text-xl font-bold mb-4 text-gray-900">My Payslips</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Pay Period</th>
                            <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Net Pay</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900">
                        @forelse($payslips as $payslip)
                            <tr class="border-b">
                                <td class="px-4 py-2 text-left text-gray-900">
                                    {{ $payslip->payPeriod->start_date->format('M d, Y') }} - {{ $payslip->payPeriod->end_date->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-2 text-right text-gray-900">₱{{ number_format($payslip->net_pay, 2) }}</td>
                                <td class="px-4 py-2 text-left">
                                    @if($payslip->payPeriod->status === 'paid')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Done Payment
                                        </span>
                                    @elseif($payslip->payPeriod->status === 'closed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-800">
                                            Closed
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <a href="{{ route('payroll.show-payslip', ['employee' => auth()->id(), 'payPeriod' => $payslip->pay_period_id]) }}" class="text-blue-500 hover:text-blue-700" title="View Payslip">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-center text-gray-500">No payslips found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // The profile dropdown toggle and close logic is now handled in layouts/app.blade.php

        const selectAllCheckbox = document.getElementById('selectAllNotifications');
        const deleteSelectedButton = document.getElementById('deleteSelectedNotifications');
        const notificationCheckboxes = document.querySelectorAll('.notification-checkbox');
        const ulElement = document.querySelector('.space-y-3.max-h-60');

        // Function to update the state of the "Delete Selected" button
        function updateDeleteSelectedButtonState() {
            const anyCheckboxChecked = Array.from(notificationCheckboxes).some(checkbox => checkbox.checked);
            deleteSelectedButton.style.display = anyCheckboxChecked ? 'inline-block' : 'none';
        }

        // Event listener for "Select All" checkbox
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                notificationCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateDeleteSelectedButtonState();
            });
        }

        // Event listener for individual notification checkboxes
        notificationCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = false;
                    }
                }
                updateDeleteSelectedButtonState();
            });
        });

        // Initial state update for the "Delete Selected" button
        updateDeleteSelectedButtonState();


        document.querySelectorAll('.delete-notification-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!confirm('Are you sure you want to delete this notification?')) {
                    return;
                }

                const form = this;
                const actionUrl = form.getAttribute('action');
                const notificationId = form.closest('li').id;

                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        // Remove the notification item from the DOM
                        const notificationElement = document.getElementById(notificationId);
                        if (notificationElement) {
                            notificationElement.remove();

                            // Check if there are no more notifications
                            if (ulElement && ulElement.children.length === 0) {
                                const parentDiv = ulElement.closest('div.bg-white');
                                if (parentDiv) {
                                    parentDiv.innerHTML = '\n                <h2 class="text-lg font-semibold text-gray-700 mb-4">General Notifications</h2>\n                    <p class="text-gray-600">No new notifications at this time.</p>\n                ';
                                }
                            }
                            updateDeleteSelectedButtonState();
                        }
                    } else if (data.error) {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error deleting notification:', error);
                    alert('An error occurred while deleting the notification.');
                });
            });
        });

        // Event listener for "Delete Selected" button
        if (deleteSelectedButton) {
            deleteSelectedButton.addEventListener('click', function() {
                const selectedNotificationIds = Array.from(notificationCheckboxes)
                    .filter(checkbox => checkbox.checked && checkbox.value !== 'salary')
                    .map(checkbox => checkbox.value);

                if (selectedNotificationIds.length === 0) {
                    alert('Please select at least one notification to delete.');
                    return;
                }

                if (!confirm(`Are you sure you want to delete ${selectedNotificationIds.length} selected notifications?`)) {
                    return;
                }

                fetch('{{ route('employee.notifications.bulkDestroy') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ _method: 'DELETE', ids: selectedNotificationIds })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        selectedNotificationIds.forEach(id => {
                            const notificationElement = document.getElementById(`notification-${id}`);
                            if (notificationElement) {
                                notificationElement.remove();
                            }
                        });
                        // After deletion, re-evaluate if "No new notifications" should be shown
                        if (ulElement && ulElement.children.length === 0) {
                            const parentDiv = ulElement.closest('div.bg-white');
                            if (parentDiv) {
                                parentDiv.innerHTML = '\n                <h2 class="text-lg font-semibold text-gray-700 mb-4">General Notifications</h2>\n                    <p class="text-gray-600">No new notifications at this time.</p>\n                ';
                            }
                        }
                        selectAllCheckbox.checked = false; // Uncheck select all after bulk delete
                        updateDeleteSelectedButtonState();
                    } else if (data.error) {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error during bulk deletion:', error);
                    alert('An error occurred during bulk deletion.');
                });
            });
        }
    });

    function markNotificationAsReadAndRedirect(notificationId, redirectUrl) {
        // Mark the notification as read (e.g., update its status in the database)
        // This is a placeholder. In a real application, you would send an AJAX request
        // to update the notification's 'is_read' status.
        // For now, we'll just redirect.

        // Example AJAX call (if you have a backend endpoint for marking as read)
        // fetch(`/api/notifications/${notificationId}/read`, {
        //     method: 'POST',
        //     headers: {
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        //         'Content-Type': 'application/json',
        //         'Accept': 'application/json',
        //     },
        //     body: JSON.stringify({ _method: 'PUT' })
        // })
        // .then(response => response.json())
        // .then(data => {
        //     if (data.success) {
        //         // Redirect to the desired URL
        //         window.location.href = redirectUrl;
        //     } else {
        //         console.error('Error marking notification as read:', data.error);
        //     }
        // })
        // .catch(error => {
        //     console.error('Error during notification read:', error);
        // });

        // For now, we'll just redirect
        window.location.href = redirectUrl;
    }
</script>
@endpush
