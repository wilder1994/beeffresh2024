<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Users\PermissionKey;
use App\Domain\Users\RoleSlug;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (PermissionKey::employeeModuleKeys() as $key) {
            Permission::query()->firstOrCreate(
                ['name' => $key, 'guard_name' => 'web']
            );
        }

        $admin = Role::query()->firstOrCreate(
            ['name' => RoleSlug::ADMIN, 'guard_name' => 'web'],
            ['name' => RoleSlug::ADMIN, 'guard_name' => 'web']
        );
        $employee = Role::query()->firstOrCreate(
            ['name' => RoleSlug::EMPLOYEE, 'guard_name' => 'web'],
            ['name' => RoleSlug::EMPLOYEE, 'guard_name' => 'web']
        );
        Role::query()->firstOrCreate(
            ['name' => RoleSlug::CUSTOMER, 'guard_name' => 'web'],
            ['name' => RoleSlug::CUSTOMER, 'guard_name' => 'web']
        );
        Role::query()->firstOrCreate(
            ['name' => RoleSlug::SUPPLIER, 'guard_name' => 'web'],
            ['name' => RoleSlug::SUPPLIER, 'guard_name' => 'web']
        );

        $admin->syncPermissions(Permission::query()->where('guard_name', 'web')->get());
        $employee->syncPermissions([]);
    }
}
