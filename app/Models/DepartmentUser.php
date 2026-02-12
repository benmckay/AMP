<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepartmentUser extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'department_id',
        'role',
        'is_active',
        'assigned_by',
        'assigned_at'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
    ];
    
    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    
    /**
     * Get who assigned this user
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    
    /**
     * Scope to get only active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope to get requesters
     */
    public function scopeRequesters($query)
    {
        return $query->whereIn('role', ['requester', 'both']);
    }
    
    /**
     * Scope to get approvers
     */
    public function scopeApprovers($query)
    {
        return $query->whereIn('role', ['approver', 'both']);
    }
    
    /**
     * Check if user can request in this department
     */
    public function canRequest(): bool
    {
        return in_array($this->role, ['requester', 'both']) && $this->is_active;
    }
    
    /**
     * Check if user can approve in this department
     */
    public function canApprove(): bool
    {
        return in_array($this->role, ['approver', 'both']) && $this->is_active;
    }
}