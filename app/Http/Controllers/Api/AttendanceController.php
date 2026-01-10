<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DTRRecord;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function storeBatch(Request $request)
    {
        $logs = $request->input('logs', []);
        $processed = 0;
        $errors = 0;

        Log::info('Received batch attendance upload', ['count' => count($logs)]);

        foreach ($logs as $log) {
            try {
                $employeeId = $log['employee_id'];
                $timestamp = Carbon::parse($log['timestamp']);
                $date = $timestamp->toDateString();
                $type = $log['type'] ?? 'AM'; // AM / PM

                $user = User::where('employee_id', $employeeId)->first();
                if (!$user) {
                    continue; // Skip if user not found
                }

                $dtr = DTRRecord::firstOrCreate(
                    ['user_id' => $user->id, 'date' => $date],
                    ['status' => 'present']
                );

                $updated = false;

                if ($type === 'AM') {
                    // Logic for AM:
                    // If both time_in and time_out are already set, IGNORE further punches (Locked).
                    if ($dtr->time_in && $dtr->time_out) {
                         // Locked - do nothing
                         $updated = false;
                    } elseif (!$dtr->time_in) {
                        $dtr->time_in = $timestamp;
                        $updated = true;
                    } elseif ($timestamp->greaterThan(Carbon::parse($dtr->time_in)) && !$dtr->time_out) {
                        $dtr->time_out = $timestamp;
                        $updated = true;
                    }
                } else {
                    // Logic for PM:
                    // If both time_in_2 and time_out_2 are already set, IGNORE further punches (Locked).
                    if ($dtr->time_in_2 && $dtr->time_out_2) {
                         // Locked - do nothing
                         $updated = false;
                    } elseif (!$dtr->time_in_2) {
                        $dtr->time_in_2 = $timestamp;
                        $updated = true;
                    } elseif ($timestamp->greaterThan(Carbon::parse($dtr->time_in_2)) && !$dtr->time_out_2) {
                        $dtr->time_out_2 = $timestamp;
                        $updated = true;
                    }
                }

                if ($updated) {
                    $dtr->save();
                    $processed++;
                }

            } catch (\Exception $e) {
                Log::error('Error processing log', ['log' => $log, 'error' => $e->getMessage()]);
                $errors++;
            }
        }

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'errors' => $errors
        ]);
    }
}
