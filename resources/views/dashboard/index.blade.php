@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
    /* ── Dashboard Root ─────────────────────────────────────── */
    #dashboard-root {
        min-height: 100vh;
        padding: 1.5rem;
        position: relative;
    }

    /* ── Ambient Blobs ──────────────────────────────────────── */
    .db-blob {
        position: fixed;
        border-radius: 50%;
        filter: blur(80px);
        pointer-events: none;
        will-change: transform;
        animation: blobDrift 20s ease-in-out infinite alternate;
        z-index: 0;
    }
    .db-blob-1 { width:520px; height:520px; background:radial-gradient(circle,rgba(59,130,246,.14) 0%,transparent 70%); top:-120px; left:-80px; animation-duration:22s; }
    .db-blob-2 { width:400px; height:400px; background:radial-gradient(circle,rgba(99,102,241,.11) 0%,transparent 70%); bottom:-60px; right:-60px; animation-duration:18s; animation-delay:-6s; }
    .db-blob-3 { width:300px; height:300px; background:radial-gradient(circle,rgba(96,165,250,.09) 0%,transparent 70%); top:40%; left:40%; animation-duration:25s; animation-delay:-12s; }

    @keyframes blobDrift {
        0%   { transform: translate(0,0) scale(1); }
        50%  { transform: translate(25px,20px) scale(1.04); }
        100% { transform: translate(-15px,35px) scale(0.97); }
    }

    /* ── Section Content above blobs ───────────────────────── */
    .db-content { position: relative; z-index: 1; }

    /* ── Page Header Banner ─────────────────────────────────── */
    .db-page-header {
        background: linear-gradient(135deg, rgba(255,255,255,.07) 0%, rgba(255,255,255,.03) 100%);
        border: 1px solid rgba(255,255,255,.1);
        backdrop-filter: blur(12px);
        border-radius: 1.25rem;
        padding: 1.25rem 1.75rem;
        margin-bottom: 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .db-page-header h1 {
        font-size: 1rem;
        font-weight: 700;
        color: rgba(255,255,255,.95);
        letter-spacing: 0.07em;
        text-transform: uppercase;
        margin: 0;
    }
    .db-page-header .header-sub {
        font-size: 0.7rem;
        color: rgba(147,197,253,.75);
        margin-top: 0.15rem;
        letter-spacing: 0.04em;
    }

    /* Org Chart Button */
    .btn-orgchart {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #1a56c4, #3b82f6);
        color: #fff;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.55rem 1.1rem;
        border-radius: 0.6rem;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(59,130,246,.35);
        transition: transform .2s, box-shadow .2s, filter .2s;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-orgchart:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(59,130,246,.5); filter: brightness(1.06); }

    /* ── Stat Cards ─────────────────────────────────────────── */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    @media (max-width: 1024px) { .stat-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 600px)  { .stat-grid { grid-template-columns: 1fr; } }

    .stat-card {
        border-radius: 1.1rem;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        overflow: hidden;
        transition: transform .25s, box-shadow .25s;
        cursor: default;
    }
    .stat-card:hover { transform: translateY(-4px); }

    .stat-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.12) 0%, rgba(255,255,255,.04) 100%);
        border-radius: inherit;
    }

    .stat-card.blue   { background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%); box-shadow: 0 8px 28px rgba(59,130,246,.35); }
    .stat-card.green  { background: linear-gradient(135deg, #15803d 0%, #22c55e 100%); box-shadow: 0 8px 28px rgba(34,197,94,.3); }
    .stat-card.amber  { background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%); box-shadow: 0 8px 28px rgba(245,158,11,.3); }
    .stat-card.red    { background: linear-gradient(135deg, #991b1b 0%, #ef4444 100%); box-shadow: 0 8px 28px rgba(239,68,68,.3); }

    .stat-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        background: rgba(255,255,255,.18);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.3rem;
        color: #fff;
        position: relative;
        z-index: 1;
    }
    .stat-info { position: relative; z-index: 1; }
    .stat-label { font-size: 0.72rem; color: rgba(255,255,255,.75); font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.2rem; }
    .stat-value { font-size: 2rem; font-weight: 800; color: #fff; line-height: 1; }

    /* ── Department Filter Row ──────────────────────────────── */
    .filter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .filter-row h2 {
        font-size: 1rem;
        font-weight: 700;
        color: rgba(255,255,255,.9);
        letter-spacing: 0.02em;
        margin: 0;
    }
    .dept-select {
        background: rgba(255,255,255,.07);
        border: 1px solid rgba(255,255,255,.15);
        color: rgba(255,255,255,.85);
        font-size: 0.8rem;
        padding: 0.5rem 0.9rem;
        border-radius: 0.6rem;
        outline: none;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        backdrop-filter: blur(6px);
    }
    .dept-select:focus { border-color: rgba(96,165,250,.6); background: rgba(255,255,255,.1); }
    .dept-select option { background: #0b2059; color: #fff; }

    /* ── Chart Cards ────────────────────────────────────────── */
    .chart-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }
    @media (max-width: 900px) { .chart-grid { grid-template-columns: 1fr; } }

    .glass-card {
        background: linear-gradient(135deg, rgba(255,255,255,.08) 0%, rgba(255,255,255,.04) 100%);
        border: 1px solid rgba(255,255,255,.1);
        backdrop-filter: blur(14px);
        border-radius: 1.25rem;
        padding: 1.5rem;
        transition: border-color .3s, box-shadow .3s, transform .3s;
    }
    .glass-card:hover {
        border-color: rgba(96,165,250,.3);
        box-shadow: 0 8px 32px rgba(59,130,246,.15);
        transform: translateY(-3px);
    }

    .card-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.9rem;
        font-weight: 700;
        color: rgba(255,255,255,.9);
        margin-bottom: 1.25rem;
        letter-spacing: 0.01em;
    }
    .card-title-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    .icon-indigo { background: linear-gradient(135deg,#4f46e5,#818cf8); color:#fff; }
    .icon-cyan   { background: linear-gradient(135deg,#0891b2,#22d3ee); color:#fff; }

    /* Chart height */
    .chart-wrapper { height: 280px; }
    .chart-wrapper canvas { width: 100% !important; height: 100% !important; }
</style>

{{-- Ambient blobs --}}
<div class="db-blob db-blob-1" aria-hidden="true"></div>
<div class="db-blob db-blob-2" aria-hidden="true"></div>
<div class="db-blob db-blob-3" aria-hidden="true"></div>

<div id="dashboard-root" class="db-content" data-role="{{ auth()->user()->role ?? '' }}">

    {{-- ── Page Header ── --}}
    <div class="db-page-header">
        <div>
            <h1>Human Resources Information System</h1>
            <div class="header-sub">WRK Services PH &mdash; {{ now()->format('l, F j, Y') }}</div>
        </div>
        <a id="dashboard-orgchart-btn" href="{{ route('organization.index') }}" class="btn-orgchart" aria-label="Open organization chart">
            <i class="fas fa-sitemap" aria-hidden="true"></i>
            <span>Org Chart</span>
        </a>
    </div>

    @if(auth()->user()->role !== 'employee')

    {{-- ── Stat Cards ── --}}
    <div class="stat-grid" id="dailyUpdateMetrics">

        {{-- Department Filter (hidden, still drives AJAX) --}}
        <form id="departmentFilterForm" action="{{ route('dashboard') }}" method="GET" style="display:none;">
            <select name="department_id" id="departmentFilter">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ (string)$department->id === (string)$selectedDepartmentId ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="stat-card blue">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total Employees</div>
                <div class="stat-value" id="totalEmployees">{{ $employeeCount ?? 0 }}</div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Present Today</div>
                <div class="stat-value" id="presentToday">{{ $presentToday ?? 0 }}</div>
            </div>
        </div>

        <div class="stat-card amber">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-label">Late Today</div>
                <div class="stat-value" id="lateToday">{{ $lateToday ?? 0 }}</div>
            </div>
        </div>

        <div class="stat-card red">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Absent Today</div>
                <div class="stat-value" id="absentToday">{{ $absentToday ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- ── Daily Update Label + Dept Filter ── --}}
    <div class="filter-row">
        <h2><i class="fas fa-chart-line" style="margin-right:.5rem;color:#60a5fa;"></i>Analytics Overview</h2>
        <form id="departmentFilterFormVisible" action="{{ route('dashboard') }}" method="GET">
            <select name="department_id" id="departmentFilterVisible" class="dept-select">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ (string)$department->id === (string)$selectedDepartmentId ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- ── Charts ── --}}
    <div class="chart-grid">

        {{-- Payroll Chart --}}
        <div class="glass-card">
            <div class="card-title">
                <div class="card-title-icon icon-indigo"><i class="fas fa-money-bill-transfer"></i></div>
                Monthly Payroll &amp; Deductions
            </div>
            <div class="chart-wrapper">
                <div tabindex="0" role="group" aria-label="Monthly Payroll and Deductions chart">
                    <canvas id="monthlyCombinedChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Attendance Chart --}}
        <div class="glass-card">
            <div class="card-title">
                <div class="card-title-icon icon-cyan"><i class="fas fa-users-line"></i></div>
                Employee Attendance Summary
            </div>
            <div class="chart-wrapper">
                <div tabindex="0" role="group" aria-label="Employee Attendance Summary chart">
                    <canvas id="monthlyAttendanceChart"></canvas>
                </div>
            </div>
        </div>

    </div>
    @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Shared chart defaults ──────────────────────────────
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: 'rgba(255,255,255,0.75)',
                    font: { size: 11, weight: '500' },
                    boxWidth: 12,
                    padding: 16,
                }
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.06)', drawBorder: false },
                ticks: { color: 'rgba(255,255,255,0.5)', maxRotation: 45, minRotation: 45, font: { size: 10 } }
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,0.06)', drawBorder: false },
                ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } }
            }
        }
    };

    // ── Payroll Chart ──────────────────────────────────────
    const combinedCtx = document.getElementById('monthlyCombinedChart');
    if (combinedCtx) {
        new Chart(combinedCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($combinedLabels) !!},
                datasets: [
                    {
                        label: 'Net Pay',
                        data: {!! json_encode($payrollData) !!},
                        backgroundColor: 'rgba(99,102,241,0.2)',
                        borderColor: 'rgba(129,140,248,1)',
                        borderWidth: 2, fill: true, tension: 0.4,
                        pointBackgroundColor: 'rgba(129,140,248,1)',
                        pointBorderColor: '#fff', pointBorderWidth: 1,
                        pointRadius: 4, pointHoverRadius: 6
                    },
                    {
                        label: 'Total Deductions',
                        data: {!! json_encode($deductionData) !!},
                        backgroundColor: 'rgba(239,68,68,0.15)',
                        borderColor: 'rgba(248,113,113,1)',
                        borderWidth: 2, fill: true, tension: 0.4,
                        pointBackgroundColor: 'rgba(248,113,113,1)',
                        pointBorderColor: '#fff', pointBorderWidth: 1,
                        pointRadius: 4, pointHoverRadius: 6
                    }
                ]
            },
            options: chartDefaults
        });
    }

    // ── Attendance Chart ───────────────────────────────────
    const attendanceCtx = document.getElementById('monthlyAttendanceChart');
    if (attendanceCtx) {
        new Chart(attendanceCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($attendanceLabels) !!},
                datasets: [
                    {
                        label: 'Present',
                        data: {!! json_encode($presentData) !!},
                        backgroundColor: 'rgba(59,130,246,0.2)',
                        borderColor: 'rgba(96,165,250,1)',
                        borderWidth: 2, fill: true, tension: 0.4,
                        pointBackgroundColor: 'rgba(96,165,250,1)',
                        pointBorderColor: '#fff', pointBorderWidth: 1,
                        pointRadius: 4, pointHoverRadius: 6
                    },
                    {
                        label: 'Late',
                        data: {!! json_encode($lateData) !!},
                        backgroundColor: 'rgba(245,158,11,0.2)',
                        borderColor: 'rgba(251,191,36,1)',
                        borderWidth: 2, fill: true, tension: 0.4,
                        pointBackgroundColor: 'rgba(251,191,36,1)',
                        pointBorderColor: '#fff', pointBorderWidth: 1,
                        pointRadius: 4, pointHoverRadius: 6
                    },
                    {
                        label: 'Absent',
                        data: {!! json_encode($absentData) !!},
                        backgroundColor: 'rgba(239,68,68,0.15)',
                        borderColor: 'rgba(248,113,113,1)',
                        borderWidth: 2, fill: true, tension: 0.4,
                        pointBackgroundColor: 'rgba(248,113,113,1)',
                        pointBorderColor: '#fff', pointBorderWidth: 1,
                        pointRadius: 4, pointHoverRadius: 6
                    }
                ]
            },
            options: chartDefaults
        });
    }

    // ── Department filter (visible select syncs the hidden one + AJAX) ──
    const visibleFilter = document.getElementById('departmentFilterVisible');
    const hiddenFilter  = document.getElementById('departmentFilter');

    if (visibleFilter) {
        visibleFilter.addEventListener('change', async function () {
            if (hiddenFilter) hiddenFilter.value = this.value;
            try {
                const res  = await fetch(`/dashboard?department_id=${this.value}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                document.getElementById('totalEmployees').textContent = data.employeeCount;
                document.getElementById('presentToday').textContent   = data.presentToday;
                document.getElementById('lateToday').textContent      = data.lateToday;
                document.getElementById('absentToday').textContent    = data.absentToday;
            } catch (e) {
                console.error('Dashboard filter error:', e);
            }
        });
    }

});
</script>
@endpush
@endsection
