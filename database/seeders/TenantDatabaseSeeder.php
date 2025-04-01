<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds for a specific tenant.
     */
    public function run(): void
    {
        // Here you would seed specific tenant database tables
        // This will be called when a new tenant database is created

        // Default folder structure
        \DB::table('folders')->insert([
            [
                'name' => 'Documents',
                'path' => '/Documents',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Images',
                'path' => '/Images',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Videos',
                'path' => '/Videos',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Additional tenant-specific settings
        \DB::table('settings')->insert([
            [
                'key' => 'max_upload_size',
                'value' => json_encode(100 * 1024 * 1024), // 100 MB
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'allowed_extensions',
                'value' => json_encode(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
