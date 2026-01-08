@extends('layouts.app')

@section('title', 'Employee Organization Chart')

@section('content')
@php
    $today = now()->format('l, F d, Y');
@endphp

<section class="bg-[#f3f5fb] min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 space-y-8">
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">{{ $today }}</p>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">HRIS DASHBOARD</h1>
                </div>
                <div class="bg-gray-100 rounded-full p-1 flex self-start lg:self-auto">
                    <a href="{{ route('employee.dashboard') }}" class="px-4 py-2 text-sm font-semibold text-gray-500 rounded-full hover:bg-gray-200 transition">Graph</a>
                    <span class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-full shadow">Org</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">Organizational Chart</h2>
            </div>

            <div class="bg-[#f0f1f6] px-4 py-8">
                <div class="w-full max-w-6xl mx-auto">
                    @if ($ceoNode)
                        @include('employee.organization.partials.node', ['node' => $ceoNode, 'departments' => $departments])
                    @else
                        <p class="text-center text-gray-500">No organizational chart nodes found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
