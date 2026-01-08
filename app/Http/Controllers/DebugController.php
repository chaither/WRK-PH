<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    public function index()
    {
        return view('debug.index');
    }

    public function runCommand(Request $request)
    {
        $command = $request->input('command');

        if (!in_array($command, [
            'cache:clear', 
            'route:clear', 
            'config:clear', 
            'view:clear', 
            'optimize:clear',
            'migrate',
            'migrate:fresh --seed'
        ])) {
            return redirect()->back()->with('error', 'Invalid command.');
        }

        // Extra safety check for destructive commands
        if ($command === 'migrate:fresh --seed') {
            if ($request->input('confirmation') !== 'I UNDERSTAND THIS WIPES ALL DATA') {
                return redirect()->back()->with('error', 'Incorrect confirmation for destructive command.');
            }
        }

        try {
            // Capture output
            // Add --force flag for production environment
            if (in_array($command, ['migrate', 'migrate:fresh --seed'])) {
                if ($command === 'migrate:fresh --seed') {
                     Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
                } else {
                     Artisan::call($command, ['--force' => true]);
                }
            } else {
                Artisan::call($command);
            }
            $output = Artisan::output();
            
            Log::info("Debug command executed: $command", ['user_id' => auth()->id()]);

            return redirect()->back()->with('success', "Command '$command' executed successfully.")->with('output', $output);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Failed to execute '$command': " . $e->getMessage());
        }
    }
    public function updateBiometricConfig(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer',
        ]);

        $ip = $request->input('ip');
        $port = $request->input('port');

        \Illuminate\Support\Facades\Cache::put('zkteco_override_ip', $ip); // Store indefinitely
        \Illuminate\Support\Facades\Cache::put('zkteco_override_port', $port); // Store indefinitely

        return redirect()->back()->with('success', "Biometric configuration updated. Using IP: $ip, Port: $port");
    }
}
