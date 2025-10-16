<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $fillable = [
        'user_id',
        'pay_period_id',
        'basic_pay',
        'overtime_pay',
        'late_deductions',
        'absences_deductions',
        'sss',
        'gsis',
        'philhealth',
        'net_pay',
        'total_hours_worked',
        'overtime_hours',
        'late_minutes',
        'absent_days'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payPeriod(): BelongsTo
    {
        return $this->belongsTo(PayPeriod::class);
    }
}