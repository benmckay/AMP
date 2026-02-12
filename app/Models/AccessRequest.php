<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccessRequest extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'request_number',
        'requester_id',
        'requester_department_id',
        'template_id',
        'department_id',
        'system_id',
        'request_type',
        'status',
        'payroll_number',
        'first_name',
        'last_name',
        'email',
        'username',
        'job_title',
        'provider_group',
        'provider_type',
        'specialty',
        'service',
        'admitting',
        'ordering_physician',
        'sign_orders',
        'cosign_orders',
        'justification',
        'priority',
        'approval_comments',
        'fulfillment_notes',
        'cancellation_reason'
    ];
    
    protected $casts = [
        'admitting' => 'boolean',
        'ordering_physician' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
    
    /**
     * Boot method - auto-generate request number
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($request) {
            if (empty($request->request_number)) {
                $request->request_number = self::generateRequestNumber();
            }
        });
    }
    
    /**
     * Get the requester (user who submitted the request)
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
    
    /**
     * Get the requester's department
     */
    public function requesterDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'requester_department_id');
    }
    
    /**
     * Get the selected template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
    
    /**
     * Get the target department (for the new user)
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    
    /**
     * Get the system
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }
    
    /**
     * Get who approved the request
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Get who fulfilled the request
     */
    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }
    
    /**
     * Get who cancelled the request
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
    
    /**
     * Get attached documents
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
    
    /**
     * Get approval history
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(RequestApproval::class, 'request_id');
    }
    
    /**
     * Scope: Pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope: Approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope: Fulfilled requests
     */
    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }
    
    /**
     * Scope: Filter by department
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
    
    /**
     * Scope: Filter by requester department
     */
    public function scopeFromDepartment($query, $departmentId)
    {
        return $query->where('requester_department_id', $departmentId);
    }
    
    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope: Search requests
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('request_number', 'ILIKE', "%{$term}%")
              ->orWhere('first_name', 'ILIKE', "%{$term}%")
              ->orWhere('last_name', 'ILIKE', "%{$term}%")
              ->orWhere('email', 'ILIKE', "%{$term}%")
              ->orWhere('payroll_number', 'ILIKE', "%{$term}%");
        });
    }
    
    /**
     * Get full name of target user
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
    
    /**
     * Get template display name
     */
    public function getTemplateDisplayAttribute(): string
    {
        return $this->template ? $this->template->display_name : 'N/A';
    }
    
    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
    
    /**
     * Check if request is fulfilled
     */
    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }
    
    /**
     * Approve the request
     */
    public function approve(User $approver, ?string $comments = null): bool
    {
        $this->status = 'approved';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->approval_comments = $comments;
        
        return $this->save();
    }
    
    /**
     * Reject the request
     */
    public function reject(User $approver, string $reason): bool
    {
        $this->status = 'rejected';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->approval_comments = $reason;
        
        return $this->save();
    }
    
    /**
     * Fulfill the request
     */
    public function fulfill(User $fulfiller, ?string $notes = null): bool
    {
        $this->status = 'fulfilled';
        $this->fulfilled_by = $fulfiller->id;
        $this->fulfilled_at = now();
        $this->fulfillment_notes = $notes;
        
        return $this->save();
    }
    
    /**
     * Cancel the request
     */
    public function cancel(User $canceller, string $reason): bool
    {
        $this->status = 'cancelled';
        $this->cancelled_by = $canceller->id;
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        
        return $this->save();
    }
    
    /**
     * Generate unique request number
     */
    public static function generateRequestNumber(): string
    {
        $year = date('Y');
        $lastRequest = self::whereYear('created_at', $year)
                          ->orderBy('id', 'desc')
                          ->first();
        
        $nextNumber = $lastRequest ? ((int) substr($lastRequest->request_number, -4)) + 1 : 1;
        
        return 'REQ-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get processing time in days
     */
    public function getProcessingTimeAttribute(): ?int
    {
        if ($this->fulfilled_at) {
            return $this->submitted_at->diffInDays($this->fulfilled_at);
        }
        
        return null;
    }
    
    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'fulfilled' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            default => 'light'
        };
    }
}