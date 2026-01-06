<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Notifications\MyResetPassword; // Import your custom notification

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'position',
        'basic_salary',
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
        'pay_schedule',
        'face_embedding',
        'employee_id',
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
     * Attributes that should be appended when the model is serialized.
     *
     * @var list<string>
     */
    protected $appends = [
        'full_name',
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
        'face_embedding' => 'array',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MyResetPassword($token));
    }

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

    public function isHRManager(): bool
    {
        return in_array($this->role, ['admin', 'hr']);
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
    }
    public function noBioRequests()
    {
        return $this->hasMany(NoBioRequest::class);
    }

    public function overtimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    /**
     * Accessor to keep backwards compatibility for code still using `name`.
     */
    public function getNameAttribute($value): string
    {
        $fullName = trim(($this->attributes['first_name'] ?? $this->first_name ?? '') . ' ' . ($this->attributes['last_name'] ?? $this->last_name ?? ''));

        if ($fullName !== '') {
            return $fullName;
        }

        return $value ?? '';
    }

    /**
     * Computed full name for serialization/use in views.
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->attributes['first_name'] ?? $this->first_name ?? '') . ' ' . ($this->attributes['last_name'] ?? $this->last_name ?? ''));
    }
}
