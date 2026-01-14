<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'night_shift_multiplier',
        'is_night_shift',
        'lunch_break_start',
        'lunch_break_end',
        'lunch_break_duration',
        'is_lunch_paid',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
