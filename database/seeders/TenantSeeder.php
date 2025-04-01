<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantService = app(TenantService::class);

        // Create sample tenants
        $tenants = [
            [
                'name' => 'Company One',
                'domain' => 'company1.localhost',
                'database' => 'tenant_company1',
                'status' => true,
            ],
            [
                'name' => 'Company Two',
                'domain' => 'company2.localhost',
                'database' => 'tenant_company2',
                'status' => true,
            ],
            [
                'name' => 'Company Three',
                'domain' => 'company3.localhost',
                'database' => 'tenant_company3',
                'status' => true,
            ],
        ];

        foreach ($tenants as $tenantData) {
            // Create tenant in central database
            $tenant = Tenant::create($tenantData);

            // Create tenant database
            $tenantService->createTenantDatabase($tenant);

            // Switch to tenant database to seed tenant-specific data
            $tenantService->switchToTenant($tenant);

            // Create tenant-specific settings
            \DB::table('settings')->insert([
                'key' => 'storage_limit',
                'value' => json_encode(1024 * 1024 * 1024), // 1 GB
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Switch back to central database
            $tenantService->switchToCentral();
        }
    }
}
