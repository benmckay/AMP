<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\OtpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AdminInsightsController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TemplateController as WebTemplateController;
use App\Http\Controllers\DepartmentController as WebDepartmentController;
use App\Http\Controllers\UserController as WebUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::prefix('otp')->middleware('throttle:10,1')->group(function () {
    Route::post('/send', [OtpController::class, 'send']);
    Route::post('/verify', [OtpController::class, 'verify']);
    Route::post('/resend', [OtpController::class, 'resend']);
});

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/login/otp', [LoginController::class, 'showOtpForm'])->name('login.otp.form');
    Route::post('/login/otp', [LoginController::class, 'verifyOtp'])->name('login.otp.verify');
    Route::post('/login/otp/resend', [LoginController::class, 'resendOtp'])->name('login.otp.resend');
    
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
        Route::get('/departments/{department}/templates', [RequestController::class, 'templatesByDepartment'])
            ->name('requests.departments.templates');
        Route::get('/templates/{template}', [RequestController::class, 'templateDetails'])
            ->whereNumber('template')
            ->name('requests.templates.show');
        Route::post('/', [RequestController::class, 'store'])->name('requests.store');
        Route::get('/{access_request}', [RequestController::class, 'show'])->name('requests.show');
        Route::get('/{access_request}/edit', [RequestController::class, 'edit'])->name('requests.edit');
        Route::put('/{access_request}', [RequestController::class, 'update'])->name('requests.update');
        
        // Approval Actions
        Route::post('/{access_request}/approve', [RequestController::class, 'approve'])->name('requests.approve');
        Route::post('/{access_request}/reject', [RequestController::class, 'reject'])->name('requests.reject');
        Route::post('/{access_request}/cancel', [RequestController::class, 'cancel'])->name('requests.cancel');
        
        // ICT Admin Actions
        Route::middleware('role:ict_admin')->group(function () {
            Route::get('/fulfillment/queue', [RequestController::class, 'fulfillmentQueue'])->name('requests.fulfillment-queue');
            Route::post('/{access_request}/fulfill', [RequestController::class, 'fulfill'])->name('requests.fulfill');
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
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    
    Route::get('/settings', [AccountController::class, 'settings'])->name('settings');
    Route::put('/settings/preferences', [AccountController::class, 'updatePreferences'])->name('settings.preferences.update');
    Route::put('/settings/password', [AccountController::class, 'updatePassword'])->name('settings.password.update');
    
    // Reports (ICT Admin and Admin only)
    Route::middleware('role:ict_admin|admin')->prefix('reports')->group(function () {
        Route::get('/', [AdminInsightsController::class, 'reports'])->name('reports.index');
        Route::get('/export/csv', [AdminInsightsController::class, 'exportReportsCsv'])->name('reports.export.csv');
        Route::get('/export/pdf', [AdminInsightsController::class, 'exportReportsPdf'])->name('reports.export.pdf');
    });
    
    // Audit Logs (ICT Admin, Admin, and Auditor only)
    Route::middleware('role:ict_admin|admin|auditor')->prefix('audit-logs')->group(function () {
        Route::get('/', [AdminInsightsController::class, 'auditLogs'])->name('audit-logs.index');
        Route::get('/export/csv', [AdminInsightsController::class, 'exportAuditLogsCsv'])->name('audit-logs.export.csv');
        Route::get('/export/pdf', [AdminInsightsController::class, 'exportAuditLogsPdf'])->name('audit-logs.export.pdf');
    });
});
