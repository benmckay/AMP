<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\DepartmentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'departmentAssignments.department']);
        
        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->role($request->role);
        }
        
        // Filter by department
        if ($request->has('department_id') && $request->department_id) {
            $query->whereHas('departmentAssignments', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        
        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'ILIKE', "%{$request->search}%")
                  ->orWhere('email', 'ILIKE', "%{$request->search}%");
            });
        }
        
        $users = $query->latest()->paginate(15);
        $departments = Department::active()->orderBy('name')->get();
        
        return view('users.index', compact('users', 'departments'));
    }
    
    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('users.create');
    }
    
    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'payroll_number' => 'nullable|string|max:50',
            'password' => ['required', 'confirmed', Password::min(8)],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'payroll_number' => $validated['payroll_number'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);
        
        // Assign roles
        if ($request->has('roles')) {
            $user->assignRole($request->roles);
        }
        
        return redirect()->route('users.index')
            ->with('success', 'User created successfully!');
    }
    
    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['roles', 'departmentAssignments.department', 'accessRequests']);
        return view('users.show', compact('user'));
    }
    
    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $user->load('roles');
        return view('users.edit', compact('user'));
    }
    
    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'payroll_number' => 'nullable|string|max:50',
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);
        
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'payroll_number' => $validated['payroll_number'],
        ]);
        
        // Update password if provided
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }
        
        // Sync roles
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }
        
        return redirect()->route('users.index')
            ->with('success', 'User updated successfully!');
    }
    
    /**
     * Assign user to department
     */
    public function assignDepartment(Request $request, User $user)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'role' => 'required|in:requester,approver,both',
        ]);
        
        DepartmentUser::updateOrCreate(
            [
                'user_id' => $user->id,
                'department_id' => $validated['department_id'],
            ],
            [
                'role' => $validated['role'],
                'is_active' => true,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ]
        );
        
        return back()->with('success', 'User assigned to department successfully!');
    }
    
    /**
     * Remove user from department
     */
    public function removeDepartment(User $user, Department $department)
    {
        DepartmentUser::where('user_id', $user->id)
            ->where('department_id', $department->id)
            ->delete();
        
        return back()->with('success', 'User removed from department!');
    }
}