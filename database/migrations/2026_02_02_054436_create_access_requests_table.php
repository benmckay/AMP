<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            
            // Requester context
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('requester_department_id')->nullable()->constrained('departments');
            
            // Template and department
            $table->foreignId('template_id')->constrained();
            $table->foreignId('department_id')->nullable()->constrained();
            $table->foreignId('system_id')->default(1)->constrained();
            
            $table->enum('request_type', ['new_access', 'additional_rights', 'reactivation', 'termination']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'fulfilled', 'cancelled'])->default('pending');
            
            // Target user details
            $table->string('payroll_number', 50)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email');
            $table->string('username', 100)->nullable();
            $table->string('job_title', 150)->nullable();
            
            // COS fields
            $table->string('provider_group', 100)->nullable();
            $table->string('provider_type', 100)->nullable();
            $table->string('specialty', 100)->nullable();
            $table->string('service', 100)->nullable();
            $table->boolean('admitting')->nullable();
            $table->boolean('ordering_physician')->nullable();
            $table->enum('sign_orders', ['orders', 'reports', 'both', 'neither'])->nullable();
            $table->enum('cosign_orders', ['orders', 'reports', 'both', 'neither'])->nullable();
            
            // Metadata
            $table->text('justification');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Workflow
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('approval_comments')->nullable();
            
            $table->timestamp('fulfilled_at')->nullable();
            $table->foreignId('fulfilled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('fulfillment_notes')->nullable();
            
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('requester_id');
            $table->index('requester_department_id');
            $table->index('department_id');
            $table->index('template_id');
            $table->index('status');
            $table->index('request_number');
            $table->index('submitted_at');
            $table->index(['status', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_requests');
    }
};