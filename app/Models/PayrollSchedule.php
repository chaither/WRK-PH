<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['pay_period_type', 'generation_days'];

    protected $casts = [
        'generation_days' => 'array',
    ];
}
