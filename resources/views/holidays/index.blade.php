@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Holiday Management</h1>

    <div class="flex justify-end mb-4">
        <button onclick="openAddHolidayModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition duration-150">
            <i class="fas fa-plus mr-2"></i> Add New Holiday
        </button>
    </div>

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
                        Date
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">
                        Type
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Rate Multiplier
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($holidays as $holiday)
                <tr x-data="{ open: false }" class="mobile-accordion hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <button @click="open = !open" class="flex justify-between items-center w-full focus:outline-none sm:cursor-default py-2">
                            <div>
                                <div class="font-medium text-gray-900">{{ $holiday->date->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $holiday->name }}</div>
                            </div>
                            <svg x-show="!open" class="w-4 h-4 sm:hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <svg x-show="open" class="w-4 h-4 sm:hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm hidden sm:table-cell">
                        {{ $holiday->name }}
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm hidden sm:table-cell">
                        {{ ucfirst(str_replace('_', ' ', $holiday->type)) }}
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm hidden sm:table-cell">
                        {{ $holiday->rate_multiplier }}x
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm hidden sm:table-cell">
                        <button onclick="openEditHolidayModal({{ $holiday->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                        <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this holiday?');" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                        No holidays found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('modals')
    @include('components.holiday_modal')
@endpush
