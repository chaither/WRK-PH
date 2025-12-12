<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HmoDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_salary',
        'max_salary',
        'is_percentage',
        'employee_share',
        'employer_share',
        'target_type',
        'applies_to',
        'deduction_frequency',
        'deduction_frequency_target_type',
        'deduction_frequency_applies_to',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_percentage' => 'boolean',
        'applies_to' => 'array',
        'deduction_frequency' => 'string',
        'deduction_frequency_target_type' => 'string',
        'deduction_frequency_applies_to' => 'array',
        'employee_share' => 'decimal:2',
        'employer_share' => 'decimal:2',
    ];
}
