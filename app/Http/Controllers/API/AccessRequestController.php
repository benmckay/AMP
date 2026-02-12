<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\Template;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AccessRequestController extends Controller
{
    /**
     * Get all access requests (with filtering)
     */
    public function index(Request $request): JsonResponse
    {
        $query = AccessRequest::with([
            'requester', 
            'requesterDepartment', 
            'template', 
            'department', 
            'system'
        ]);
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        // Filter by requester
        if ($request->has('requester_id')) {
            $query->where('requester_id', $request->requester_id);
        }
        
        // Filter by request type
        if ($request->has('request_type')) {
            $query->where('request_type', $request->request_type);
        }
        
        // Search
        if ($request->has('search')) {
            $query->search($request->search);
        }
        
        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('submitted_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('submitted_at', '<=', $request->to_date);
        }
        
        $requests = $query->latest('submitted_at')
            ->paginate($request->input('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $requests,
            'message' => 'Access requests retrieved successfully'
        ]);
    }
    
    /**
     * Get a specific access request
     */
    public function show(AccessRequest $accessRequest): JsonResponse
    {
        $accessRequest->load([
            'requester',
            'requesterDepartment',
            'template.department',
            'department',
            'system',
            'approvedBy',
            'fulfilledBy',
            'cancelledBy',
            'documents',
            'approvals.approver'
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $accessRequest,
            'message' => 'Access request details retrieved successfully'
        ]);
    }
    
    /**
     * Create a new access request
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:templates,id',
            'department_id' => 'nullable|exists:departments,id',
            'request_type' => 'required|in:new_access,additional_rights,reactivation,termination',
            'payroll_number' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'username' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:150',
            'justification' => 'required|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            
            // COS-specific fields (conditional)
            'provider_group' => 'nullable|string|max:100',
            'provider_type' => 'nullable|string|max:100',
            'specialty' => 'nullable|string|max:100',
            'service' => 'nullable|string|max:100',
            'admitting' => 'nullable|boolean',
            'ordering_physician' => 'nullable|boolean',
            'sign_orders' => 'nullable|in:orders,reports,both,neither',
            'cosign_orders' => 'nullable|in:orders,reports,both,neither',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        // Get requester's department
        $requesterDepartment = DB::table('department_users')
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();
        
        // Create the access request
        $accessRequest = AccessRequest::create([
            'requester_id' => $request->user()->id,
            'requester_department_id' => $requesterDepartment?->department_id,
            'template_id' => $request->template_id,
            'department_id' => $request->department_id,
            'system_id' => $request->system_id ?? 1, // Default to EHR
            'request_type' => $request->request_type,
            'status' => 'pending',
            'payroll_number' => $request->payroll_number,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'username' => $request->username,
            'job_title' => $request->job_title,
            'justification' => $request->justification,
            'priority' => $request->priority ?? 'normal',
            
            // COS fields
            'provider_group' => $request->provider_group,
            'provider_type' => $request->provider_type,
            'specialty' => $request->specialty,
            'service' => $request->service,
            'admitting' => $request->admitting,
            'ordering_physician' => $request->ordering_physician,
            'sign_orders' => $request->sign_orders,
            'cosign_orders' => $request->cosign_orders,
        ]);
        
        $accessRequest->load(['template', 'department', 'requester']);
        
        // TODO: Send notification to approvers
        
        return response()->json([
            'success' => true,
            'data' => $accessRequest,
            'message' => 'Access request submitted successfully'
        ], 201);
    }
    
    /**
     * Update an access request (only if pending)
     */
    public function update(Request $request, AccessRequest $accessRequest): JsonResponse
    {
        // Only allow updates if request is pending
        if (!$accessRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be updated'
            ], 422);
        }
        
        // Only allow requester or admin to update
        if ($accessRequest->requester_id !== $request->user()->id && !$request->user()->hasRole('ict_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this request'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'template_id' => 'sometimes|required|exists:templates,id',
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|max:255',
            'justification' => 'sometimes|required|string',
            'priority' => 'sometimes|in:low,normal,high,urgent',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $accessRequest->update($request->only([
            'template_id', 'first_name', 'last_name', 'email',
            'username', 'job_title', 'justification', 'priority',
            'provider_group', 'provider_type', 'specialty', 'service',
            'admitting', 'ordering_physician', 'sign_orders', 'cosign_orders'
        ]));
        
        $accessRequest->load(['template', 'department', 'requester']);
        
        return response()->json([
            'success' => true,
            'data' => $accessRequest,
            'message' => 'Access request updated successfully'
        ]);
    }
    
    /**
     * Approve an access request
     */
    public function approve(Request $request, AccessRequest $accessRequest): JsonResponse
    {
        if (!$accessRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be approved'
            ], 422);
        }
        
        // Check if user is an approver in the department
        $isApprover = DB::table('department_users')
            ->where('user_id', $request->user()->id)
            ->where('department_id', $accessRequest->requester_department_id)
            ->whereIn('role', ['approver', 'both'])
            ->where('is_active', true)
            ->exists();
        
        if (!$isApprover && !$request->user()->hasRole('ict_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to approve this request'
            ], 403);
        }
        
        $accessRequest->approve($request->user(), $request->input('comments'));
        
        // Log approval
        DB::table('request_approvals')->insert([
            'request_id' => $accessRequest->id,
            'approver_id' => $request->user()->id,
            'action' => 'approved',
            'comments' => $request->input('comments'),
            'created_at' => now()
        ]);
        
        // TODO: Send notification to ICT Admin for fulfillment
        
        return response()->json([
            'success' => true,
            'data' => $accessRequest->fresh(['approvedBy']),
            'message' => 'Access request approved successfully'
        ]);
    }
    
    /**
     * Reject an access request
     */
    public function reject(Request $request, AccessRequest $accessRequest): JsonResponse
    {
        if (!$accessRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be rejected'
            ], 422);
        }
        
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Rejection reason is required'
            ], 422);
        }
        
        // Check if user is an approver
        $isApprover = DB::table('department_users')
            ->where('user_id', $request->user()->id)
            ->where('department_id', $accessRequest->requester_department_id)
            ->whereIn('role', ['approver', 'both'])
            ->where('is_active', true)
            ->exists();
        
        if (!$isApprover && !$request->user()->hasRole('ict_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reject this request'
            ], 403);
        }
        
        $accessRequest->reject($request->user(), $request->reason);
        
        // Log rejection
        DB::table('request_approvals')->insert([
            'request_id' => $accessRequest->id,
            'approver_id' => $request->user()->id,
            'action' => 'rejected',
            'comments' => $request->reason,
            'created_at' => now()
        ]);
        
        // TODO: Send notification to requester
        
        return response()->json([
            'success' => true,
            'data' => $accessRequest->fresh(['approvedBy']),
            'message' => 'Access request rejected'
        ]);
    }
    
    /**
     * Fulfill an access request (ICT Admin only)
     */
    public function fulfill(Request $request, AccessRequest $accessRequest): JsonResponse
    {
        if (!$accessRequest->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Only approved requests can be fulfilled'
            ], 422);
        }
        
        $accessRequest->fulfill($request->user(), $request->input('notes'));
        
        // TODO: Send notification to requester
        
        return response()->json([
            'success' => true,
            'data' => $accessRequest->fresh(['fulfilledBy']),
            'message' => 'Access request fulfilled successfully'
        ]);
    }
    
    /**
     * Cancel an access request
     */
    public function cancel(Request $request, AccessRequest $accessRequest): JsonResponse
    {
        if ($accessRequest->isFulfilled()) {
            return response()->json([
                'success' => false,
                'message' => 'Fulfilled requests cannot be cancelled'
            ], 422);
        }
        
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Cancellation reason is required'
            ], 422);
        }
        
        // Only requester or admin can cancel
        if ($accessRequest->requester_id !== $request->user()->id && !$request->user()->hasRole('ict_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this request'
            ], 403);
        }
        
        $accessRequest->cancel($request->user(), $request->reason);
        
        return response()->json([
            'success' => true,
            'data' => $accessRequest->fresh(['cancelledBy']),
            'message' => 'Access request cancelled'
        ]);
    }
    
    /**
     * Get requests for current user (requester view)
     */
    public function myRequests(Request $request): JsonResponse
    {
        $requests = AccessRequest::where('requester_id', $request->user()->id)
            ->with(['template', 'department', 'approvedBy', 'fulfilledBy'])
            ->latest('submitted_at')
            ->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $requests,
            'message' => 'Your requests retrieved successfully'
        ]);
    }
    
    /**
     * Get pending requests for approval (approver view)
     */
    public function pendingApprovals(Request $request): JsonResponse
    {
        // Get departments where user is an approver
        $departmentIds = DB::table('department_users')
            ->where('user_id', $request->user()->id)
            ->whereIn('role', ['approver', 'both'])
            ->where('is_active', true)
            ->pluck('department_id');
        
        $requests = AccessRequest::whereIn('requester_department_id', $departmentIds)
            ->where('status', 'pending')
            ->with(['requester', 'template', 'department'])
            ->latest('submitted_at')
            ->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $requests,
            'message' => 'Pending approvals retrieved successfully'
        ]);
    }
    
    /**
     * Get approved requests awaiting fulfillment (ICT Admin view)
     */
    public function fulfillmentQueue(Request $request): JsonResponse
    {
        $requests = AccessRequest::where('status', 'approved')
            ->with(['requester', 'template.department', 'approvedBy'])
            ->orderBy('approved_at')
            ->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $requests,
            'message' => 'Fulfillment queue retrieved successfully'
        ]);
    }
}