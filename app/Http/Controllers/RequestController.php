<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use App\Models\Department;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RequestController extends Controller
{
    /**
     * Get active templates for a requester department (web session endpoint).
     */
    public function templatesByDepartment(Department $department)
    {
        $canRequestInDepartment = DB::table('department_users')
            ->where('user_id', Auth::id())
            ->where('department_id', $department->id)
            ->whereIn('role', ['requester', 'both'])
            ->where('is_active', true)
            ->exists();

        if (!$canRequestInDepartment) {
            return response()->json([
                'message' => 'You are not authorized to request access for this department.',
            ], 403);
        }

        $templates = Template::active()
            ->where('department_id', $department->id)
            ->orderBy('mnemonic')
            ->get([
                'id',
                'mnemonic',
                'name',
                'department_id',
                'ehr_access_level',
                'requires_cos_approval',
            ]);

        return response()->json([
            'data' => [
                'department' => [
                    'id' => $department->id,
                    'name' => $department->name,
                ],
                'templates' => $templates,
                'total' => $templates->count(),
            ],
        ]);
    }

    /**
     * Get template details for request form preview (web session endpoint).
     */
    public function templateDetails(Template $template)
    {
        $canRequestInDepartment = DB::table('department_users')
            ->where('user_id', Auth::id())
            ->where('department_id', $template->department_id)
            ->whereIn('role', ['requester', 'both'])
            ->where('is_active', true)
            ->exists();

        if (!$canRequestInDepartment) {
            return response()->json([
                'message' => 'You are not authorized to view this template.',
            ], 403);
        }

        return response()->json([
            'data' => [
                'id' => $template->id,
                'mnemonic' => $template->mnemonic,
                'name' => $template->name,
                'display_name' => $template->display_name,
                'ehr_access_level' => $template->ehr_access_level,
                'requires_cos_approval' => $template->requires_cos_approval,
            ],
        ]);
    }

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

        $departmentIds = $departments->pluck('id')->all();

        $templatesByDepartment = Template::active()
            ->whereIn('department_id', $departmentIds)
            ->orderBy('mnemonic')
            ->get([
                'id',
                'mnemonic',
                'name',
                'department_id',
                'ehr_access_level',
                'requires_cos_approval',
            ])
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'mnemonic' => $template->mnemonic,
                    'name' => $template->name,
                    'department_id' => $template->department_id,
                    'display_name' => "{$template->mnemonic} - {$template->name}",
                    'ehr_access_level' => $template->ehr_access_level,
                    'requires_cos_approval' => (bool) $template->requires_cos_approval,
                ];
            })
            ->groupBy('department_id')
            ->map(fn ($items) => array_values($items->all()))
            ->toArray();

        return view('requests.create', compact('departments', 'templatesByDepartment'));
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
    public function show(AccessRequest $accessRequest)
    {
        $accessRequest->load([
            'requester',
            'requesterDepartment',
            'template.department',
            'department',
            'system',
            'approvedBy',
            'fulfilledBy',
            'cancelledBy'
        ]);
        
        return view('requests.show', ['request' => $accessRequest]);
    }

    /**
     * Show form to edit a request
     */
    public function edit(AccessRequest $accessRequest)
    {
        if ($accessRequest->requester_id !== Auth::id() && Auth::user()?->role !== 'ict_admin') {
            abort(403);
        }

        $departments = Department::orderBy('name')->get();
        $templates = Template::where('is_active', true)->orderBy('name')->get();

        return view('requests.edit', ['request' => $accessRequest, 'departments' => $departments, 'templates' => $templates]);
    }

    /**
     * Update an existing request
     */
    public function update(Request $requestData, AccessRequest $accessRequest)
    {
        if ($accessRequest->requester_id !== Auth::id() && Auth::user()?->role !==('ict_admin')) {
            abort(403);
        }

        if (!in_array($accessRequest->status, ['pending', 'rejected'], true)) {
            return back()->with('error', 'Only pending or rejected requests can be updated.');
        }

        $validated = $requestData->validate([
            'template_id' => 'required|exists:templates,id',
            'department_id' => 'nullable|exists:departments,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email',
            'payroll_number' => 'nullable|string|max:50',
            'username' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:150',
            'justification' => 'required|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ]);

        $accessRequest->update($validated);

        return redirect()->route('requests.show', $accessRequest->id)
            ->with('success', 'Request updated successfully.');
    }
    
    /**
     * Approve a request
     */
    public function approve(Request $request, AccessRequest $accessRequest)
    {
        Log::info('Approve method called', [
            'request_id' => $accessRequest?->id ?? 'null',
            'exists' => $accessRequest?->exists ?? false,
            'status' => $accessRequest?->status ?? 'null',
        ]);
        
        try {
            if ($accessRequest->status !== 'pending') {
                return back()->with('error', 'This request has already been processed.');
            }
            
            $accessRequest->approve(Auth::user(), $request->input('comments'));
            
            // Log approval
            DB::table('request_approvals')->insert([
                'request_id' => $accessRequest->request_id,
                'approver_id' => Auth::id(),
                'action' => 'approved',
                'comments' => $request->input('comments'),
                'created_at' => now()
            ]);
            
            return back()->with('success', 'Request approved successfully!');
        } catch (\Exception $e) {
            Log::error('Error in approve method', [
                'error' => $e->getMessage(),
                'request_id' => $accessRequest?->id ?? 'null',
            ]);
            return back()->with('error', 'An error occurred while approving the request: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject a request
     */
    public function reject(Request $request, AccessRequest $accessRequest)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);
        
        try {
            if ($accessRequest->status !== 'pending') {
                return back()->with('error', 'This request has already been processed.');
            }
            
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
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while rejecting the request: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a request
     */
    public function cancel(Request $request, AccessRequest $accessRequest)
    {
        if ($accessRequest->requester_id !== Auth::id() && Auth::user()?->role !== 'ict_admin') {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $accessRequest->cancel(Auth::user(), $validated['reason']);

        return back()->with('success', 'Request cancelled successfully.');
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

    /**
     * Approval history
     */
    public function approvalHistory()
    {
        $requests = AccessRequest::where('approved_by', Auth::id())
            ->with(['requester', 'template', 'department'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('approved_at')
            ->paginate(15);

        return view('approvals.history', compact('requests'));
    }

    /**
     * HR reactivation queue
     */
    public function reactivations()
    {
        $requests = AccessRequest::where('request_type', 'reactivation')
            ->where('status', 'pending')
            ->with(['requester', 'template', 'department'])
            ->latest('submitted_at')
            ->paginate(15);

        return view('requests.reactivations', compact('requests'));
    }

    /**
     * HR termination queue
     */
    public function terminations()
    {
        $requests = AccessRequest::where('request_type', 'termination')
            ->where('status', 'pending')
            ->with(['requester', 'template', 'department'])
            ->latest('submitted_at')
            ->paginate(15);

        return view('requests.terminations', compact('requests'));
    }

    /**
     * Show form for creating a termination request
     */
    public function createTermination()
    {
        $departments = Department::orderBy('name')->get();
        $templates = Template::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('requests.create-termination', compact('departments', 'templates'));
    }
}
