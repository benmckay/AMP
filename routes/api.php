<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\TemplateController;
use App\Http\Controllers\API\AccessRequestController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\AuditLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::put('update-password', [AuthController::class, 'updatePassword']);
    });
    
    // Departments
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::get('/search', [DepartmentController::class, 'search']);
        Route::get('/my-requester-departments', [DepartmentController::class, 'myRequesterDepartments']);
        Route::get('/my-approver-departments', [DepartmentController::class, 'myApproverDepartments']);
        Route::get('/{department}', [DepartmentController::class, 'show']);
        Route::get('/{department}/templates', [DepartmentController::class, 'templates']);
    });
    
    // Templates
    Route::prefix('templates')->group(function () {
        Route::get('/', [TemplateController::class, 'index']);
        Route::get('/search', [TemplateController::class, 'search']);
        Route::get('/{template}', [TemplateController::class, 'show']);
        Route::get('/{template}/statistics', [TemplateController::class, 'statistics']);
        
        // ICT Admin only routes
        Route::middleware(['role:ict_admin'])->group(function () {
            Route::post('/', [TemplateController::class, 'store']);
            Route::put('/{template}', [TemplateController::class, 'update']);
            Route::delete('/{template}', [TemplateController::class, 'destroy']);
        });
    });
    
    // Access Requests
    Route::prefix('requests')->group(function () {
        Route::get('/', [AccessRequestController::class, 'index']);
        Route::get('/my-requests', [AccessRequestController::class, 'myRequests']);
        Route::get('/pending-approvals', [AccessRequestController::class, 'pendingApprovals']);
        Route::get('/{accessRequest}', [AccessRequestController::class, 'show']);
        Route::post('/', [AccessRequestController::class, 'store']);
        Route::put('/{accessRequest}', [AccessRequestController::class, 'update']);
        
        // Approval workflow
        Route::post('/{accessRequest}/approve', [AccessRequestController::class, 'approve']);
        Route::post('/{accessRequest}/reject', [AccessRequestController::class, 'reject']);
        Route::post('/{accessRequest}/cancel', [AccessRequestController::class, 'cancel']);
        
        // ICT Admin only
        Route::middleware(['role:ict_admin'])->group(function () {
            Route::get('/fulfillment-queue', [AccessRequestController::class, 'fulfillmentQueue']);
            Route::post('/{accessRequest}/fulfill', [AccessRequestController::class, 'fulfill']);
        });
    });
    
    // User Management (ICT Admin only)
    Route::middleware(['role:ict_admin'])->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
        Route::post('/{user}/assign-department', [UserController::class, 'assignDepartment']);
        Route::delete('/{user}/remove-department/{department}', [UserController::class, 'removeDepartment']);
        Route::get('/{user}/login-history', [UserController::class, 'loginHistory']);
    });
    
    // Dashboards
    Route::prefix('dashboard')->group(function () {
        Route::get('/requester', [DashboardController::class, 'requester']);
        Route::get('/approver', [DashboardController::class, 'approver']);
        Route::get('/hr', [DashboardController::class, 'hr'])->middleware('role:hr');
        Route::get('/ict', [DashboardController::class, 'ict'])->middleware('role:ict_admin');
        Route::get('/admin', [DashboardController::class, 'admin'])->middleware('role:admin');
    });
    
    // Audit Logs (Admin and Auditor only)
    Route::middleware(['role:admin,ict_admin,auditor'])->prefix('audit-logs')->group(function () {
        Route::get('/', [AuditLogController::class, 'index']);
        Route::get('/export', [AuditLogController::class, 'export']);
    });
});