@extends('layouts.app')

@section('title', 'Create Employee')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Create New Employee</h2>
            <a href="{{ route('employees.index') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('employees.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Information Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4 text-blue-800">Personal Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-gray-700 font-medium mb-2">Full Name</label>
                            <input type="text" name="name" id="name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                value="{{ old('name') }}"
                                placeholder="Enter full name">
                        </div>

                        <div>
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                            <input type="email" name="email" id="email" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                value="{{ old('email') }}"
                                placeholder="Enter email address">
                        </div>

                        <div>
                            <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                            <input type="password" name="password" id="password" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                placeholder="Enter password">
                            <p class="text-sm text-gray-500 mt-1">Minimum 8 characters</p>
                        </div>

                        <div>
                            <label for="position" class="block text-gray-700 font-medium mb-2">Position</label>
                            <input type="text" name="position" id="position" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                value="{{ old('position') }}"
                                placeholder="Enter job position">
                        </div>
                    </div>
                </div>

                <!-- Salary Information Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4 text-blue-800">Salary Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="basic_salary" class="block text-gray-700 font-medium mb-2">Basic Salary</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-600">₱</span>
                                <input type="number" name="basic_salary" id="basic_salary" required
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                    value="{{ old('basic_salary') }}"
                                    step="0.01" min="0"
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div>
                            <label for="pay_period" class="block text-gray-700 font-medium mb-2">Pay Period</label>
                            <select name="pay_period" id="pay_period" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                                <option value="">Select pay period</option>
                                <option value="semi-monthly" {{ old('pay_period') == 'semi-monthly' ? 'selected' : '' }}>
                                    Semi-monthly (15th and 30th)
                                </option>
                                <option value="monthly" {{ old('pay_period') == 'monthly' ? 'selected' : '' }}>
                                    Monthly
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="daily_rate" class="block text-gray-700 font-medium mb-2">Daily Rate</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-600">₱</span>
                                <input type="number" name="daily_rate" id="daily_rate" required
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                    value="{{ old('daily_rate') }}"
                                    step="0.01" min="0"
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div>
                            <label for="hourly_rate" class="block text-gray-700 font-medium mb-2">Hourly Rate</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-600">₱</span>
                                <input type="number" name="hourly_rate" id="hourly_rate" required
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                    value="{{ old('hourly_rate') }}"
                                    step="0.01" min="0"
                                    placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Information Section -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-blue-800">Schedule Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="work_start" class="block text-gray-700 font-medium mb-2">Work Start Time</label>
                        <input type="time" name="work_start" id="work_start" required
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                            value="{{ old('work_start', '09:00') }}">
                    </div>

                    <div>
                        <label for="work_end" class="block text-gray-700 font-medium mb-2">Work End Time</label>
                        <input type="time" name="work_end" id="work_end" required
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                            value="{{ old('work_end', '18:00') }}">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6">
                <button type="reset" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Reset Form
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Employee
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const basicSalaryInput = document.getElementById('basic_salary');
    const dailyRateInput = document.getElementById('daily_rate');
    const hourlyRateInput = document.getElementById('hourly_rate');

    // Disable daily_rate and hourly_rate inputs as they will be auto-calculated
    dailyRateInput.setAttribute('readonly', true);
    hourlyRateInput.setAttribute('readonly', true);

    // Function to calculate rates
    function calculateRates() {
        const basicSalary = parseFloat(basicSalaryInput.value) || 0;
        const dailyRate = basicSalary / 22; // Assuming 22 working days in a month
        const hourlyRate = dailyRate / 8; // Assuming 8 working hours per day

        dailyRateInput.value = dailyRate.toFixed(2);
        hourlyRateInput.value = hourlyRate.toFixed(2);
    }

    // Initial calculation on page load if basic salary is already set (e.g., from old() values)
    calculateRates();

    // Recalculate rates when basic salary changes
    basicSalaryInput.addEventListener('input', calculateRates);

    // Form validation (keeping existing validation, but adjusting for auto-calculated fields)
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        // Basic salary can be 0 or less, if it's the case, the rates will also be 0.
        const basicSalary = parseFloat(basicSalaryInput.value);
        if (basicSalary < 0) {
            e.preventDefault();
            alert('Basic Salary cannot be negative.');
        }
    });
});
</script>
@endpush
@endsection