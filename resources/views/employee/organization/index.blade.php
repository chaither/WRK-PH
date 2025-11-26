@extends('layouts.app')

@section('title', 'Employee Organization Chart')

@section('content')
<section class="py-16 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Our Organization</h1>

        <!-- Organizational Chart Content -->
        <div class="space-y-12 w-full max-w-6xl mx-auto py-8">
            @if ($ceoNode)
                @include('employee.organization.partials.node', ['node' => $ceoNode])
            @else
                <p class="text-center text-gray-500">No organizational chart nodes found.</p>
            @endif
        </div>
    </div>
</section>
@endsection
