<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superAdmin = Role::create(['name' => 'super-admin']);
        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'manager']);
        $user = Role::create(['name' => 'user']);
        $guest = Role::create(['name' => 'guest']);

        // Super Admin has all permissions
        $superAdmin->givePermissionTo(Permission::all());

        // Admin permissions
        $admin->givePermissionTo([
            'file.view', 'file.create', 'file.edit', 'file.delete',
            'folder.view', 'folder.create', 'folder.edit', 'folder.delete',
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'setting.view', 'setting.edit',
            'version.view', 'version.create', 'version.restore',
        ]);

        // Manager permissions
        $manager->givePermissionTo([
            'file.view', 'file.create', 'file.edit', 'file.delete',
            'folder.view', 'folder.create', 'folder.edit', 'folder.delete',
            'user.view',
            'version.view', 'version.create', 'version.restore',
        ]);

        // User permissions
        $user->givePermissionTo([
            'file.view', 'file.create', 'file.edit',
            'folder.view', 'folder.create',
            'version.view', 'version.create',
        ]);

        // Guest permissions
        $guest->givePermissionTo([
            'file.view',
            'folder.view',
        ]);
    }
}
