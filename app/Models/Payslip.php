<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $fillable = [
        'user_id',
        'pay_period_id',
        'pay_period_start',
        'pay_period_end',
        'gross_pay',
        'overtime_pay',
        'late_deductions',
        'deductions',
        'net_pay',
        'total_hours_worked',
        'overtime_hours',
        'late_minutes',
        'absent_days',
        'details'
    ];

    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'details' => 'array',
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