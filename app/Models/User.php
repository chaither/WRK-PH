<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'position',
        'basic_salary',
        'pay_period',
        'daily_rate',
        'hourly_rate',
        'work_start',
        'work_end',
        'leave_balance',
        'shift_id',
        'rest_days',
        'department_id',
        'start_date',
        'working_days',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'work_start' => 'datetime',
        'work_end' => 'datetime',
        'basic_salary' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'rest_days' => 'array',
        'working_days' => 'array',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isHR(): bool
    {
        return $this->role === 'hr';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
    }

    public function dtrRecords()
    {
        return $this->hasMany(DTRRecord::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function changeShiftRequests()
    {
        return $this->hasMany(ChangeShiftRequest::class);
    }

    public function changeRestdayRequests()
    {
        return $this->hasMany(ChangeRestdayRequest::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    public function noBioRequests()
    {
        return $this->hasMany(NoBioRequest::class);
    }

    public function overtimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class);
    }
}
