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
        'pay_period_type',
        'generated_by_user_id',
        'regenerated_by_user_id',
        'marked_paid_by_user_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }

    public function regeneratedBy()
    {
        return $this->belongsTo(User::class, 'regenerated_by_user_id');
    }

    public function markedPaidBy()
    {
        return $this->belongsTo(User::class, 'marked_paid_by_user_id');
    }
}