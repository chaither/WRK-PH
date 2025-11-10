<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoBioRequest extends Model
{
    protected $fillable = ['user_id', 'date', 'type', 'reason', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
