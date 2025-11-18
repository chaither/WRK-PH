<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GovernmentContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'min_salary',
        'max_salary',
        'is_percentage',
        'employee_share',
        'employer_share',
        'target_type',
        'applies_to',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_percentage' => 'boolean',
        'applies_to' => 'array',
        'employee_share' => 'decimal:2',
        'employer_share' => 'decimal:2',
    ];
}
