<div id="employeeModal" class="fixed inset-0 bg-gray-900/50 hidden items-center justify-center z-50 p-0 sm:p-2 md:p-4"> {{-- Consistent dark overlay --}}
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-auto sm:p-3 md:p-5 transform transition-all duration-300 scale-100 flex flex-col max-h-[95vh] overflow-y-auto" x-data="{ currentStep: 1, totalSteps: 3 }"> {{-- Sharper modal styling --}}
        <div class="flex justify-between items-center mb-4 border-b pb-2">
