<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeRestdayRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_restdays',
        'requested_restdays',
        'reason',
        'status',
    ];

    protected $casts = [
        'current_restdays' => 'array',
        'requested_restdays' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
