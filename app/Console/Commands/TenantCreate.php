<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TenantCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {name} {domain} {--database=} {--seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant with database';

    /**
     * Execute the console command.
     */
    public function handle(TenantService $tenantService)
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $database = $this->option('database') ?: 'tenant_'.Str::slug($name, '_');

        $this->info("Creating tenant '{$name}' with domain '{$domain}' and database '{$database}'...");

        // Check if tenant with this domain already exists
        if (Tenant::where('domain', $domain)->exists()) {
            $this->error("Tenant with domain '{$domain}' already exists!");

            return 1;
        }

        // Check if tenant with this database already exists
        if (Tenant::where('database', $database)->exists()) {
            $this->error("Tenant with database '{$database}' already exists!");

            return 1;
        }

        // Create tenant
        $tenant = Tenant::create([
            'name' => $name,
            'domain' => $domain,
            'database' => $database,
            'status' => true,
        ]);

        // Create tenant database and run migrations
        try {
            $tenantService->createTenantDatabase($tenant);
            $this->info("Tenant database '{$database}' created and migrated successfully!");

            if ($this->option('seed')) {
                $this->info('Seeding tenant database...');
                $tenantService->switchToTenant($tenant);

                // Run tenant database seeders
                \Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\TenantDatabaseSeeder',
                    '--force' => true,
                ]);

                $tenantService->switchToCentral();
                $this->info('Tenant database seeded successfully!');
            }

            $this->info("Tenant '{$name}' created successfully!");

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create tenant database: '.$e->getMessage());

            // Delete tenant record as database creation failed
            $tenant->delete();

            return 1;
        }
    }
}
