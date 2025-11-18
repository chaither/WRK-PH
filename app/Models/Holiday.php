<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'name', 'type', 'rate_multiplier'];

    protected $casts = [
        'date' => 'date',
        'rate_multiplier' => 'decimal:2',
    ];
}
