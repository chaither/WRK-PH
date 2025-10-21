@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Submit Leave Request</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('employee.leave.store') }}" method="POST" class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        @csrf
        <div class="mb-4">
            <label for="start_date" class="block text-gray-700 text-sm font-bold mb-2">Start Date:</label>
            <input type="date" name="start_date" id="start_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('start_date') }}" required>
        </div>
        <div class="mb-4">
            <label for="end_date" class="block text-gray-700 text-sm font-bold mb-2">End Date:</label>
            <input type="date" name="end_date" id="end_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('end_date') }}" required>
        </div>
        <div class="mb-6">
            <label for="reason" class="block text-gray-700 text-sm font-bold mb-2">Reason:</label>
            <textarea name="reason" id="reason" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>{{ old('reason') }}</textarea>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Submit Request
            </button>
            <a href="{{ route('employee.leave.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
