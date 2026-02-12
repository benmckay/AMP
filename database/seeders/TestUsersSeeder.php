<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@akuh.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // ICT Admin
        $ict = User::updateOrCreate(
            ['email' => 'ict@akuh.com'],
            [
                'name' => 'ICT Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $ict->assignRole('ict_admin');

        // Requester (Physician Department)
        $requester = User::updateOrCreate(
            ['email' => 'requester@akuh.com'],
            [
                'name' => 'Dr. John Requester',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $requester->assignRole('requester');
        
        // Assign to Physician department as requester (assuming Physician ID is 1)
        DB::table('department_users')->updateOrInsert(
            ['user_id' => $requester->id, 'department_id' => 1],
            [
                'role' => 'requester',
                'is_active' => true,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Approver (Physician Department)
        $approver = User::updateOrCreate(
            ['email' => 'approver@akuh.com'],
            [
                'name' => 'Dr. Sarah Approver',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $approver->assignRole('approver');
        
        // Assign to Physician department as approver
        DB::table('department_users')->updateOrInsert(
            ['user_id' => $approver->id, 'department_id' => 1],
            [
                'role' => 'approver',
                'is_active' => true,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
