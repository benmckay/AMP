<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view-requests',
            'create-requests',
            'approve-requests',
            'fulfill-requests',
            'manage-templates',
            'manage-users',
            'view-audit-logs',
            'view-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign permissions
        $requester = Role::findOrCreate('requester');
        $requester->syncPermissions(['view-requests', 'create-requests']);

        $approver = Role::findOrCreate('approver');
        $approver->syncPermissions(['view-requests', 'approve-requests']);

        $hr = Role::findOrCreate('hr');
        $hr->syncPermissions(['view-requests', 'create-requests', 'approve-requests']);

        $ictAdmin = Role::findOrCreate('ict_admin');
        $ictAdmin->syncPermissions([
            'view-requests', 'create-requests', 'fulfill-requests',
            'manage-templates', 'manage-users', 'view-audit-logs', 'view-reports'
        ]);

        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions(Permission::all());

        $auditor = Role::findOrCreate('auditor');
        $auditor->syncPermissions(['view-audit-logs', 'view-reports']);
    }
}
