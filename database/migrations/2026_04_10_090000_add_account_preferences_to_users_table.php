<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_timezone', 64)->nullable()->after('department');
            $table->string('preferred_language', 10)->default('en')->after('preferred_timezone');
            $table->string('theme', 20)->default('system')->after('preferred_language');
            $table->boolean('notify_security_alerts')->default(true)->after('theme');
            $table->boolean('notify_request_updates')->default(true)->after('notify_security_alerts');
            $table->boolean('notify_weekly_summary')->default(false)->after('notify_request_updates');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_timezone',
                'preferred_language',
                'theme',
                'notify_security_alerts',
                'notify_request_updates',
                'notify_weekly_summary',
            ]);
        });
    }
};

