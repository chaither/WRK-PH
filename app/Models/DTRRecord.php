<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // Add Carbon import

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
        'work_hours', // Add this line
        'overtime_hours',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'time_in_2' => 'datetime',
        'time_out_2' => 'datetime',
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
        if (!$this->time_in || !$this->user) {
            $this->late_minutes = 0;
            $this->status = 'pending'; // Or a suitable default
            return;
        }

        $workStartTime = Carbon::parse($this->user->work_start)->setTimezone('Asia/Manila');
        $clockInTime = Carbon::parse($this->time_in)->setTimezone('Asia/Manila');

        // Create Carbon instances with today's date but using only the time for comparison
        $clockInTimeOnly = Carbon::createFromTime($clockInTime->hour, $clockInTime->minute, $clockInTime->second, 'Asia/Manila');
        $workStartTimeOnly = Carbon::createFromTime($workStartTime->hour, $workStartTime->minute, $workStartTime->second, 'Asia/Manila');

        $lateMinutes = 0;
        $status = 'present';

        if ($clockInTimeOnly->greaterThan($workStartTimeOnly)) {
            $lateMinutes = abs($clockInTimeOnly->diffInMinutes($workStartTimeOnly));
            $status = 'late';
        }

        $this->late_minutes = $lateMinutes;
        $this->status = $status;
    }

    public function recalculateAllHours()
    {
        $this->recalculateLateStatus();

        $totalWorkHours = $this->calculateWorkHours();
        $totalOvertimeHours = $this->calculateOvertimeHours($totalWorkHours);

        $this->work_hours = $totalWorkHours;
        $this->overtime_hours = $totalOvertimeHours;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calculateWorkHours()
    {
        $workMinutes = 0;

        // First segment: time_in to time_out (morning)
        if ($this->time_in && $this->time_out && $this->time_in instanceof \Carbon\Carbon && $this->time_out instanceof \Carbon\Carbon) {
            $workMinutes += $this->time_out->diffInMinutes($this->time_in, true); // Added true for absolute difference
        }

        // Second segment: time_in_2 to time_out_2 (afternoon)
        if ($this->time_in_2 && $this->time_out_2 && $this->time_in_2 instanceof \Carbon\Carbon && $this->time_out_2 instanceof \Carbon\Carbon) {
            $workMinutes += $this->time_out_2->diffInMinutes($this->time_in_2, true); // Added true for absolute difference
        }

        \Log::info('DTRRecord ID: ' . $this->id . ' - time_in: ' . ($this->time_in ? $this->time_in->toDateTimeString() : 'NULL'));
        \Log::info('DTRRecord ID: ' . $this->id . ' - time_out: ' . ($this->time_out ? $this->time_out->toDateTimeString() : 'NULL'));
        \Log::info('DTRRecord ID: ' . $this->id . ' - time_in_2: ' . ($this->time_in_2 ? $this->time_in_2->toDateTimeString() : 'NULL'));
        \Log::info('DTRRecord ID: ' . $this->id . ' - time_out_2: ' . ($this->time_out_2 ? $this->time_out_2->toDateTimeString() : 'NULL'));
        \Log::info('DTRRecord ID: ' . $this->id . ' - Calculated workMinutes: ' . $workMinutes);


        return max(0, (float) ($workMinutes / 60));
    }

    public function calculateOvertimeHours(float $totalWorkHours = 0)
    {
        $regularWorkHours = 8; // Assuming 8 regular work hours

        if ($totalWorkHours > $regularWorkHours) {
            return (float) ($totalWorkHours - $regularWorkHours);
        }
        return 0;
    }

    public function calculateLateHours()
    {
        if (!isset($this->late_minutes)) {
            return 0;
        }
        return abs(round($this->late_minutes / 60, 2));
    }
}