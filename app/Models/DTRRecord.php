<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // Add Carbon import
use Illuminate\Support\Facades\Log; // Import Log Facade

class DTRRecord extends Model
{
    protected $table = 'dtr_records';

    protected $fillable = [
        'user_id',
        'date',
        'time_in',
        'time_out',
        'time_in_2',
        'time_out_2',
        'status',
        'remarks',
        'late_minutes',
        'regular_work_hours', // Renamed from work_hours
        'overtime_hours',
        'total_work_hours', // New column
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'time_in_2' => 'datetime',
        'time_out_2' => 'datetime',
        'regular_work_hours' => 'float',
        'overtime_hours' => 'float',
        'total_work_hours' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($dtrRecord) {
            $dtrRecord->recalculateAllHours();
        });
    }

    public function recalculateLateStatus()
    {
        $workStartTime = Carbon::parse($this->user->work_start)->setTimezone('Asia/Manila');
        
        $lateMinutes = 0;
        $status = 'present';

        // Calculate lateness for morning clock-in (time_in)
        if ($this->time_in) {
            $clockInTime = Carbon::parse($this->time_in)->setTimezone('Asia/Manila');
            $clockInTimeOnly = Carbon::createFromTime($clockInTime->hour, $clockInTime->minute, 0, 'Asia/Manila');
            $workStartTimeOnly = Carbon::createFromTime($workStartTime->hour, $workStartTime->minute, 0, 'Asia/Manila');

            if ($clockInTimeOnly->greaterThan($workStartTimeOnly)) {
                $calculatedLateness = floor(abs($clockInTimeOnly->diffInSeconds($workStartTimeOnly)) / 60.0);
                if ($calculatedLateness > 0) {
                    $lateMinutes += $calculatedLateness;
                    $status = 'late';
                }
            } else {
                $status = 'present';
            }
        }

        // Calculate lateness for afternoon clock-in (time_in_2)
        if ($this->time_in_2 && $this->time_out) {
            $afternoonWorkStartTime = Carbon::parse($this->time_out->format('H:i:s'))->addHour()->setTimezone('Asia/Manila'); // Assuming 1 hour lunch break after time_out
            $clockInTime2 = Carbon::parse($this->time_in_2)->setTimezone('Asia/Manila');
            
            $clockInTime2Only = Carbon::createFromTime($clockInTime2->hour, $clockInTime2->minute, $clockInTime2->second, 'Asia/Manila');
            $afternoonWorkStartTimeOnly = Carbon::createFromTime($afternoonWorkStartTime->hour, $afternoonWorkStartTime->minute, $afternoonWorkStartTime->second, 'Asia/Manila');

            if ($clockInTime2Only->greaterThan($afternoonWorkStartTimeOnly)) {
                $calculatedLateness = floor(abs($clockInTime2Only->diffInSeconds($afternoonWorkStartTimeOnly)) / 60.0);
                if ($calculatedLateness > 0) {
                    $lateMinutes += $calculatedLateness;
                    // If already late from morning, keep 'late' status, otherwise set it
                    if ($status !== 'late') {
                        $status = 'late';
                    }
                }
            } else if ($status !== 'late') {
                $status = 'present';
            }
        }

        Log::info('DTRRecord ID: ' . $this->id . ' - Final lateMinutes before saving: ' . $lateMinutes);
        $this->late_minutes = $lateMinutes;
        $this->status = $status;
    }

    public function recalculateAllHours()
    {
        $this->recalculateLateStatus();

        $totalWorkSeconds = $this->calculateWorkHours();

        // Convert total seconds directly to hours (rounded to 2 decimal places)
        $this->regular_work_hours = floor($totalWorkSeconds / 60) / 60; // Convert total seconds to minutes (floor), then to hours
        $this->total_work_hours = $this->regular_work_hours + ($this->overtime_hours ?? 0);

        // Additional status check for half-day
        if (!$this->time_in && $this->time_in_2 && $this->time_out_2 && $this->regular_work_hours > 0) {
            $this->status = 'half_day';
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calculateWorkHours()
    {
        $totalWorkSeconds = 0;

        // First segment: time_in to time_out (morning)
        if ($this->time_in && $this->time_out && $this->time_in instanceof \Carbon\Carbon && $this->time_out instanceof \Carbon\Carbon) {
            $totalWorkSeconds += $this->time_out->diffInSeconds($this->time_in, true);
        }

        // Second segment: time_in_2 to time_out_2 (afternoon)
        if ($this->time_in_2 && $this->time_out_2 && $this->time_in_2 instanceof \Carbon\Carbon && $this->time_out_2 instanceof \Carbon\Carbon) {
            $totalWorkSeconds += $this->time_out_2->diffInSeconds($this->time_in_2, true);
        }

        // Round total seconds to the nearest minute, then convert to total seconds (i.e., remove seconds if < 30, round up if >= 30)
        // Given the requirement to discard seconds (floor), we will just floor the total seconds to the nearest minute's worth of seconds
        $totalWorkSeconds = floor($totalWorkSeconds / 60) * 60; // Floor to nearest minute, then convert back to seconds

        Log::info('DTRRecord ID: ' . $this->id . ' - time_in: ' . ($this->time_in ? $this->time_in->toDateTimeString() : 'NULL'));
        Log::info('DTRRecord ID: ' . $this->id . ' - time_out: ' . ($this->time_out ? $this->time_out->toDateTimeString() : 'NULL'));
        Log::info('DTRRecord ID: ' . $this->id . ' - time_in_2: ' . ($this->time_in_2 ? $this->time_in_2->toDateTimeString() : 'NULL'));
        Log::info('DTRRecord ID: ' . $this->id . ' - time_out_2: ' . ($this->time_out_2 ? $this->time_out_2->toDateTimeString() : 'NULL'));
        Log::info('DTRRecord ID: ' . $this->id . ' - Calculated totalWorkSeconds (floored to minute): ' . $totalWorkSeconds);

        return max(0, $totalWorkSeconds);
    }

    public function calculateLateHours()
    {
        if (!isset($this->late_minutes)) {
            return 0;
        }
        return abs($this->late_minutes / 60);
    }

    // Accessor to format regular_work_hours into H:M:S
    public function getFormattedRegularWorkHoursAttribute()
    {
        // Ensure regular_work_hours is treated as a float representing hours
        $totalMinutes = floor($this->regular_work_hours * 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $seconds = 0; // Seconds are not considered in the calculation as per new logic

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}