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
        'overtime_time_in',
        'overtime_time_out',
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
        $this->loadMissing('user.shift');
        $user = $this->user;
        $shift = $user->shift;

        // 1. Get standard start times from shift or fallback to user model
        $workStartTime = $shift ? Carbon::parse($shift->start_time) : Carbon::parse($user->work_start);
        $lunchEnd = ($shift && $shift->lunch_break_end) ? Carbon::parse($shift->lunch_break_end) : null;
        
        $lateMinutes = 0;
        $status = 'present';

        // 2. Calculate lateness for first session (Session 1 In)
        if ($this->time_in) {
            $clockInTime = Carbon::parse($this->time_in);
            $clockInTimeOnly = (int)$clockInTime->format('Hi');
            $workStartTimeOnly = (int)$workStartTime->format('Hi');

            if ($clockInTimeOnly > $workStartTimeOnly) {
                $diffInSeconds = $clockInTime->diffInSeconds($clockInTime->copy()->setTimeFromTimeString($workStartTime->format('H:i:s')), false);
                $calculatedLateness = floor(abs($diffInSeconds) / 60.0);
                if ($calculatedLateness > 0) {
                    $lateMinutes += $calculatedLateness;
                    $status = 'late';
                }
            }
        }

        // 3. Calculate lateness for second session (Session 2 In)
        if ($this->time_in_2) {
            $clockInTime2 = Carbon::parse($this->time_in_2);
            $clockInTime2Only = (int)$clockInTime2->format('Hi');
            
            // Default expected PM start is either shift lunch end or 1 hour after AM clock out
            if ($lunchEnd) {
                $expectedPmStartStr = $lunchEnd->format('H:i:s');
            } else {
                $expectedPmStartStr = $this->time_out ? Carbon::parse($this->time_out)->addHour()->format('H:i:s') : '13:00';
            }
            
            $expectedPmStart = Carbon::parse($expectedPmStartStr);
            $expectedPmStartOnly = (int)$expectedPmStart->format('Hi');

            if ($clockInTime2Only > $expectedPmStartOnly) {
                $diffInSeconds = $clockInTime2->diffInSeconds($clockInTime2->copy()->setTimeFromTimeString($expectedPmStart->format('H:i:s')), false);
                $calculatedLateness = floor(abs($diffInSeconds) / 60.0);
                if ($calculatedLateness > 0) {
                    $lateMinutes += $calculatedLateness;
                    if ($status !== 'late') $status = 'late';
                }
            }
        }

        $this->late_minutes = $lateMinutes;
        $this->status = $status;
    }

    public function recalculateAllHours()
    {
        $this->recalculateLateStatus();
        $this->calculateWorkHours();
        
        // Additional status check for half-day
        // We use the already computed regular_work_hours
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
        $this->loadMissing('user.shift');
        $shift = $this->user->shift;
        $dtrDate = $this->date; // Carbon instance from casts

        // 1. Establish Shift Window (Absolute Timestamps for this DTR Date)
        if ($shift) {
            $shiftStartStr = $shift->start_time; // H:i:s
            $shiftEndStr = $shift->end_time;     // H:i:s
            
            $sStart = $dtrDate->copy()->setTimeFromTimeString($shiftStartStr);
            $sEnd = $dtrDate->copy()->setTimeFromTimeString($shiftEndStr);
            
            // Handle Night Shift (End < Start)
            if ($sEnd->lessThan($sStart)) {
                $sEnd->addDay();
            }
            
            // Lunch Window
            $lStart = null;
            $lEnd = null;
            if ($shift->lunch_break_start && $shift->lunch_break_end) {
                $lStart = $dtrDate->copy()->setTimeFromTimeString($shift->lunch_break_start);
                $lEnd = $dtrDate->copy()->setTimeFromTimeString($shift->lunch_break_end);
                
                // Adjust for Night Shift crossing midnight
                // If Lunch Start < Shift Start, it implies it's next day (early morning lunch)
                if ($lStart->lessThan($sStart)) {
                    $lStart->addDay();
                }
                // If Lunch End < Lunch Start (rare but possible?), add day
                if ($lEnd->lessThan($lStart)) {
                    $lEnd->addDay();
                }
            }
        } else {
            // No shift, no regular hours possible
            $sStart = null;
            $sEnd = null;
            $lStart = null;
            $lEnd = null;
        }

        // 2. Define Helper for Intersection
        // Returns seconds of overlap between [WorkStart, WorkEnd] and [TargetStart, TargetEnd]
        $getOverlap = function($wStart, $wEnd, $tStart, $tEnd) {
             if (!$wStart || !$wEnd || !$tStart || !$tEnd) return 0;
             
             // Ignore seconds for intersection calculation to allow "minute-perfect" matching
             // e.g. 13:00:25 becomes 13:00:00, preventing 25s deduction
             $wStart = $wStart->copy()->setSecond(0);
             $wEnd = $wEnd->copy()->setSecond(0);
             // Shift/Lunch times are usually 00 seconds already but safer to force
             $tStart = $tStart->copy()->setSecond(0);
             $tEnd = $tEnd->copy()->setSecond(0);

             // Intersection
             $start = $wStart->max($tStart);
             $end = $wEnd->min($tEnd);
             
             if ($end->lessThan($start)) return 0;
             
             return abs($end->diffInSeconds($start));
        };
        
        // 3. Define Helper for Total Duration
        // User Request: Strict clamping. Early In ignored. Late Out ignored. 
        // Total Hours should basically be the same as Regular Hours (Shift Intersection).
        // "No less, no more unless if the employee is late time in"
        
        $regularSeconds = 0;
        
        // Process Session 1
        if ($this->time_in && $this->time_out) {
            if ($sStart && $sEnd) {
                // Fully Clamped Intersection
                $rawOverlap = $getOverlap($this->time_in, $this->time_out, $sStart, $sEnd);
                
                if ($lStart && $lEnd) {
                    $lunchOverlap = $getOverlap($this->time_in, $this->time_out, $lStart, $lEnd);
                    $rawOverlap = max(0, $rawOverlap - $lunchOverlap);
                }
                $regularSeconds += $rawOverlap;
            }
        }
        
        // Process Session 2
        if ($this->time_in_2 && $this->time_out_2) {
            if ($sStart && $sEnd) {
                // Fully Clamped Intersection
                $rawOverlap = $getOverlap($this->time_in_2, $this->time_out_2, $sStart, $sEnd);
                if ($lStart && $lEnd) {
                    $lunchOverlap = $getOverlap($this->time_in_2, $this->time_out_2, $lStart, $lEnd);
                    $rawOverlap = max(0, $rawOverlap - $lunchOverlap);
                }
                $regularSeconds += $rawOverlap;
                
            }
        }
        
        // Bridge Case (In 1 -> Out 2)
        if ($this->time_in && !$this->time_out && ($this->time_in_2 || $this->time_out_2)) {
             $out = $this->time_out_2 ?: $this->time_in_2;
             
             if ($sStart && $sEnd) {
                 $rawOverlap = $getOverlap($this->time_in, $out, $sStart, $sEnd);
                 
                 if ($lStart && $lEnd) {
                     $lunchOverlap = $getOverlap($this->time_in, $out, $lStart, $lEnd);
                     $rawOverlap = max(0, $rawOverlap - $lunchOverlap);
                 } elseif ($shift && $shift->lunch_break_duration) {
                      $rawOverlap -= ($shift->lunch_break_duration * 3600);
                 }
                 $regularSeconds += max(0, $rawOverlap);
             }
        }

        // Floor to nearest minute
        $regularSeconds = floor($regularSeconds / 60) * 60;
        
        // 4. Overtime Calculation (Strict Intersection with Approved Request)
        // Only calculate if approved overtime window is set (overtime_time_in / overtime_time_out)
        // These fields should be populated by the Overtime Request Approval process
        
        $otSeconds = 0;
        
        if ($this->overtime_time_in && $this->overtime_time_out) {
            $otStart = $dtrDate->copy()->setTimeFromTimeString($this->overtime_time_in);
            $otEnd = $dtrDate->copy()->setTimeFromTimeString($this->overtime_time_out);
            
            // Handle Night Shift OT Crossing Midnight
            if ($otEnd->lessThan($otStart)) {
                $otEnd->addDay();
            }
            
            // Per User Request: If OT is approved, payout the FULL approved amount 
            // regardless of actual clock out time (Guaranteed OT).
            // We only check if they were present at all to be safe (regular work hours > 0).
            
            if ($this->regular_work_hours > 0) {
                 // Ensure absolute positive value
                 $approvedDuration = abs($otEnd->diffInSeconds($otStart));
                 $otSeconds = $approvedDuration;
            } else {
                 $otSeconds = 0;
            }
        }

        // Total Work Hours = Regular Hours + Overtime Hours
        // NOTE: Regular Hours are already strictly clamped to SHIFT.
        // Overtime Hours are strictly clamped to APPROVED OT WINDOW.
        
        $totalSeconds = $regularSeconds + $otSeconds;

        Log::info("DTR Calculation [ID:{$this->id}]: Regular: {$regularSeconds}s, OT: {$otSeconds}s, Total: {$totalSeconds}s");

        // Assign Values
        $this->regular_work_hours = floor($regularSeconds / 60) / 60;
        $this->overtime_hours = floor($otSeconds / 60) / 60;
        $this->total_work_hours = floor($totalSeconds / 60) / 60;

        return $totalSeconds;
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
        // Use round to avoid floating point floor errors (e.g. 7.98333 * 60 = 478.999 -> floor = 478 instead of 479)
        $totalMinutes = round($this->regular_work_hours * 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $seconds = 0;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function getFormattedTotalWorkHoursAttribute()
    {
        $totalMinutes = round($this->total_work_hours * 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $seconds = 0; 

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}