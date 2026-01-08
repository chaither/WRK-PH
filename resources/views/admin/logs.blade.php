@extends('layouts.app')

@section('title', 'Application Logs')

@section('content')
<div class="container mx-auto px-6 py-6">
    <div class="bg-white rounded-lg shadow-xl p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-file-alt mr-3 text-indigo-600"></i> Application Logs
        </h1>
        
        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Log File Location:</strong> {{ $logFile }}
            </p>
        </div>

        <div class="mb-6">
            <div class="flex gap-4 mb-4">
                <button onclick="showAllLogs()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Show All Recent Logs (200 lines)
                </button>
                <button onclick="showErrorLogs()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Show Error Logs Only
                </button>
                <a href="{{ route('admin.logs') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Refresh
                </a>
            </div>
        </div>

        <div id="allLogs" class="hidden">
            <h2 class="text-xl font-semibold mb-3 text-gray-700">All Recent Logs (Last 200 lines)</h2>
            <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-auto max-h-96 font-mono text-sm">
                @foreach($allLogs as $line)
                    <div class="{{ stripos($line, 'error') !== false || stripos($line, 'exception') !== false ? 'text-red-400' : '' }}">
                        {{ $line }}
                    </div>
                @endforeach
            </div>
        </div>

        <div id="errorLogs">
            <h2 class="text-xl font-semibold mb-3 text-gray-700">Error Logs (Filtered)</h2>
            <div class="bg-gray-900 text-red-400 p-4 rounded-lg overflow-auto max-h-96 font-mono text-sm">
                @if(count($errorLogs) > 0)
                    @foreach($errorLogs as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                @else
                    <div class="text-green-400">No errors found in recent logs.</div>
                @endif
            </div>
        </div>

        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Note:</strong> This page shows the last 200 lines of the log file. For full logs, access the server via SSH and use: <code class="bg-gray-200 px-2 py-1 rounded">tail -f storage/logs/laravel.log</code>
            </p>
        </div>
    </div>
</div>

<script>
function showAllLogs() {
    document.getElementById('allLogs').classList.remove('hidden');
    document.getElementById('errorLogs').classList.add('hidden');
}

function showErrorLogs() {
    document.getElementById('allLogs').classList.add('hidden');
    document.getElementById('errorLogs').classList.remove('hidden');
}
</script>
@endsection
