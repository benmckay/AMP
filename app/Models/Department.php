<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'head_user_id'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get all templates for this department
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }
    
    /**
     * Get only active templates
     */
    public function activeTemplates(): HasMany
    {
        return $this->templates()->where('is_active', true);
    }
    
    /**
     * Get department head
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }
    
    /**
     * Get all access requests for this department
     */
    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class);
    }
    
    /**
     * Get department user assignments
     */
    public function departmentUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'department_users');
    }
    
    /**
     * Get users assigned as requesters
     */
    public function requesters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'department_users')
                    ->wherePivotIn('role', ['requester', 'both'])
                    ->wherePivot('is_active', true);
    }
    
    /**
     * Get users assigned as approvers
     */
    public function approvers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'department_users')
                    ->wherePivotIn('role', ['approver', 'both'])
                    ->wherePivot('is_active', true);
    }
    
    /**
     * Scope to get only active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Check if user is a requester in this department
     */
    public function hasRequester(User $user): bool
    {
        return $this->requesters()->where('users.id', $user->id)->exists();
    }
    
    /**
     * Check if user is an approver in this department
     */
    public function hasApprover(User $user): bool
    {
        return $this->approvers()->where('users.id', $user->id)->exists();
    }
    
    /**
     * Get template count
     */
    public function getTemplateCountAttribute(): int
    {
        return $this->templates()->count();
    }
    
    /**
     * Get active template count
     */
    public function getActiveTemplateCountAttribute(): int
    {
        return $this->activeTemplates()->count();
    }
}