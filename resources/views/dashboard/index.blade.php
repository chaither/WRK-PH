@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Parallax Background -->
<div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none" aria-hidden="true">
    <div class="parallax-layer absolute -top-40 -right-40 w-80 h-80 bg-blue-500/12 rounded-full blur-3xl" data-speed="0.20"></div>
    <div class="parallax-layer absolute -bottom-44 -left-44 w-96 h-96 bg-purple-500/12 rounded-full blur-3xl" data-speed="0.12"></div>
    <div class="parallax-layer absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" data-speed="0.06">
        <div class="parallax-inner w-96 h-96 bg-cyan-500/6 rounded-full blur-3xl"></div>
    </div>
    <div class="parallax-layer absolute top-8 left-1/4 w-64 h-64 bg-indigo-400/6 rounded-full blur-2xl" data-speed="0.28"></div>
    <div class="parallax-layer absolute bottom-20 right-1/3 w-44 h-44 bg-rose-400/6 rounded-full blur-2xl" data-speed="0.16"></div>
</div>

<style>
/* Parallax: keep transforms on GPU and subtle */
.parallax-layer, .parallax-inner { transform: translate3d(0,0,0); will-change: transform; }

/* Chart float animation */
.chart-float { animation: floatY 6s ease-in-out infinite; will-change: transform; }
.chart-float.paused { animation-play-state: paused !important; }
@keyframes floatY { 0% { transform: translateY(-6px);} 50% { transform: translateY(6px);} 100% { transform: translateY(-6px);} }

/* Make sure canvas fills wrapper */
.chart-wrapper { height: 24rem; }
.chart-wrapper canvas { width: 100% !important; height: 100% !important; }

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
    .chart-float { animation: none; }
}
</style>

<div id="dashboard-root" class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative" data-role="{{ auth()->user()->role ?? '' }}">
    <!-- Header Section -->
    <div class="bg-white/80 backdrop-blur-sm shadow-lg border-b border-gray-200/50 p-6 mb-8">
        <div class="flex flex-wrap justify-between items-center">
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-3">
                    <h1 class="text-4xl font-bold text-[#0B1432] truncate">
                        LIMEHILLS HRIS
                    </h1>
                </div>
                <p class="text-gray-600 mt-2">Period: June 2025 - May 2026</p>
            </div>
            <div class="flex items-center space-x-4">
                <button id="dashboard-orgchart-btn" type="button" aria-label="Open organization chart" title="Organization Chart" class="inline-flex items-center bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300" onclick="window.location='{{ route('organization.index') }}'">
                    <i class="fas fa-sitemap mr-2" aria-hidden="true"></i>
                    <span class="hidden sm:inline">Org Chart</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    @if(auth()->user()->role !== 'employee')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8 p-6">
        
        <!-- Left Column -->
        <div class="space-y-8">
            <!-- Attendance Type Chart -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 hover:shadow-2xl transition-all duration-500 hover:scale-105">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-chart-bar text-blue-600 mr-3"></i>
                    Attendance Type
                </h3>
                <div class="chart-wrapper">
                    <div class="chart-float" tabindex="0" role="group" aria-label="Attendance Type chart wrapper">
                        <canvas id="attendanceTypeChart"></canvas>
                    </div>
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
                <div class="chart-wrapper">
                    <div class="chart-float" tabindex="0" role="group" aria-label="Leave Reasons chart wrapper">
                        <canvas id="terminationReasonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Departments Section -->
    <div class="container mx-auto px-4 bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 mx-6 mb-8 hover:shadow-2xl transition-all duration-500">
        <div class="flex flex-wrap justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Daily Update</h2>
            <div class="flex items-center space-x-4">
                <form id="departmentFilterForm" action="{{ route('dashboard') }}" method="GET">
                    <select name="department_id" id="departmentFilter" class="bg-white/60 backdrop-blur-sm border border-gray-300 rounded-lg px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ (string)$department->id === (string)$selectedDepartmentId ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6" id="dailyUpdateMetrics">
            <div class="bg-blue-600 rounded-xl p-6 text-white hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Total Employees</p>
                        <p class="text-3xl font-bold" id="totalEmployees">{{ $employeeCount ?? 0 }}</p>
                    </div>
                    <i class="fas fa-users text-4xl text-blue-200"></i>
                </div>
            </div>
            
            <div class="bg-green-600 rounded-xl p-6 text-white hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Present Today</p>
                        <p class="text-3xl font-bold" id="presentToday">{{ $presentToday ?? 0 }}</p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-green-200"></i>
                </div>
            </div>
            
            <div class="bg-yellow-600 rounded-xl p-6 text-white hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm">Late Today</p>
                        <p class="text-3xl font-bold" id="lateToday">{{ $lateToday ?? 0 }}</p>
                    </div>
                    <i class="fas fa-clock text-4xl text-yellow-200"></i>
                </div>
            </div>
            
            <div class="bg-red-600 rounded-xl p-6 text-white hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm">Absent Today</p>
                        <p class="text-3xl font-bold" id="absentToday">{{ $absentToday ?? 0 }}</p>
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
    // The profile dropdown toggle and close logic is now handled in layouts/app.blade.php

    const labels = ['Jun','Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar','Apr','May'];

    // Attendance Type Chart
        const attendanceCtx = document.getElementById('attendanceTypeChart').getContext('2d');
        const attendanceChart = new Chart(attendanceCtx, {
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
        const leaveChart = new Chart(leaveCtx, {
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

        // Parallax effect for background layers
        (function(){
            const layers = document.querySelectorAll('.parallax-layer, .parallax-inner');
            if(!layers.length) return;
            let latestScroll = window.scrollY;
            let ticking = false;
            function onScroll(){ latestScroll = window.scrollY; if(!ticking){ window.requestAnimationFrame(update); ticking = true; } }
            function update(){
                layers.forEach(layer => {
                    const parent = layer.classList.contains('parallax-inner') ? layer.parentElement : layer;
                    const speed = parseFloat(parent && parent.dataset && parent.dataset.speed) || parseFloat(layer.dataset && layer.dataset.speed) || 0.08;
                    const y = latestScroll * speed;
                    layer.style.transform = `translate3d(0, ${y}px, 0)`;
                    layer.style.willChange = 'transform';
                });
                ticking = false;
            }
            window.addEventListener('scroll', onScroll, { passive: true });
            // initial position
            update();
        })();

        // Gentle moving animation for charts (sinusoidal offsets) + pause on admin hover/focus
        (function(){
            const dashboardRoot = document.getElementById('dashboard-root');
            const role = (dashboardRoot?.dataset?.role || '').toLowerCase();
            const isAdmin = role.includes('admin');

            const wrappers = document.querySelectorAll('.chart-float');
            wrappers.forEach(w => {
                // allow pointer interaction to pause briefly for everyone
                w.addEventListener('pointerdown', ()=> w.classList.add('paused'));
                w.addEventListener('pointerup', ()=> w.classList.remove('paused'));
            });

            // Pause/resume only for admin on hover/focus
            if(isAdmin){
                wrappers.forEach(w => {
                    w.addEventListener('mouseenter', ()=> w.classList.add('paused'));
                    w.addEventListener('mouseleave', ()=> w.classList.remove('paused'));
                    w.addEventListener('focus', ()=> w.classList.add('paused'));
                    w.addEventListener('blur', ()=> w.classList.remove('paused'));
                });
            }

            // helper: animate a chart by applying small sine offsets to its data
            function animateChart(chart, baselineDatasets, amplitudeFactor = 0.05, speed = 1.0){
                let last = performance.now();
                let rafId;
                function step(now){
                    const dt = now - last;
                    if(dt > 30){ // throttle to ~30fps
                        const t = now / 1000 * speed;
                        const wrapper = chart.canvas.closest('.chart-float');
                        const paused = wrapper && wrapper.classList.contains('paused');
                        if(!paused){
                            // update each dataset
                            chart.data.datasets.forEach((ds, idx) => {
                                const base = baselineDatasets[idx];
                                if(!base) return;
                                const newData = base.map((val, j) => {
                                    // some datasets may be zero; scale amplitude reasonably
                                    const amp = (Math.max(1, Math.abs(val)) * amplitudeFactor) + 0.5;
                                    const offset = Math.sin(t * (0.6 + idx*0.15) + j*0.3) * amp;
                                    const v = Math.max(0, Math.round((val + offset) * 100) / 100);
                                    return v;
                                });
                                ds.data = newData;
                            });
                            chart.update('none');
                        }
                        last = now;
                    }
                    rafId = requestAnimationFrame(step);
                }
                rafId = requestAnimationFrame(step);
                return ()=> cancelAnimationFrame(rafId);
            }

            // capture baselines and start animators
            try{
                if(typeof attendanceChart !== 'undefined' && attendanceChart){
                    const baselineA = attendanceChart.data.datasets.map(d => Array.from(d.data));
                    animateChart(attendanceChart, baselineA, 0.04, 0.9);
                }
                if(typeof leaveChart !== 'undefined' && leaveChart){
                    const baselineL = leaveChart.data.datasets.map(d => Array.from(d.data));
                    animateChart(leaveChart, baselineL, 0.06, 0.8);
                }
            }catch(e){ console.warn('Chart animation error', e); }
        })();

    const departmentFilter = document.getElementById('departmentFilter');
    departmentFilter.addEventListener('change', async function() {
        const selectedDepartmentId = this.value;
        try {
            const response = await fetch(`/dashboard?department_id=${selectedDepartmentId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            // Update the dashboard metrics
            document.getElementById('totalEmployees').textContent = data.employeeCount;
            document.getElementById('presentToday').textContent = data.presentToday;
            document.getElementById('lateToday').textContent = data.lateToday;
            document.getElementById('absentToday').textContent = data.absentToday;

        } catch (error) {
            console.error('Error fetching dashboard data:', error);
            alert('Failed to load dashboard data.');
        }
    });

});
</script>
@endpush
@endsection
