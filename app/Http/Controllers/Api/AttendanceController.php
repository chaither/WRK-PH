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

                // SMARTER SEQUENTIAL FILLING:
                // We prioritize the assigned session (AM/PM), but overflow if necessary.
                if ($type === 'AM') {
                    if (!$dtr->time_in) {
                        $dtr->time_in = $timestamp;
                        $updated = true;
                    } elseif (!$dtr->time_out && $timestamp->greaterThan(Carbon::parse($dtr->time_in))) {
                        $dtr->time_out = $timestamp;
                        $updated = true;
                    } elseif (!$dtr->time_in_2) {
                        // Overflow to session 2 if morning is already complete
                        $dtr->time_in_2 = $timestamp;
                        $updated = true;
                    } elseif (!$dtr->time_out_2 && $timestamp->greaterThan(Carbon::parse($dtr->time_in_2))) {
                        $dtr->time_out_2 = $timestamp;
                        $updated = true;
                    }
                } else {
                    // Logic for PM:
                    // If morning session is incomplete (missing time_out) and this punch is close to midday,
                    // we allow it to fill the morning clock-out slot first.
                    if ($dtr->time_in && !$dtr->time_out && $timestamp->hour < 14) {
                         $dtr->time_out = $timestamp;
                         $updated = true;
                    } elseif (!$dtr->time_in_2) {
                        $dtr->time_in_2 = $timestamp;
                        $updated = true;
                    } elseif (!$dtr->time_out_2 && $timestamp->greaterThan(Carbon::parse($dtr->time_in_2))) {
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
