@extends('layouts.app')

@section('title', $employee->name . ' Profile')

@section('content')
<div class="container mx-auto px-4 py-6">
    <header class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-user-circle mr-3 text-indigo-600"></i>{{ $employee->name }}'s Profile
        </h1>
        <a href="javascript:history.back()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </header>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Employee Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-gray-600"><strong>Name:</strong> {{ $employee->name }}</p>
                <p class="text-gray-600"><strong>Email:</strong> {{ $employee->email }}</p>
                <p class="text-gray-600"><strong>Position:</strong> {{ $employee->position ?? 'N/A' }}</p>
                <p class="text-gray-600"><strong>Department:</strong> {{ $employee->department->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600"><strong>Role:</strong> {{ ucfirst($employee->role) }}</p>
                <p class="text-gray-600"><strong>Hired Date:</strong> {{ $employee->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        @include('components.request-card', [
            'title' => 'Daily Time Records',
            'count' => $dtrRecords->count(),
            'latest' => $dtrRecords->first(),
            'emptyMessage' => 'No DTR records found.',
            'icon' => 'fas fa-clock',
            'route' => route('employee.dtr.history', $employee->id) // Assuming a route for DTR history
        ])

        @include('components.request-card', [
            'title' => 'Overtime Requests',
            'count' => $overtimeRequests->count(),
            'latest' => $overtimeRequests->first(),
            'emptyMessage' => 'No overtime requests found.',
            'icon' => 'fas fa-hourglass-half',
            'route' => route('employee.overtime.history', $employee->id) // Assuming a route for overtime history
        ])

        @include('components.request-card', [
            'title' => 'Leave Requests',
            'count' => $leaveRequests->count(),
            'latest' => $leaveRequests->first(),
            'emptyMessage' => 'No leave requests found.',
            'icon' => 'fas fa-calendar-times',
            'route' => route('employee.leave.history', $employee->id) // Assuming a route for leave history
        ])

        @include('components.request-card', [
            'title' => 'Shift Change Requests',
            'count' => $changeShiftRequests->count(),
            'latest' => $changeShiftRequests->first(),
            'emptyMessage' => 'No shift change requests found.',
            'icon' => 'fas fa-calendar-alt',
            'route' => route('employee.shift.history', $employee->id) // Assuming a route for shift history
        ])

        @include('components.request-card', [
            'title' => 'Rest Day Change Requests',
            'count' => $changeRestdayRequests->count(),
            'latest' => $changeRestdayRequests->first(),
            'emptyMessage' => 'No rest day change requests found.',
            'icon' => 'fas fa-sun',
            'route' => route('employee.restday.history', $employee->id) // Assuming a route for rest day history
        ])

        @include('components.request-card', [
            'title' => 'No Bio Requests',
            'count' => $noBioRequests->count(),
            'latest' => $noBioRequests->first(),
            'emptyMessage' => 'No no-bio requests found.',
            'icon' => 'fas fa-fingerprint',
            'route' => route('employee.nobio.history', $employee->id) // Assuming a route for no-bio history
        ])
    </div>

</div>
@endsection
