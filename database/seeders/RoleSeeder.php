<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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

        // Crear permisos
        $permissions = [
            'acceder-panel-admin',
            'gestionar-usuarios',
            'editar-contenido',
            'eliminar-contenido',
            'ver-reportes',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos

        // Rol: user (usuario básico)
        $roleUser = Role::create(['name' => 'user']);
        $roleUser->givePermissionTo(['editar-contenido']);

        // Rol: moderator
        $roleModerator = Role::create(['name' => 'moderator']);
        $roleModerator->givePermissionTo(['editar-contenido', 'eliminar-contenido', 'ver-reportes']);

        $roleVerified = Role::create(['name' => 'verified']);
        $roleVerified->givePermissionTo(['acceder-panel-admin']);

        // Rol: admin
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());
    }
}
