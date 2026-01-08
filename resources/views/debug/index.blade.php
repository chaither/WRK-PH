@extends('layouts.app')

@section('content')
<div class="container mx-auto px-6 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Debug Tools</h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">System Commands</h2>
        
        @if(session('output'))
            <div class="bg-gray-900 text-green-400 font-mono p-4 rounded-md mb-4 whitespace-pre-wrap overflow-x-auto text-sm">
{{ session('output') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            
            {{-- Safe Commands --}}
            <form action="{{ route('debug.run') }}" method="POST">
                @csrf
                <input type="hidden" name="command" value="optimize:clear">
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-150">
                    Clear All Caches (optimize:clear)
                </button>
            </form>

            <form action="{{ route('debug.run') }}" method="POST">
                @csrf
                <input type="hidden" name="command" value="cache:clear">
                <button type="submit" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded transition duration-150">
                    Clear App Cache
                </button>
            </form>

            <form action="{{ route('debug.run') }}" method="POST">
                @csrf
                <input type="hidden" name="command" value="route:clear">
                <button type="submit" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded transition duration-150">
                    Clear Route Cache
                </button>
            </form>

            <form action="{{ route('debug.run') }}" method="POST">
                @csrf
                <input type="hidden" name="command" value="config:clear">
                <button type="submit" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded transition duration-150">
                    Clear Config Cache
                </button>
            </form>

             <form action="{{ route('debug.run') }}" method="POST">
                @csrf
                <input type="hidden" name="command" value="view:clear">
                <button type="submit" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded transition duration-150">
                    Clear View Cache
                </button>
            </form>

            <form action="{{ route('debug.run') }}" method="POST">
                @csrf
                <input type="hidden" name="command" value="migrate">
                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-150">
                    Run Migrations (migrate)
                </button>
            </form>

        </div>
    </div>

    <div class="bg-red-50 rounded-lg shadow-md p-6 border border-red-200">
        <h2 class="text-xl font-bold mb-4 text-red-700"><i class="fas fa-exclamation-triangle mr-2"></i> Danger Zone</h2>
        
        <form action="{{ route('debug.run') }}" method="POST" onsubmit="return confirm('WARNING: THIS WILL DELETE ALL DATA. ARE YOU ABSOLUTELY SURE?');">
            @csrf
            <input type="hidden" name="command" value="migrate:fresh --seed">
            
            <div class="mb-4">
                <label class="block text-red-800 text-sm font-bold mb-2" for="confirmation">
                    Type "I UNDERSTAND THIS WIPES ALL DATA" to confirm:
                </label>
                <input class="shadow appearance-none border border-red-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" 
                    id="confirmation" type="text" name="confirmation" placeholder="Type confirmation phrase here" required>
            </div>

            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full transition duration-150">
                ⚠️ WIPE DATABASE & SEED (migrate:fresh --seed)
            </button>
        </form>
    </div>
</div>
@endsection
