<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    /**
     * Get all active departments
     */
    public function index(): JsonResponse
    {
        $departments = Department::active()
            ->withCount(['templates', 'activeTemplates'])
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $departments,
            'message' => 'Departments retrieved successfully'
        ]);
    }
    
    /**
     * Get a specific department with templates
     */
    public function show(Department $department): JsonResponse
    {
        $department->load(['activeTemplates', 'head', 'requesters', 'approvers']);
        
        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'Department details retrieved successfully'
        ]);
    }
    
    /**
     * Get templates for a specific department
     */
    public function templates(Department $department): JsonResponse
    {
        $templates = $department->activeTemplates()
            ->orderBy('mnemonic')
            ->get()
            ->map(function($template) {
                return [
                    'id' => $template->id,
                    'mnemonic' => $template->mnemonic,
                    'name' => $template->name,
                    'display_name' => $template->display_name,
                    'category' => $template->category,
                    'ehr_access_level' => $template->ehr_access_level,
                    'requires_cos_approval' => $template->requires_cos_approval,
                    'usage_count' => $template->usage_count
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => [
                'department' => [
                    'id' => $department->id,
                    'code' => $department->code,
                    'name' => $department->name
                ],
                'templates' => $templates,
                'total' => $templates->count()
            ],
            'message' => 'Department templates retrieved successfully'
        ]);
    }
    
    /**
     * Get departments where user is a requester
     */
    public function myRequesterDepartments(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $departments = Department::whereHas('departmentUsers', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('role', ['requester', 'both'])
                  ->where('is_active', true);
        })
        ->with(['activeTemplates'])
        ->get();
        
        return response()->json([
            'success' => true,
            'data' => $departments,
            'message' => 'Your requester departments retrieved successfully'
        ]);
    }
    
    /**
     * Get departments where user is an approver
     */
    public function myApproverDepartments(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $departments = Department::whereHas('departmentUsers', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('role', ['approver', 'both'])
                  ->where('is_active', true);
        })
        ->withCount(['accessRequests' => function($query) {
            $query->where('status', 'pending');
        }])
        ->get();
        
        return response()->json([
            'success' => true,
            'data' => $departments,
            'message' => 'Your approver departments retrieved successfully'
        ]);
    }
    
    /**
     * Search departments
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->input('query', '');
        
        $departments = Department::active()
            ->where(function($query) use ($term) {
                $query->where('name', 'ILIKE', "%{$term}%")
                      ->orWhere('code', 'ILIKE', "%{$term}%")
                      ->orWhere('description', 'ILIKE', "%{$term}%");
            })
            ->withCount('activeTemplates')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $departments,
            'message' => 'Search results retrieved successfully'
        ]);
    }
}