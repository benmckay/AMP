<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Auditable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'payroll_number',
        'department',
        'preferred_timezone',
        'preferred_language',
        'theme',
        'notify_security_alerts',
        'notify_request_updates',
        'notify_weekly_summary',
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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notify_security_alerts' => 'boolean',
            'notify_request_updates' => 'boolean',
            'notify_weekly_summary' => 'boolean',
        ];
    }

    public function accessRequests()
    {
        return $this->hasMany(AccessRequest::class, 'requester_id');
    }

    public function approvals()
    {
        return $this->hasMany(RequestApproval::class, 'approver_id');
    }

    public function templates()
    {
        return $this->belongsToMany(Template::class, 'user_templates');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function otpCodes()
    {
        return $this->hasMany(OtpCode::class);
    }

    /**
     * Department assignment rows for this user (pivot model records).
     */
    public function departmentAssignments(): HasMany
    {
        return $this->hasMany(DepartmentUser::class, 'user_id');
    }

    /**
     * Departments this user belongs to.
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_users')
            ->withPivot(['role', 'is_active', 'assigned_by', 'assigned_at']);
    }
}
