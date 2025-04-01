<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // File permissions
        Permission::create(['name' => 'file.view']);
        Permission::create(['name' => 'file.create']);
        Permission::create(['name' => 'file.edit']);
        Permission::create(['name' => 'file.delete']);

        // Folder permissions
        Permission::create(['name' => 'folder.view']);
        Permission::create(['name' => 'folder.create']);
        Permission::create(['name' => 'folder.edit']);
        Permission::create(['name' => 'folder.delete']);

        // User permissions
        Permission::create(['name' => 'user.view']);
        Permission::create(['name' => 'user.create']);
        Permission::create(['name' => 'user.edit']);
        Permission::create(['name' => 'user.delete']);

        // Tenant permissions
        Permission::create(['name' => 'tenant.view']);
        Permission::create(['name' => 'tenant.create']);
        Permission::create(['name' => 'tenant.edit']);
        Permission::create(['name' => 'tenant.delete']);

        // Setting permissions
        Permission::create(['name' => 'setting.view']);
        Permission::create(['name' => 'setting.edit']);

        // Version permissions
        Permission::create(['name' => 'version.view']);
        Permission::create(['name' => 'version.create']);
        Permission::create(['name' => 'version.restore']);
    }
}
