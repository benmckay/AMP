<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments
     */
    public function index()
    {
        $departments = Department::withCount('templates')
            ->orderBy('name')
            ->paginate(15);
        
        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:departments,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $department = Department::create([
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully!');
    }

    /**
     * Display the specified department
     */
    public function show(Department $department)
    {
        $department->load(['templates', 'departmentUsers.roles']);
        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified department
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, Department $department)
    {
        if ($request->boolean('_delete')) {
            return $this->destroy($department);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:departments,code,' . $department->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully!');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        if ($department->templates()->exists()) {
            return back()->with('error', 'Cannot delete a department that still has templates.');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully!');
    }
}
