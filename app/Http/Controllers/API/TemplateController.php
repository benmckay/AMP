<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\Department;  
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    /**
     * Get all active templates
     */
    public function index(Request $request): JsonResponse
    {
        $query = Template::active()->with('department');
        
        // Filter by department if provided
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Search if query provided
        if ($request->has('query')) {
            $query->search($request->query);
        }
        
        $templates = $query->orderBy('mnemonic')->get();
        
        return response()->json([
            'success' => true,
            'data' => $templates,
            'total' => $templates->count(),
            'message' => 'Templates retrieved successfully'
        ]);
    }
    
    /**
     * Get a specific template
     */
    public function show(Template $template): JsonResponse
    {
        $template->load(['department', 'creator']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $template->id,
                'mnemonic' => $template->mnemonic,
                'name' => $template->name,
                'display_name' => $template->display_name,
                'full_display' => $template->full_display,
                'department' => [
                    'id' => $template->department->id,
                    'code' => $template->department->code,
                    'name' => $template->department->name
                ],
                'category' => $template->category,
                'description' => $template->description,
                'ehr_access_level' => $template->ehr_access_level,
                'ehr_module_access' => $template->ehr_module_access,
                'ehr_permissions' => $template->ehr_permissions,
                'requires_cos_approval' => $template->requires_cos_approval,
                'usage_count' => $template->usage_count,
                'is_active' => $template->is_active,
                'version' => $template->version,
                'created_at' => $template->created_at,
                'updated_at' => $template->updated_at
            ],
            'message' => 'Template details retrieved successfully'
        ]);
    }
    
    /**
     * Create a new template (ICT Admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mnemonic' => 'required|string|max:50|unique:templates,mnemonic',
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'ehr_access_level' => 'nullable|string|max:50',
            'ehr_module_access' => 'nullable|array',
            'ehr_permissions' => 'nullable|array',
            'requires_cos_approval' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $template = Template::create([
            'mnemonic' => $request->mnemonic,
            'name' => $request->name,
            'department_id' => $request->department_id,
            'category' => $request->category,
            'description' => $request->description,
            'ehr_access_level' => $request->ehr_access_level ?? 'standard',
            'ehr_module_access' => $request->ehr_module_access ?? [],
            'ehr_permissions' => $request->ehr_permissions ?? [],
            'requires_cos_approval' => $request->requires_cos_approval ?? false,
            'created_by' => $request->user()->id,
            'is_active' => true,
            'version' => 1
        ]);
        
        $template->load('department');
        
        return response()->json([
            'success' => true,
            'data' => $template,
            'message' => 'Template created successfully'
        ], 201);
    }
    
    /**
     * Update a template (ICT Admin only)
     */
    public function update(Request $request, Template $template): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mnemonic' => 'sometimes|required|string|max:50|unique:templates,mnemonic,' . $template->id,
            'name' => 'sometimes|required|string|max:255',
            'department_id' => 'sometimes|required|exists:departments,id',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'ehr_access_level' => 'nullable|string|max:50',
            'ehr_module_access' => 'nullable|array',
            'ehr_permissions' => 'nullable|array',
            'is_active' => 'boolean',
            'requires_cos_approval' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $template->update($request->only([
            'mnemonic', 'name', 'department_id', 'category', 'description',
            'ehr_access_level', 'ehr_module_access', 'ehr_permissions',
            'is_active', 'requires_cos_approval'
        ]));
        
        // Increment version on update
        $template->increment('version');
        
        $template->load('department');
        
        return response()->json([
            'success' => true,
            'data' => $template,
            'message' => 'Template updated successfully'
        ]);
    }
    
    /**
     * Soft delete a template (ICT Admin only)
     */
    public function destroy(Template $template): JsonResponse
    {
        // Check if template is in use
        if ($template->accessRequests()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete template that is in use by access requests'
            ], 422);
        }
        
        $template->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully'
        ]);
    }
    
    /**
     * Search templates by mnemonic or name
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->input('query', '');
        $departmentId = $request->input('department_id');
        
        $query = Template::active()->with('department');
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        if ($term) {
            $query->search($term);
        }
        
        $templates = $query->orderBy('mnemonic')->get();
        
        return response()->json([
            'success' => true,
            'data' => $templates,
            'total' => $templates->count(),
            'message' => 'Search results retrieved successfully'
        ]);
    }
    
    /**
     * Get template usage statistics
     */
    public function statistics(Template $template): JsonResponse
    {
        $stats = [
            'template' => [
                'id' => $template->id,
                'mnemonic' => $template->mnemonic,
                'name' => $template->name,
                'department' => $template->department->name
            ],
            'total_requests' => $template->accessRequests()->count(),
            'pending_requests' => $template->accessRequests()->where('status', 'pending')->count(),
            'approved_requests' => $template->accessRequests()->where('status', 'approved')->count(),
            'fulfilled_requests' => $template->accessRequests()->where('status', 'fulfilled')->count(),
            'rejected_requests' => $template->accessRequests()->where('status', 'rejected')->count(),
            'recent_requests' => $template->accessRequests()
                ->with('requester')
                ->latest()
                ->take(5)
                ->get()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Template statistics retrieved successfully'
        ]);
    }
}