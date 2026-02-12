<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class System extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get all access requests for this system
     */
    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class);
    }
    
    /**
     * Scope to get only active systems
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}