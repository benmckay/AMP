<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Template extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'mnemonic',
        'name',
        'department_id',
        'category',
        'description',
        'ehr_access_level',
        'ehr_module_access',
        'ehr_permissions',
        'system_access',
        'permissions',
        'is_active',
        'requires_cos_approval',
        'created_by',
        'version'
    ];
    
    protected $casts = [
        'ehr_module_access' => 'array',
        'ehr_permissions' => 'array',
        'system_access' => 'array',
        'permissions' => 'array',
        'is_active' => 'boolean',
        'requires_cos_approval' => 'boolean',
    ];
    
    /**
     * Get the department this template belongs to
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    
    /**
     * Get all access requests using this template
     */
    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class);
    }
    
    /**
     * Get the user who created this template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Scope to get only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope to filter by department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
    
    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    
    /**
     * Scope to search templates
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('mnemonic', 'ILIKE', "%{$term}%")
              ->orWhere('name', 'ILIKE', "%{$term}%")
              ->orWhere('description', 'ILIKE', "%{$term}%");
        });
    }
    
    /**
     * Get display name with mnemonic
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->mnemonic} - {$this->name}";
    }
    
    /**
     * Get full display with department
     */
    public function getFullDisplayAttribute(): string
    {
        return "{$this->department->name}: {$this->mnemonic} - {$this->name}";
    }
    
    /**
     * Get usage count (how many times this template was used)
     */
    public function getUsageCountAttribute(): int
    {
        return $this->accessRequests()->count();
    }
}