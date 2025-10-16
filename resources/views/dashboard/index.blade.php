@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @if(auth()->user()->role === 'admin')
        <div class="bg-blue-100 p-6 rounded-lg">
            <h3 class="text-xl font-semibold text-blue-800">Total Employees</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $employeeCount ?? 0 }}</p>
        </div>
        @endif

        @if(in_array(auth()->user()->role, ['admin', 'hr']))
        <div class="bg-green-100 p-6 rounded-lg">
            <h3 class="text-xl font-semibold text-green-800">Present Today</h3>
            <p class="text-3xl font-bold text-green-600">{{ $presentToday ?? 0 }}</p>
        </div>

        <div class="bg-red-100 p-6 rounded-lg">
            <h3 class="text-xl font-semibold text-red-800">Absent Today</h3>
            <p class="text-3xl font-bold text-red-600">{{ $absentToday ?? 0 }}</p>
        </div>
        @endif
    </div>
</div>
@endsection