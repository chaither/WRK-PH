<?php

namespace App\Services;

use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Support\Facades\Log;
use App\Models\DTRRecord;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class ZktecoService
{
    protected $zkteco;

    public function __construct()
    {
        // Delay initialization until connect() is called
    }

    protected function initializeZkteco()
    {
        if (!$this->zkteco) {
            try {
                $this->zkteco = new ZKTeco(
                    ip: config('zkteco.device_ip'),
                    port: config('zkteco.device_port'),
                    shouldPing: config('zkteco.should_ping'),
                    timeout: config('zkteco.timeout'),
                    password: config('zkteco.device_password')
                );
            } catch (Exception $e) {
                Log::error('Failed to initialize ZKTeco device: ' . $e->getMessage());
                throw $e;
            }
        }
    }

    public function connect()
    {
        try {
            $this->initializeZkteco();
            $this->zkteco->connect();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to connect to ZKTeco device: ' . $e->getMessage());
            return false;
        }
    }

    public function disconnect()
    {
        try {
            $this->zkteco->disconnect();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to disconnect from ZKTeco device: ' . $e->getMessage());
            return false;
        }
    }

    public function getDeviceInfo()
    {
        $this->initializeZkteco();
        return [
            'vendor' => $this->zkteco->vendorName(),
            'device_name' => $this->zkteco->deviceName(),
            'serial_number' => $this->zkteco->serialNumber(),
            'platform' => $this->zkteco->platform(),
            'version' => $this->zkteco->version(),
            'fm_version' => $this->zkteco->fmVersion(),
        ];
    }

    public function getUsers()
    {
        try {
            $this->initializeZkteco();
            return $this->zkteco->getUsers();
        } catch (Exception $e) {
            Log::error('Failed to get users from ZKTeco device: ' . $e->getMessage());
            return [];
        }
    }

    public function getAttendances($callback = null)
    {
        try {
            $this->initializeZkteco();
            return $this->zkteco->getAttendances($callback);
        } catch (Exception $e) {
            Log::error('Failed to get attendances from ZKTeco device: ' . $e->getMessage());
            return [];
        }
    }

    public function clearAttendances()
    {
        try {
            $this->initializeZkteco();
            $this->zkteco->clearAttendance();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to clear attendances on ZKTeco device: ' . $e->getMessage());
            return false;
        }
    }

    public function setUser($uid, $userid, $name, $password = '', $role = 0)
    {
        try {
            $this->initializeZkteco();
            $this->zkteco->setUser($uid, $userid, $name, $password, $role);
            return true;
        } catch (Exception $e) {
            Log::error('Failed to set user on ZKTeco device: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($uid)
    {
        try {
            $this->initializeZkteco();
            $this->zkteco->deleteUser($uid);
            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete user from ZKTeco device: ' . $e->getMessage());
            return false;
        }
    }

    public function getTime()
    {
        try {
            $this->initializeZkteco();
            return $this->zkteco->getTime();
        } catch (Exception $e) {
            Log::error('Failed to get time from ZKTeco device: ' . $e->getMessage());
            return null;
        }
    }

    public function syncUsersToDevice()
    {
        // Ensure we are connected before pushing users; otherwise nothing is written.
        if (!$this->connect()) {
            Log::warning('Biometric device is offline, skipping user sync');
            return false;
        }

        try {
            // Get all non-admin users from database (admins should never be on the device)
            $users = \App\Models\User::whereNotNull('employee_id')
                ->where('role', '!=', 'admin')
                ->get();

            $synced = 0;
            foreach ($users as $user) {
                // Set user on device with employee_id as userid
                $this->zkteco->setUser(
                    $user->id, // uid
                    $user->employee_id, // userid
                    $user->name,
                    '', // password
                    0 // role
                );
                $synced++;
            }

            return $synced;
        } catch (Exception $e) {
            Log::error('Failed to sync users to ZKTeco device: ' . $e->getMessage());
            return false;
        } finally {
            try {
                $this->disconnect();
            } catch (Exception $disconnectError) {
                // Ignore disconnect errors to avoid masking primary issues
            }
        }
    }

    /**
     * Sync a single user to the biometric device
     * Only syncs if device is online and user has employee_id
     * 
     * @param \App\Models\User $user
     * @return bool Returns true if synced successfully, false otherwise
     */
    public function syncSingleUser($user)
    {
        // Only sync if user has employee_id and is not an admin
        if (!$user || !$user->employee_id) {
            Log::info('Skipping biometric sync: User does not have employee_id', ['user_id' => $user->id ?? null]);
            return false;
        }
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            Log::info('Skipping biometric sync: Admin users are not synced to device', ['user_id' => $user->id]);
            return false;
        }

        try {
            // Check if device is online before attempting sync
            if (!$this->connect()) {
                Log::warning('Biometric device is offline, skipping sync for user', [
                    'user_id' => $user->id,
                    'employee_id' => $user->employee_id
                ]);
                return false;
            }

            // Set user on device with employee_id as userid
            $result = $this->setUser(
                $user->id, // uid
                $user->employee_id, // userid
                $user->name,
                '', // password
                0 // role
            );

            $this->disconnect();

            if ($result) {
                Log::info('Successfully synced user to biometric device', [
                    'user_id' => $user->id,
                    'employee_id' => $user->employee_id,
                    'name' => $user->name
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Failed to sync single user to ZKTeco device', [
                'user_id' => $user->id,
                'employee_id' => $user->employee_id,
                'error' => $e->getMessage()
            ]);
            // Make sure to disconnect even if there's an error
            try {
                $this->disconnect();
            } catch (Exception $disconnectError) {
                // Ignore disconnect errors
            }
            return false;
        }
    }

    /**
     * Sync attendances from device and create/update DTR records
     * This method processes attendance records and creates daily time records
     * 
     * @param bool $clearDeviceAfterSync Whether to clear attendances from device after sync
     * @return array Returns array with 'synced' and 'skipped' counts
     */
    public function syncAttendancesToDTR($clearDeviceAfterSync = false)
    {
        try {
            // Check if device is online
            if (!$this->connect()) {
                Log::warning('Biometric device is offline, cannot sync attendances');
                return ['synced' => 0, 'skipped' => 0, 'error' => 'Device offline'];
            }

            // Get attendances from device
            $attendances = $this->getAttendances();

            if (empty($attendances)) {
                Log::info('No attendances found on biometric device');
                $this->disconnect();
                return ['synced' => 0, 'skipped' => 0];
            }

            Log::info('Processing attendances from biometric device', ['count' => count($attendances)]);

            // Log first attendance to see the data structure
            if (!empty($attendances)) {
                Log::info('Sample attendance record structure', [
                    'first_attendance' => $attendances[0],
                    'keys' => array_keys($attendances[0] ?? [])
                ]);
            }

            // Group attendances by user and date, then sort chronologically
            // This ensures we process attendances in the correct order
            $groupedAttendances = [];
            foreach ($attendances as $attendance) {
                try {
                    $attendanceUserId = $attendance['user_id'] ?? null;
                    if (!$attendanceUserId) {
                        continue;
                    }

                    $timestampString = $attendance['record_time'] ?? $attendance['timestamp'] ?? null;
                    if (!$timestampString) {
                        continue;
                    }

                    $timestamp = Carbon::parse($timestampString);
                    $date = $timestamp->toDateString();
                    
                    $key = "{$attendanceUserId}_{$date}";
                    if (!isset($groupedAttendances[$key])) {
                        $groupedAttendances[$key] = [];
                    }
                    
                    $groupedAttendances[$key][] = [
                        'attendance' => $attendance,
                        'user_id' => $attendanceUserId,
                        'timestamp' => $timestamp,
                        'date' => $date
                    ];
                } catch (\Exception $e) {
                    // Skip invalid records
                    continue;
                }
            }

            // Sort each group chronologically (oldest first)
            // This ensures we process attendances in the correct order
            foreach ($groupedAttendances as $key => $group) {
                usort($groupedAttendances[$key], function($a, $b) {
                    return $a['timestamp']->greaterThan($b['timestamp']) ? 1 : -1;
                });
            }
            
            // However, if a DTR record doesn't exist for a date, we want to prioritize newer attendances
            // So we'll process in reverse order (newest first) for dates without DTR records
            // This handles the case where DTR was deleted and new attendances come in

            $synced = 0;
            $skipped = 0;
            $skippedReasons = [
                'user_not_found' => 0,
                'admin_user' => 0,
                'already_recorded' => 0,
                'all_slots_filled' => 0,
                'error' => 0
            ];

            // Process grouped attendances
            foreach ($groupedAttendances as $group) {
                // Check if DTR record exists for this user/date combination
                $firstItem = $group[0];
                $checkUser = User::where('employee_id', (string)$firstItem['user_id'])
                               ->orWhere('id', $firstItem['user_id'])
                               ->first();
                
                $dtrExists = false;
                if ($checkUser) {
                    $dtrExists = DTRRecord::where('user_id', $checkUser->id)
                                         ->where('date', $firstItem['date'])
                                         ->exists();
                }
                
                // If DTR doesn't exist, process in reverse order (newest first) to prioritize new attendances
                // If DTR exists, process in normal order (oldest first) to fill slots chronologically
                $processOrder = $dtrExists ? $group : array_reverse($group);
                
                foreach ($processOrder as $item) {
                    $attendance = $item['attendance'];
                    $attendanceUserId = $item['user_id'];
                    $timestamp = $item['timestamp'];
                    $date = $item['date'];
                try {
                    // Log the attendance being processed
                    Log::debug('Processing attendance record', [
                        'attendance' => $attendance,
                        'uid' => $attendance['uid'] ?? 'unknown',
                        'user_id' => $attendanceUserId,
                        'record_time' => $timestamp->format('Y-m-d H:i:s')
                    ]);

                    // Find user by employee_id (preferred) or by database id
                    // The device user_id should match the employee_id we synced
                    $user = User::where('employee_id', (string)$attendanceUserId)
                               ->orWhere('id', $attendanceUserId)
                               ->first();

                if (!$user) {
                    Log::warning('User not found for attendance record', [
                        'attendance_id' => $attendanceUserId,
                        'timestamp' => $timestamp->format('Y-m-d H:i:s'),
                        'searched_employee_ids' => User::whereNotNull('employee_id')->pluck('employee_id')->toArray()
                    ]);
                    $skipped++;
                    $skippedReasons['user_not_found']++;
                    continue;
                }

                // Skip admin users entirely — they should not have biometric logs or DTRs
                if ($user->isAdmin()) {
                    Log::info('Skipping attendance for admin user', [
                        'user_id' => $user->id,
                        'employee_id' => $user->employee_id,
                        'timestamp' => $timestamp->format('Y-m-d H:i:s'),
                    ]);
                    $skipped++;
                    $skippedReasons['admin_user']++;
                    continue;
                }

                // Prevent creating DTR records for logs earlier than the employee's lifecycle
                // Handles "fresh employee gets instant time-in" caused by old logs on device
                $employmentStartDate = null;
                if (!empty($user->start_date)) {
                    try {
                        $employmentStartDate = Carbon::parse($user->start_date)->toDateString();
                    } catch (\Exception $e) {
                        // Ignore parse errors, fall back to created_at below
                    }
                }
                $createdAtDate = null;
                $createdAtExact = null;
                if (!empty($user->created_at)) {
                    try {
                        $createdAtExact = Carbon::parse($user->created_at);
                        $createdAtDate = $createdAtExact->toDateString();
                    } catch (\Exception $e) {
                        // Ignore parse errors
                    }
                }

                // Use the later of start_date and created_at (date) as minimum date
                $minValidDate = $employmentStartDate ?? $createdAtDate;
                if ($employmentStartDate && $createdAtDate) {
                    $minValidDate = max($employmentStartDate, $createdAtDate);
                }

                // Skip logs dated before start/creation date (date-level guard)
                if ($minValidDate && $date < $minValidDate) {
                    Log::info('Skipping attendance before employment start date', [
                        'user_id' => $user->id,
                        'employee_id' => $user->employee_id,
                        'attendance_date' => $date,
                        'min_valid_date' => $minValidDate,
                    ]);
                    $skipped++;
                    $skippedReasons['already_recorded']++;
                    continue;
                }

                // Also skip logs earlier (with time) than account creation time to avoid
                // same-day older logs being pulled right after creation
                if ($createdAtExact && $timestamp->lessThan($createdAtExact)) {
                    Log::info('Skipping attendance earlier than user creation datetime', [
                        'user_id' => $user->id,
                        'employee_id' => $user->employee_id,
                        'attendance_timestamp' => $timestamp->format('Y-m-d H:i:s'),
                        'user_created_at' => $createdAtExact->format('Y-m-d H:i:s'),
                    ]);
                    $skipped++;
                    $skippedReasons['already_recorded']++;
                    continue;
                }

                $time = $timestamp->toTimeString();

                // Get or create DTR record for this user and date
                $dtrRecord = DTRRecord::where('user_id', $user->id)
                                      ->where('date', $date)
                                      ->first();

                    if (!$dtrRecord) {
                        // No DTR record exists for this date - create new one
                        // Decide the initial slot based on shift and time of day
                        $preferAfternoonFirstSlot = $this->shouldUseAfternoonSlot($user, $timestamp, new DTRRecord());

                        $payload = [
                            'user_id' => $user->id,
                            'date' => $date,
                            'status' => 'present',
                        ];

                        if ($preferAfternoonFirstSlot) {
                            $payload['time_in_2'] = $timestamp;
                            Log::info('Created new DTR record with afternoon time_in_2 (no morning log)', [
                                'user_id' => $user->id,
                                'employee_id' => $user->employee_id,
                                'date' => $date,
                                'time_in_2' => $time,
                            ]);
                        } else {
                            $payload['time_in'] = $timestamp;
                            Log::info('Created new DTR record with time_in', [
                                'user_id' => $user->id,
                                'employee_id' => $user->employee_id,
                                'date' => $date,
                                'time_in' => $time,
                            ]);
                        }

                        $dtrRecord = DTRRecord::create($payload);
                        $synced++;
                        Log::info('DTR record created - will be updated with subsequent attendances', [
                            'user_id' => $user->id,
                            'employee_id' => $user->employee_id,
                            'date' => $date,
                            'first_slot' => $preferAfternoonFirstSlot ? 'time_in_2' : 'time_in'
                        ]);
                    } else {
                        // Check if this exact timestamp is already recorded (exact match to second)
                        $timeAlreadyRecorded = false;
                        $timeFields = ['time_in', 'time_out', 'time_in_2', 'time_out_2'];
                        
                        foreach ($timeFields as $field) {
                            if ($dtrRecord->$field) {
                                $existingTime = Carbon::parse($dtrRecord->$field);
                                // Check for exact match (to the second) - only skip true duplicates
                                if ($existingTime->format('Y-m-d H:i:s') === $timestamp->format('Y-m-d H:i:s')) {
                                    $timeAlreadyRecorded = true;
                                    Log::debug('Exact timestamp already recorded (exact duplicate), skipping', [
                                        'user_id' => $user->id,
                                        'date' => $date,
                                        'field' => $field,
                                        'existing_time' => $existingTime->format('Y-m-d H:i:s'),
                                        'new_time' => $timestamp->format('Y-m-d H:i:s')
                                    ]);
                                    break;
                                }
                            }
                        }

                        if ($timeAlreadyRecorded) {
                            // This exact timestamp was already processed, skip it
                            $skipped++;
                            $skippedReasons['already_recorded']++;
                            continue;
                        }

                        // Try to find an appropriate slot for this timestamp
                        // Since attendances are sorted chronologically, we can assign them properly
                        $updated = false;

                        $preferAfternoonFirstSlot = $this->shouldUseAfternoonSlot($user, $timestamp, $dtrRecord);

                        if ($preferAfternoonFirstSlot) {
                            // Day shift but first log is already afternoon: treat as PM time-in/out set
                            if (!$dtrRecord->time_in_2) {
                                $dtrRecord->time_in_2 = $timestamp;
                                $updated = true;
                                Log::info('Updated DTR record with afternoon first time_in_2 (no morning log)', [
                                    'user_id' => $user->id,
                                    'date' => $date,
                                    'time_in_2' => $time
                                ]);
                            } elseif (!$dtrRecord->time_out_2 && $timestamp->greaterThan(Carbon::parse($dtrRecord->time_in_2))) {
                                $dtrRecord->time_out_2 = $timestamp;
                                $updated = true;
                                Log::info('Updated DTR record with afternoon first time_out_2 (no morning log)', [
                                    'user_id' => $user->id,
                                    'date' => $date,
                                    'time_out_2' => $time
                                ]);
                            }
                        } elseif ($dtrRecord->time_in_2 && !$dtrRecord->time_out_2 && $timestamp->greaterThan(Carbon::parse($dtrRecord->time_in_2))) {
                            // PM-first flow already started; next punch should close PM slot
                            $dtrRecord->time_out_2 = $timestamp;
                            $updated = true;
                            Log::info('Updated DTR record with time_out_2 following afternoon-first sequence', [
                                'user_id' => $user->id,
                                'date' => $date,
                                'time_out_2' => $time
                            ]);
                        } elseif (!$dtrRecord->time_in) {
                            $dtrRecord->time_in = $timestamp;
                            $updated = true;
                            Log::info('Updated DTR record with time_in', [
                                'user_id' => $user->id,
                                'date' => $date,
                                'time_in' => $time
                            ]);
                        } elseif (!$dtrRecord->time_out && $dtrRecord->time_in && $timestamp->greaterThan(Carbon::parse($dtrRecord->time_in))) {
                            $dtrRecord->time_out = $timestamp;
                            $updated = true;
                            Log::info('Updated DTR record with time_out', [
                                'user_id' => $user->id,
                                'date' => $date,
                                'time_out' => $time
                            ]);
                        } elseif (!$dtrRecord->time_in_2 && 
                                 $timestamp->greaterThan(Carbon::parse($dtrRecord->time_out ?? $dtrRecord->time_in))) {
                            $dtrRecord->time_in_2 = $timestamp;
                            $updated = true;
                            Log::info('Updated DTR record with time_in_2', [
                                'user_id' => $user->id,
                                'date' => $date,
                                'time_in_2' => $time
                            ]);
                        } elseif (!$dtrRecord->time_out_2 && 
                                 $timestamp->greaterThan(Carbon::parse($dtrRecord->time_in_2 ?? $dtrRecord->time_out ?? $dtrRecord->time_in))) {
                            $dtrRecord->time_out_2 = $timestamp;
                            $updated = true;
                            Log::info('Updated DTR record with time_out_2', [
                                'user_id' => $user->id,
                                'date' => $date,
                                'time_out_2' => $time
                            ]);
                        } else {
                            // All slots filled - but check if this is a newer attendance that should replace an older one
                            // This handles the case where DTR was deleted and new attendances come in
                            $allFilled = $dtrRecord->time_in && 
                                        $dtrRecord->time_out && 
                                        $dtrRecord->time_in_2 && 
                                        $dtrRecord->time_out_2;
                            
                            if ($allFilled) {
                                // Check if this timestamp is newer than any existing slot
                                // If so, we might want to update the latest slot (time_out_2) if it's significantly older
                                $latestTime = Carbon::parse($dtrRecord->time_out_2 ?? $dtrRecord->time_in_2 ?? $dtrRecord->time_out ?? $dtrRecord->time_in);
                                
                                // If new timestamp is significantly newer (more than 1 hour), update time_out_2
                                // This handles cases where employee clocks out later than recorded
                                if ($timestamp->greaterThan($latestTime->copy()->addHour())) {
                                    $dtrRecord->time_out_2 = $timestamp;
                                    $updated = true;
                                    $synced++;
                                    Log::info('Updated DTR record time_out_2 with newer timestamp', [
                                        'user_id' => $user->id,
                                        'date' => $date,
                                        'old_time_out_2' => $latestTime->format('H:i:s'),
                                        'new_time_out_2' => $time
                                    ]);
                                } else {
                                    $skipped++;
                                    $skippedReasons['all_slots_filled']++;
                                    Log::debug('All time slots filled for DTR record', [
                                        'user_id' => $user->id,
                                        'employee_id' => $user->employee_id,
                                        'date' => $date,
                                        'timestamp' => $time
                                    ]);
                                }
                            } else {
                                // Some slots are empty but timestamp doesn't fit chronologically
                                // This shouldn't happen with sorted attendances, but log it
                                $skipped++;
                                $skippedReasons['already_recorded']++;
                                Log::debug('Timestamp does not fit chronological order', [
                                    'user_id' => $user->id,
                                    'date' => $date,
                                    'timestamp' => $time
                                ]);
                            }
                        }

                        if ($updated) {
                            $dtrRecord->save(); // Save to trigger recalculation
                            $synced++;
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Error processing individual attendance record', [
                        'attendance' => $attendance,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $skipped++;
                    $skippedReasons['error']++;
                    continue;
                }
            } // End of foreach group
            } // End of foreach groupedAttendances

            $this->disconnect();

            // Clear attendances from device if requested
            if ($clearDeviceAfterSync && $synced > 0) {
                Log::info('Clearing attendances from biometric device after sync');
                if ($this->connect() && $this->clearAttendances()) {
                    Log::info('Successfully cleared attendances from device');
                } else {
                    Log::warning('Failed to clear attendances from device');
                }
                $this->disconnect();
            }

            Log::info('Attendance sync completed', [
                'synced' => $synced,
                'skipped' => $skipped,
                'total' => count($attendances),
                'skip_reasons' => $skippedReasons
            ]);

            return [
                'synced' => $synced, 
                'skipped' => $skipped,
                'skip_reasons' => $skippedReasons
            ];
        } catch (Exception $e) {
            Log::error('Failed to sync attendances to DTR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            try {
                $this->disconnect();
            } catch (Exception $disconnectError) {
                // Ignore disconnect errors
            }
            return ['synced' => 0, 'skipped' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Determine if this user follows a "day shift" pattern (morning start)
     */
    protected function isDayShift($user): bool
    {
        if (!$user) {
            return false;
        }

        // Prefer explicit shift relation/name
        $shift = null;
        try {
            $shift = $user->relationLoaded('shift') ? $user->shift : $user->shift()->first();
        } catch (\Exception $e) {
            $shift = null;
        }

        if ($shift) {
            $name = strtolower($shift->name ?? '');
            if (str_contains($name, 'day')) {
                return true;
            }
            if (!empty($shift->start_time)) {
                try {
                    return Carbon::parse($shift->start_time)->hour < 12;
                } catch (\Exception $e) {
                    // fall through
                }
            }
        }

        // Fallback to per-user work_start if provided
        if (!empty($user->work_start)) {
            try {
                return Carbon::parse($user->work_start)->hour < 12;
            } catch (\Exception $e) {
                // ignore
            }
        }

        return false;
    }

    /**
     * Decide whether the first log for the day should be placed in the afternoon slot
     * (time_in_2) instead of morning slot (time_in).
     */
    protected function shouldUseAfternoonSlot($user, Carbon $timestamp, $dtrRecord): bool
    {
        // Only consider this when there are no previous slots filled
        $hasAnyTimes = $dtrRecord->time_in || $dtrRecord->time_out || $dtrRecord->time_in_2 || $dtrRecord->time_out_2;
        if ($hasAnyTimes) {
            return false;
        }

        if (!$this->isDayShift($user)) {
            return false;
        }

        $midday = Carbon::createFromTime(12, 0, 0, $timestamp->timezone);
        return $timestamp->greaterThanOrEqualTo($midday);
    }
}