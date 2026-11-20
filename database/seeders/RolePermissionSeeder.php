<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Warga
            'report.create',
            'report.vote',
            // Petugas lapangan
            'officer.access',
            'report.update-progress',
            // Admin pusat
            'admin.access',
            'report.validate',
            'report.assign',
            'officer.manage',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        Role::findOrCreate('warga', 'web')
            ->syncPermissions(['report.create', 'report.vote']);

        Role::findOrCreate('petugas', 'web')
            ->syncPermissions(['officer.access', 'report.update-progress']);

        Role::findOrCreate('admin', 'web')
            ->syncPermissions(['admin.access', 'report.validate', 'report.assign', 'officer.manage']);
    }
}
