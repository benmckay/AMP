<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('mnemonic', 50)->unique();
            $table->string('name');
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('category', 100)->nullable();
            $table->text('description')->nullable();
            
            // EHR-specific access
            $table->string('ehr_access_level', 50)->default('standard');
            $table->jsonb('ehr_module_access')->default('{}');
            $table->jsonb('ehr_permissions')->default('{}');
            
            // General system access
            $table->jsonb('system_access')->default('[]');
            $table->jsonb('permissions')->default('{}');
            
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_cos_approval')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('mnemonic');
            $table->index('department_id');
            $table->index('category');
            $table->index('is_active');
            $table->index('name');
            $table->index('ehr_access_level');
        });
        
        // PostgreSQL-only GIN indexes for JSONB fields.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_templates_ehr_modules ON templates USING gin(ehr_module_access)');
            DB::statement('CREATE INDEX idx_templates_system_access ON templates USING gin(system_access)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
