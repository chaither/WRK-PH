<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DTRRecord extends Model
{
    protected $table = 'dtr_records';

    protected $fillable = [
        'user_id',
        'date',
        'time_in',
        'time_out',
        'status',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calculateWorkHours()
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        $timeIn = \Carbon\Carbon::parse($this->time_in);
        $timeOut = \Carbon\Carbon::parse($this->time_out);
        $regularWorkHours = 8;

        $totalMinutes = $timeOut->diffInMinutes($timeIn);
        $actualHours = min($totalMinutes / 60, $regularWorkHours);
        return max(0, (float) $actualHours);
    }

    public function calculateOvertimeHours()
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        $timeIn = \Carbon\Carbon::parse($this->time_in);
        $timeOut = \Carbon\Carbon::parse($this->time_out);
        $regularWorkHours = 8;

        $totalMinutes = $timeOut->diffInMinutes($timeIn);
        $totalHours = $totalMinutes / 60;
        return max(0, (float) $totalHours - $regularWorkHours);
    }
}