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
    public function updateBiometricConfig(Request $request, \App\Services\ZktecoService $zktecoService)
    {
        $request->validate([
            'ip' => 'required|string',
            'port' => 'required|integer',
        ]);

        $ip = $request->input('ip');
        $port = $request->input('port');

        \Illuminate\Support\Facades\Cache::put('zkteco_override_ip', $ip); // Store indefinitely
        \Illuminate\Support\Facades\Cache::put('zkteco_override_port', $port); // Store indefinitely

        // Test the connection immediately
        try {
            if ($zktecoService->connect()) {
                $info = $zktecoService->getDeviceInfo();
                $deviceName = $info['device_name'] ?? 'Unknown Device';
                $zktecoService->disconnect();
                
                $message = "✅ CONFIGURATION UPDATED & CONNECTED!\n";
                $message .= "Successfully connected to {$deviceName} at {$ip}:{$port}";
                
                return redirect()->back()->with('success', $message);
            } else {
                return redirect()->back()->with('error', "⚠️ Configuration Updated, BUT Connection FAILED.\nCould not reach {$ip}:{$port}. Please check your VPN/Network.");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "⚠️ Configuration Updated, BUT Error Occurred: " . $e->getMessage());
        }
    }

    public function syncBiometricUsers(\App\Services\ZktecoService $zktecoService)
    {
        try {
            // Ensure we use the cached config if available
            $ip = \Illuminate\Support\Facades\Cache::get('zkteco_override_ip', config('zkteco.device_ip'));
            $port = \Illuminate\Support\Facades\Cache::get('zkteco_override_port', config('zkteco.device_port'));
            
            if (!$zktecoService->connect()) {
                return redirect()->back()->with('error', "Cannot connect to device at $ip:$port to perform sync.");
            }

            $count = $zktecoService->syncUsersToDevice();
            $zktecoService->disconnect();

            if ($count === false) {
                return redirect()->back()->with('error', "Sync failed. Check logs for details.");
            }

            return redirect()->back()->with('success', "✅ SUCCESS! Synced $count employees to biometric device.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Sync Error: " . $e->getMessage());
        }
    }
}
