<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AccessRequest;
use App\Models\Department;
use App\Models\Template;

class DashboardController extends Controller
{
    /**
     * Main dashboard - redirects based on role
     */
    public function index()
    {
        $user = auth()->user();
        
        // Check roles and redirect accordingly
        if ($user->hasRole('admin') || $user->hasRole('ict_admin')) {
            return redirect()->route('dashboard.ict');
        } elseif ($user->hasRole('hr')) {
            return redirect()->route('dashboard.hr');
        } elseif ($this->userIsApprover($user)) {
            return redirect()->route('dashboard.approver');
        } elseif ($this->userIsRequester($user)) {
            return redirect()->route('dashboard.requester');
        }
        
        // Default fallback
        return view('dashboard.requester');
    }
    
    /**
     * Requester Dashboard
     */
    public function requester()
    {
        $user = auth()->user();
        
        // Get user's departments where they are requesters
        $departments = DB::table('department_users')
            ->join('departments', 'departments.id', '=', 'department_users.department_id')
            ->where('department_users.user_id', $user->id)
            ->whereIn('department_users.role', ['requester', 'both'])
            ->where('department_users.is_active', true)
            ->select('departments.*')
            ->get();
        
        // Get user's requests
        $myRequests = AccessRequest::where('requester_id', $user->id)
            ->with(['template', 'department'])
            ->latest('submitted_at')
            ->take(10)
            ->get();
        
        // Statistics
        $stats = [
            'total_requests' => AccessRequest::where('requester_id', $user->id)->count(),
            'pending' => AccessRequest::where('requester_id', $user->id)->where('status', 'pending')->count(),
            'approved' => AccessRequest::where('requester_id', $user->id)->where('status', 'approved')->count(),
            'fulfilled' => AccessRequest::where('requester_id', $user->id)->where('status', 'fulfilled')->count(),
        ];
        
        return view('dashboard.requester', compact('departments', 'myRequests', 'stats'));
    }
    
    /**
     * Approver Dashboard
     */
    public function approver()
    {
        $user = auth()->user();
        
        // Get departments where user is an approver
        $departmentIds = DB::table('department_users')
            ->where('user_id', $user->id)
            ->whereIn('role', ['approver', 'both'])
            ->where('is_active', true)
            ->pluck('department_id');
        
        // Get pending approvals
        $pendingApprovals = AccessRequest::whereIn('requester_department_id', $departmentIds)
            ->where('status', 'pending')
            ->with(['requester', 'template', 'department'])
            ->latest('submitted_at')
            ->paginate(15);
        
        // Statistics
        $stats = [
            'pending_approvals' => AccessRequest::whereIn('requester_department_id', $departmentIds)
                ->where('status', 'pending')->count(),
            'approved_today' => AccessRequest::whereIn('requester_department_id', $departmentIds)
                ->where('status', 'approved')
                ->whereDate('approved_at', today())->count(),
            'approved_this_month' => AccessRequest::whereIn('requester_department_id', $departmentIds)
                ->where('status', 'approved')
                ->whereMonth('approved_at', now()->month)->count(),
        ];
        
        return view('dashboard.approver', compact('pendingApprovals', 'stats'));
    }
    
    /**
     * HR Dashboard
     */
    public function hr()
    {
        // Get reactivation and termination requests
        $reactivationRequests = AccessRequest::where('request_type', 'reactivation')
            ->where('status', 'pending')
            ->with(['requester', 'template'])
            ->latest('submitted_at')
            ->paginate(10);
        
        $terminationRequests = AccessRequest::where('request_type', 'termination')
            ->where('status', 'pending')
            ->with(['requester', 'template'])
            ->latest('submitted_at')
            ->paginate(10);
        
        // Statistics
        $stats = [
            'pending_reactivations' => AccessRequest::where('request_type', 'reactivation')
                ->where('status', 'pending')->count(),
            'pending_terminations' => AccessRequest::where('request_type', 'termination')
                ->where('status', 'pending')->count(),
            'completed_this_month' => AccessRequest::whereIn('request_type', ['reactivation', 'termination'])
                ->where('status', 'fulfilled')
                ->whereMonth('fulfilled_at', now()->month)->count(),
        ];
        
        return view('dashboard.hr', compact('reactivationRequests', 'terminationRequests', 'stats'));
    }
    
    /**
     * ICT Admin Dashboard
     */
    public function ict()
    {
        // Get fulfillment queue
        $fulfillmentQueue = AccessRequest::where('status', 'approved')
            ->with(['requester', 'template.department', 'approvedBy'])
            ->orderBy('approved_at')
            ->paginate(15);
        
        // Get recent fulfilled
        $recentFulfilled = AccessRequest::where('status', 'fulfilled')
            ->with(['template', 'fulfilledBy'])
            ->latest('fulfilled_at')
            ->take(10)
            ->get();
        
        // Statistics
        $stats = [
            'awaiting_fulfillment' => AccessRequest::where('status', 'approved')->count(),
            'fulfilled_today' => AccessRequest::where('status', 'fulfilled')
                ->whereDate('fulfilled_at', today())->count(),
            'fulfilled_this_month' => AccessRequest::where('status', 'fulfilled')
                ->whereMonth('fulfilled_at', now()->month)->count(),
            'total_templates' => Template::count(),
            'active_templates' => Template::where('is_active', true)->count(),
        ];
        
        // Department statistics
        $departmentStats = Department::withCount([
            'accessRequests',
            'templates'
        ])->get();
        
        return view('dashboard.ict', compact('fulfillmentQueue', 'recentFulfilled', 'stats', 'departmentStats'));
    }
    
    /**
     * Check if user is a requester
     */
    private function userIsRequester($user): bool
    {
        return DB::table('department_users')
            ->where('user_id', $user->id)
            ->whereIn('role', ['requester', 'both'])
            ->where('is_active', true)
            ->exists();
    }
    
    /**
     * Check if user is an approver
     */
    private function userIsApprover($user): bool
    {
        return DB::table('department_users')
            ->where('user_id', $user->id)
            ->whereIn('role', ['approver', 'both'])
            ->where('is_active', true)
            ->exists();
    }
}