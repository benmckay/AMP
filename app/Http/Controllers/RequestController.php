<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use App\Models\Department;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * Display a listing of user's requests
     */
    public function index()
    {
        $requests = AccessRequest::where('requester_id', Auth::id())
            ->with(['template', 'department', 'system'])
            ->latest('submitted_at')
            ->paginate(15);
        
        return view('requests.index', compact('requests'));
    }
    
    /**
     * Show the form for creating a new request
     */
    public function create()
    {
        // Get departments where user is a requester
        $departments = DB::table('department_users')
            ->join('departments', 'departments.id', '=', 'department_users.department_id')
            ->where('department_users.user_id', Auth::id())
            ->whereIn('department_users.role', ['requester', 'both'])
            ->where('department_users.is_active', true)
            ->select('departments.*')
            ->get();
        
        return view('requests.create', compact('departments'));
    }
    
    /**
     * Store a newly created request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'department_id' => 'nullable|exists:departments,id',
            'request_type' => 'required|in:new_access,additional_rights,reactivation,termination',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email',
            'payroll_number' => 'nullable|string|max:50',
            'username' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:150',
            'justification' => 'required|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ]);
        
        // Get requester's department
        $requesterDepartment = DB::table('department_users')
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();
        
        $accessRequest = AccessRequest::create([
            'requester_id' => Auth::id(),
            'requester_department_id' => $requesterDepartment?->department_id,
            'template_id' => $validated['template_id'],
            'department_id' => $validated['department_id'],
            'system_id' => 1, // Default to EHR
            'request_type' => $validated['request_type'],
            'status' => 'pending',
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'payroll_number' => $validated['payroll_number'],
            'username' => $validated['username'],
            'job_title' => $validated['job_title'],
            'justification' => $validated['justification'],
            'priority' => $validated['priority'] ?? 'normal',
        ]);
        
        return redirect()->route('requests.show', $accessRequest->id)
            ->with('success', 'Access request submitted successfully!');
    }
    
    /**
     * Display the specified request
     */
    public function show(AccessRequest $request)
    {
        $request->load([
            'requester',
            'requesterDepartment',
            'template.department',
            'department',
            'system',
            'approvedBy',
            'fulfilledBy',
            'cancelledBy'
        ]);
        
        return view('requests.show', compact('request'));
    }
    
    /**
     * Approve a request
     */
    public function approve(Request $request, AccessRequest $accessRequest)
    {
        $accessRequest->approve(Auth::user(), $request->input('comments'));
        
        // Log approval
        DB::table('request_approvals')->insert([
            'request_id' => $accessRequest->id,
            'approver_id' => Auth::id(),
            'action' => 'approved',
            'comments' => $request->input('comments'),
            'created_at' => now()
        ]);
        
        return back()->with('success', 'Request approved successfully!');
    }
    
    /**
     * Reject a request
     */
    public function reject(Request $request, AccessRequest $accessRequest)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);
        
        $accessRequest->reject(Auth::user(), $request->reason);
        
        // Log rejection
        DB::table('request_approvals')->insert([
            'request_id' => $accessRequest->id,
            'approver_id' => Auth::id(),
            'action' => 'rejected',
            'comments' => $request->reason,
            'created_at' => now()
        ]);
        
        return back()->with('success', 'Request rejected.');
    }
    
    /**
     * Fulfill a request (ICT Admin)
     */
    public function fulfill(Request $request, AccessRequest $accessRequest)
    {
        $accessRequest->fulfill(Auth::user(), $request->input('notes'));
        
        return back()->with('success', 'Request marked as fulfilled!');
    }
    
    /**
     * Fulfillment queue (ICT Admin)
     */
    public function fulfillmentQueue()
    {
        $requests = AccessRequest::where('status', 'approved')
            ->with(['requester', 'template.department', 'approvedBy'])
            ->orderBy('approved_at')
            ->paginate(15);
        
        return view('requests.fulfillment-queue', compact('requests'));
    }
    
    /**
     * Pending approvals
     */
    public function pendingApprovals()
    {
        // Get departments where user is an approver
        $departmentIds = DB::table('department_users')
            ->where('user_id', Auth::id())
            ->whereIn('role', ['approver', 'both'])
            ->where('is_active', true)
            ->pluck('department_id');
        
        $requests = AccessRequest::whereIn('requester_department_id', $departmentIds)
            ->where('status', 'pending')
            ->with(['requester', 'template', 'department'])
            ->latest('submitted_at')
            ->paginate(15);
        
        return view('approvals.pending', compact('requests'));
    }
}