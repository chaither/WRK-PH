<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayPeriod extends Model
{
    protected $fillable = [
        'start_date',
        'end_date',
        'status',
        'pay_period_type'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }
}