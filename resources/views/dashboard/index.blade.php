@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Parallax Background -->
<div class="fixed inset-0 -z-10 overflow-hidden">
    <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: -3s;"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl animate-bounce"></div>
</div>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative">
    <!-- Header Section -->
    <div class="bg-white/80 backdrop-blur-sm shadow-lg border-b border-gray-200/50 p-6 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    HRIS Dashboard
                </h1>

                <p class="text-gray-600 mt-2">Period: June 2025 - May 2026</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button id="profileDropdownToggle" class="rounded-full p-3 focus:outline-none">
                        <i class="fas fa-user-circle text-3xl text-blue-600"></i>
                    </button>
                    <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 hidden">
                        <a href="{{ route('password.request') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-key mr-2"></i>Change Password</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i>Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    @if(auth()->user()->role !== 'employee')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
        
        <!-- Left Column -->
        <div class="space-y-8">
            <!-- Attendance Type Chart -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 hover:shadow-2xl transition-all duration-500 hover:scale-105">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-chart-bar text-blue-600 mr-3"></i>
                    Attendance Type
                </h3>
                <div class="h-80">
                    <canvas id="attendanceTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-8">
            <!-- Leave Reasons Chart -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 hover:shadow-2xl transition-all duration-500 hover:scale-105">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-chart-bar text-purple-600 mr-3"></i>
                    Leave Reasons
                </h3>
                <div class="h-80">
                    <canvas id="terminationReasonChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Departments Section -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 mx-6 mb-8 hover:shadow-2xl transition-all duration-500">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Top 10 Departments</h2>
            <div class="flex items-center space-x-4">
                <select class="bg-white/60 backdrop-blur-sm border border-gray-300 rounded-lg px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option>Department Name</option>
                    <option>IT Department</option>
                    <option>HR Department</option>
                    <option>Finance Department</option>
                </select>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-blue-600 rounded-xl p-6 text-white hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Total Employees</p>
                        <p class="text-3xl font-bold">{{ $employeeCount ?? 0 }}</p>
                    </div>
                    <i class="fas fa-users text-4xl text-blue-200"></i>
                </div>
            </div>
            
            <div class="bg-green-600 rounded-xl p-6 text-white hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Present Today</p>
                        <p class="text-3xl font-bold">{{ $presentToday ?? 0 }}</p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-green-200"></i>
                </div>
            </div>
            
            <div class="bg-yellow-600 rounded-xl p-6 text-white hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm">Late Today</p>
                        <p class="text-3xl font-bold">{{ $lateToday ?? 0 }}</p>
                    </div>
                    <i class="fas fa-clock text-4xl text-yellow-200"></i>
                </div>
            </div>
            
            <div class="bg-red-600 rounded-xl p-6 text-white hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm">Absent Today</p>
                        <p class="text-3xl font-bold">{{ $absentToday ?? 0 }}</p>
                    </div>
                    <i class="fas fa-times-circle text-4xl text-red-200"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('profileDropdownToggle').addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event from bubbling up to window click listener
        document.getElementById('profileDropdown').classList.toggle('hidden');
    });

    // Close the dropdown if the user clicks outside of it
    window.addEventListener('click', function(event) {
        if (!event.target.matches('#profileDropdownToggle') && !event.target.closest('#profileDropdown')) {
            var dropdown = document.getElementById('profileDropdown');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        }
    });

    const labels = ['Jun','Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar','Apr','May'];

    // Attendance Type Chart
    const attendanceCtx = document.getElementById('attendanceTypeChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Present', data: [100,100,92,89,94,0,0,0,0,0,0,0], backgroundColor:'rgba(59,130,246,0.8)', borderColor:'rgba(59,130,246,1)', borderWidth:2 },
                { label: 'Late', data: [,7,5,7,4,0,0,0,0,0,0,0], backgroundColor:'rgba(245,158,11,0.8)', borderColor:'rgba(245,158,11,1)', borderWidth:2 },
                { label: 'Absent', data: [0,0,3,4,2,0,0,0,0,0,0,0], backgroundColor:'rgba(239,68,68,0.8)', borderColor:'rgba(239,68,68,1)', borderWidth:2 }
            ]
        },
        options: {
            responsive:true,
            maintainAspectRatio:false,
            plugins:{ legend:{ position:'top', labels:{ color:'#374151', font:{ size:12, weight:'500' } } } },
            scales:{ 
                x:{ grid:{ display:false }, ticks:{ color:'#6B7280' } }, 
                y:{ beginAtZero:true, grid:{ color:'#E5E7EB' }, ticks:{ color:'#6B7280' } } 
            },
            animation:{ duration:2000, easing:'easeInOutQuart' }
        }
    });

    // Leave Reasons Chart (0–20 rating scale)
    const leaveCtx = document.getElementById('terminationReasonChart').getContext('2d');
    new Chart(leaveCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Personal', data: [0,0,5,2,3,0,0,0,0,0,0,0], backgroundColor:'rgba(139,92,246,0.8)', borderColor:'rgba(139,92,246,1)', borderWidth:2 },
                { label: 'School', data: [0,0,7,10,2,0,0,0,0,0,0,0], backgroundColor:'rgba(59,130,246,0.8)', borderColor:'rgba(59,130,246,1)', borderWidth:2 },
                { label: 'Sick Leave', data: [0,0,20,8,2,0,0,0,0,0,0,0], backgroundColor:'rgba(16,185,129,0.8)', borderColor:'rgba(16,185,129,1)', borderWidth:2 },
                { label: 'Natural disasters', data: [0,0,0,7,15,0,0,0,0,0,0,0], backgroundColor:'rgba(245,158,11,0.8)', borderColor:'rgba(245,158,11,1)', borderWidth:2 }
            ]   
        },
        options: {
            responsive:true,
            maintainAspectRatio:false,
            plugins:{ legend:{ position:'top', labels:{ color:'#374151', font:{ size:12, weight:'500' } } } },
            scales:{ 
                x:{ grid:{ display:false }, ticks:{ color:'#6B7280' } }, 
                y:{ min:0, max:24, beginAtZero:true, grid:{ color:'#E5E7EB' }, ticks:{ color:'#6B7280' } } 
            },
            animation:{ duration:2000, easing:'easeInOutQuart' }
        }
    });

});
</script>
@endpush
@endsection
