<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\TemplateController as WebTemplateController;
use App\Http\Controllers\DepartmentController as WebDepartmentController;
use App\Http\Controllers\UserController as WebUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    Route::get('/password/reset', function () {
        return view('auth.forgot-password');
    })->name('password.request');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/requester', [DashboardController::class, 'requester'])->name('dashboard.requester');
    Route::get('/dashboard/approver', [DashboardController::class, 'approver'])->name('dashboard.approver');
    Route::get('/dashboard/hr', [DashboardController::class, 'hr'])->name('dashboard.hr')->middleware('role:hr');
    Route::get('/dashboard/ict', [DashboardController::class, 'ict'])->name('dashboard.ict')->middleware('role:ict_admin');
    
    // Request Routes
    Route::prefix('requests')->group(function () {
        Route::get('/', [RequestController::class, 'index'])->name('requests.index');
        Route::get('/create', [RequestController::class, 'create'])->name('requests.create');
        Route::post('/', [RequestController::class, 'store'])->name('requests.store');
        Route::get('/{request}', [RequestController::class, 'show'])->name('requests.show');
        Route::get('/{request}/edit', [RequestController::class, 'edit'])->name('requests.edit');
        Route::put('/{request}', [RequestController::class, 'update'])->name('requests.update');
        
        // Approval Actions
        Route::post('/{request}/approve', [RequestController::class, 'approve'])->name('requests.approve');
        Route::post('/{request}/reject', [RequestController::class, 'reject'])->name('requests.reject');
        Route::post('/{request}/cancel', [RequestController::class, 'cancel'])->name('requests.cancel');
        
        // ICT Admin Actions
        Route::middleware('role:ict_admin')->group(function () {
            Route::get('/fulfillment/queue', [RequestController::class, 'fulfillmentQueue'])->name('requests.fulfillment-queue');
            Route::post('/{request}/fulfill', [RequestController::class, 'fulfill'])->name('requests.fulfill');
        });
        
        // HR Routes
        Route::middleware('role:hr')->group(function () {
            Route::get('/reactivations', [RequestController::class, 'reactivations'])->name('requests.reactivations');
            Route::get('/terminations', [RequestController::class, 'terminations'])->name('requests.terminations');
            Route::get('/create-termination', [RequestController::class, 'createTermination'])->name('requests.create-termination');
        });
    });
    
    // Browse Templates (All users)
    Route::get('/templates/browse', [WebTemplateController::class, 'browse'])->name('templates.browse');

    // Template Routes (ICT Admin only)
    Route::middleware('role:ict_admin')->prefix('templates')->group(function () {
        Route::get('/', [WebTemplateController::class, 'index'])->name('templates.index');
        Route::get('/create', [WebTemplateController::class, 'create'])->name('templates.create');
        Route::post('/', [WebTemplateController::class, 'store'])->name('templates.store');
        Route::get('/{template}', [WebTemplateController::class, 'show'])->whereNumber('template')->name('templates.show');
        Route::get('/{template}/edit', [WebTemplateController::class, 'edit'])->whereNumber('template')->name('templates.edit');
        Route::put('/{template}', [WebTemplateController::class, 'update'])->whereNumber('template')->name('templates.update');
        Route::delete('/{template}', [WebTemplateController::class, 'destroy'])->whereNumber('template')->name('templates.destroy');
    });
    
    // Department Routes (ICT Admin only)
    Route::middleware('role:ict_admin')->prefix('departments')->group(function () {
        Route::get('/', [WebDepartmentController::class, 'index'])->name('departments.index');
        Route::get('/create', [WebDepartmentController::class, 'create'])->name('departments.create');
        Route::post('/', [WebDepartmentController::class, 'store'])->name('departments.store');
        Route::get('/{department}', [WebDepartmentController::class, 'show'])->name('departments.show');
        Route::get('/{department}/edit', [WebDepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/{department}', [WebDepartmentController::class, 'update'])->name('departments.update');
    });
    
    // User Management Routes (ICT Admin only)
    Route::middleware('role:ict_admin')->prefix('users')->group(function () {
        Route::get('/', [WebUserController::class, 'index'])->name('users.index');
        Route::get('/create', [WebUserController::class, 'create'])->name('users.create');
        Route::post('/', [WebUserController::class, 'store'])->name('users.store');
        Route::get('/{user}', [WebUserController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [WebUserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [WebUserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [WebUserController::class, 'destroy'])->name('users.destroy');
        
        // Department Assignment
        Route::post('/{user}/assign-department', [WebUserController::class, 'assignDepartment'])->name('users.assign-department');
        Route::delete('/{user}/remove-department/{department}', [WebUserController::class, 'removeDepartment'])->name('users.remove-department');
    });
    
    // Approval Routes
    Route::prefix('approvals')->group(function () {
        Route::get('/pending', [RequestController::class, 'pendingApprovals'])->name('approvals.pending');
        Route::get('/history', [RequestController::class, 'approvalHistory'])->name('approvals.history');
    });
    
    // Profile & Settings
    Route::get('/profile', function () {
        return view('profile.show');
    })->name('profile');
    
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings');
    
    // Reports (ICT Admin and Admin only)
    Route::middleware('role:ict_admin,admin')->prefix('reports')->group(function () {
        Route::get('/', function () {
            return view('reports.index');
        })->name('reports.index');
    });
    
    // Audit Logs (ICT Admin, Admin, and Auditor only)
    Route::middleware('role:ict_admin,admin,auditor')->prefix('audit-logs')->group(function () {
        Route::get('/', function () {
            return view('audit-logs.index');
        })->name('audit-logs.index');
    });
});
