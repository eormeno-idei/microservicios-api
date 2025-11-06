<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Role::count() > 2) {
            return;
        }

        // Resetear caché de roles y permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'access-admin-panel',
            'manage-users',
            'edit-content',
            'remove-content',
            'view-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $roleUser = Role::create(['name' => 'user']);
        $roleUser->givePermissionTo(['edit-content']);

        $roleVerified = Role::create(['name' => 'verified']);
        $roleVerified->givePermissionTo(['access-admin-panel']);

        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());
    }
}
