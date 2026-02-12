<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    /**
     * Display a listing of templates grouped by department
     */
    public function index(Request $request)
    {
        $query = Department::with(['templates' => function($q) use ($request) {
            if ($request->has('search')) {
                $q->where('name', 'ILIKE', "%{$request->search}%")
                  ->orWhere('mnemonic', 'ILIKE', "%{$request->search}%");
            }
        }])->withCount('templates');

        if ($request->has('department_id') && $request->department_id) {
            $query->where('id', $request->department_id);
        }

        $departments = Department::orderBy('name')->get();
        $templatesByDepartment = $query->orderBy('name')->get();

        return view('templates.index', compact('departments', 'templatesByDepartment'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create(Request $request)
    {
        $departments = Department::orderBy('name')->get();
        $selectedDepartmentId = $request->query('department');

        return view('templates.create', compact('departments', 'selectedDepartmentId'));
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mnemonic' => 'required|string|max:50|unique:templates,mnemonic',
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'ehr_access_level' => 'nullable|string|max:50',
            'requires_cos_approval' => 'boolean'
        ]);

        $template = Template::create([
            ...$validated,
            'created_by' => Auth::id(),
            'is_active' => true,
            'version' => 1
        ]);

        return redirect()->route('templates.show', $template->id)
            ->with('success', 'Template created successfully!');
    }

    /**
     * Display the specified template
     */
    public function show(Template $template)
    {
        $template->load(['department', 'creator']);
        return view('templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(Template $template)
    {
        $departments = Department::orderBy('name')->get();
        return view('templates.edit', compact('template', 'departments'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'mnemonic' => 'required|string|max:50|unique:templates,mnemonic,' . $template->id,
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'ehr_access_level' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'requires_cos_approval' => 'boolean'
        ]);

        $template->update($validated);
        $template->increment('version');

        return redirect()->route('templates.show', $template->id)
            ->with('success', 'Template updated successfully!');
    }

    /**
     * Remove the specified template
     */
    public function destroy(Template $template)
    {
        if ($template->accessRequests()->exists()) {
            return back()->with('error', 'Cannot delete template that is in use by access requests.');
        }

        $template->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template deleted successfully!');
    }

    /**
     * Browse templates for all users
     */
    public function browse(Request $request)
    {
        $query = Template::active()->with('department');

        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'ILIKE', "%{$request->search}%")
                  ->orWhere('mnemonic', 'ILIKE', "%{$request->search}%");
            });
        }

        $templates = $query->orderBy('name')->paginate(20);
        $departments = Department::orderBy('name')->get();

        return view('templates.browse', compact('templates', 'departments'));
    }
}
