<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'pr.create', 'pr.edit', 'pr.view_own', 'pr.view_all',
            'pr.approve_hod', 'pr.approve_stores', 'pr.approve_bursar',
            'pr.approve_vc_requisition', 'pr.approve_vc_payment', 'pr.audit',
            'pr.manage_quotes', 'pr.create_po',
            'stock.manage',
            'suppliers.manage',
            'reports.view',
            'admin.users', 'admin.roles',
            'admin.departments', 'admin.budget', 'admin.hod_delegations',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        Role::firstOrCreate(['name' => 'requester'])->syncPermissions([
            'pr.create', 'pr.edit', 'pr.view_own',
        ]);

        Role::firstOrCreate(['name' => 'hod'])->syncPermissions([
            'pr.create', 'pr.view_own', 'pr.view_all', 'pr.approve_hod',
        ]);

        Role::firstOrCreate(['name' => 'stores'])->syncPermissions([
            'pr.view_all', 'pr.approve_stores', 'stock.manage',
        ]);

        Role::firstOrCreate(['name' => 'bursar'])->syncPermissions([
            'pr.view_all', 'pr.approve_bursar', 'reports.view',
        ]);

        Role::firstOrCreate(['name' => 'vc'])->syncPermissions([
            'pr.view_all', 'pr.approve_vc_requisition', 'pr.approve_vc_payment', 'reports.view',
        ]);

        Role::firstOrCreate(['name' => 'procurement'])->syncPermissions([
            'pr.view_all', 'pr.manage_quotes', 'pr.create_po', 'suppliers.manage', 'reports.view',
        ]);

        Role::firstOrCreate(['name' => 'auditor'])->syncPermissions([
            'pr.view_all', 'pr.audit', 'reports.view',
        ]);

        Role::firstOrCreate(['name' => 'admin'])->syncPermissions(
            Permission::all()
        );
    }
}
