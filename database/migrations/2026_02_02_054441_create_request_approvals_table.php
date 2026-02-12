<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('access_requests')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users');
            $table->enum('action', ['approved', 'rejected', 'returned']);
            $table->string('status'); // approved, rejected
            $table->text('comments')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('request_id');
            $table->index('approver_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_approvals');
    }
};
