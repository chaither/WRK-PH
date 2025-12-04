@extends('layouts.app')

@section('title', 'Employee Notification History')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 p-6">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#0B1432]">Employee Notification History</h1>
            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-medium hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        @if($notifications->count() > 0)
            <ul class="space-y-4">
                @foreach($notifications as $notification)
                    <li class="bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-4 rounded-md flex justify-between items-start">
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
                        @if(!$notification->read_at)
                            <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="mark-as-read-form ml-4">
                                @csrf
                                <button type="submit" class="text-green-500 hover:text-green-700 text-sm focus:outline-none">
                                    Mark as Read
                                </button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-600 text-center">No notification history available.</p>
        @endif

    </div>
</div>

<script>
    function markNotificationAsReadAndRedirect(notificationId, redirectUrl) {
        fetch('/notifications/' + notificationId + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            console.log(data.message);
            window.location.href = redirectUrl;
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
            window.location.href = redirectUrl; // Redirect even if marking as read fails
        });
    }

    document.querySelectorAll('.mark-as-read-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const actionUrl = form.getAttribute('action');

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    form.closest('li').querySelector('p.font-semibold').classList.remove('text-blue-800');
                    form.closest('li').querySelector('p.font-semibold').classList.add('text-gray-600');
                    form.remove(); // Remove the "Mark as Read" button
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
                alert('An error occurred while marking the notification as read.');
            });
        });
    });
</script>
@endsection
