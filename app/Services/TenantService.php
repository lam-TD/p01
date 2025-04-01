<?php

namespace App\Services;

use App\Models\Tenant;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenantService
{
    /**
     * Create a new tenant database
     */
    public function createTenantDatabase(Tenant $tenant): bool
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        // Create tenant database
        if ($driver === 'mysql') {
            DB::statement("CREATE DATABASE IF NOT EXISTS {$tenant->database}");
        } elseif ($driver === 'pgsql') {
            DB::statement("CREATE DATABASE {$tenant->database}");
        }

        // Configure tenant database connection
        Config::set('database.connections.tenant', [
            'driver' => $driver,
            'host' => config("database.connections.{$connection}.host"),
            'port' => config("database.connections.{$connection}.port"),
            'database' => $tenant->database,
            'username' => config("database.connections.{$connection}.username"),
            'password' => config("database.connections.{$connection}.password"),
            'charset' => config("database.connections.{$connection}.charset", 'utf8mb4'),
            'collation' => config("database.connections.{$connection}.collation", 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ]);

        // Migrate tenant database
        DB::reconnect('tenant');
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        // Create tenant storage
        $this->createTenantStorage($tenant);

        // Seed tenant database with defaults
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TenantDatabaseSeeder',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        // Switch back to default connection
        DB::reconnect($connection);

        return true;
    }

    /**
     * Create tenant storage
     */
    protected function createTenantStorage(Tenant $tenant): bool
    {
        $storageDriver = config('filesystems.disks.tenant.driver', 'local');

        if ($storageDriver === 'local') {
            // Create local storage directory for tenant
            $tenantDirectory = storage_path('app/tenants/'.$tenant->id);
            if (! file_exists($tenantDirectory)) {
                mkdir($tenantDirectory, 0755, true);
                mkdir($tenantDirectory.'/files', 0755, true);
            }
        } elseif ($storageDriver === 's3' || config('filesystems.disks.tenant.driver') === 'minio') {
            // Create bucket for tenant in MinIO/S3
            $this->createTenantBucket($tenant);
        }

        return true;
    }

    /**
     * Create tenant bucket in MinIO/S3
     */
    protected function createTenantBucket(Tenant $tenant): bool
    {
        $diskConfig = config('filesystems.disks.'.(config('filesystems.disks.tenant.driver') === 'minio' ? 'minio' : 's3'));

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => $diskConfig['region'],
            'endpoint' => $diskConfig['endpoint'] ?? null,
            'use_path_style_endpoint' => $diskConfig['use_path_style_endpoint'] ?? false,
            'credentials' => [
                'key' => $diskConfig['key'],
                'secret' => $diskConfig['secret'],
            ],
        ]);

        $bucketName = strtolower($diskConfig['bucket'].'-'.$tenant->id);

        // Check if bucket exists
        if (! $s3Client->doesBucketExist($bucketName)) {
            // Create bucket
            $s3Client->createBucket([
                'Bucket' => $bucketName,
            ]);

            // Set bucket policy for tenant access
            $s3Client->putBucketPolicy([
                'Bucket' => $bucketName,
                'Policy' => json_encode([
                    'Version' => '2012-10-17',
                    'Statement' => [
                        [
                            'Sid' => 'TenantAccess',
                            'Effect' => 'Allow',
                            'Principal' => ['AWS' => '*'],
                            'Action' => [
                                's3:GetObject',
                                's3:PutObject',
                                's3:DeleteObject',
                            ],
                            'Resource' => "arn:aws:s3:::{$bucketName}/*",
                            'Condition' => [
                                'StringEquals' => [
                                    's3:x-amz-meta-tenant-id' => (string) $tenant->id,
                                ],
                            ],
                        ],
                    ],
                ]),
            ]);
        }

        // Store bucket name in tenant config
        $this->switchToTenant($tenant);
        DB::table('settings')->insert([
            'key' => 'storage_bucket',
            'value' => json_encode($bucketName),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->switchToCentral();

        return true;
    }

    /**
     * Switch to tenant database
     */
    public function switchToTenant(Tenant $tenant): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        // Configure tenant database connection
        Config::set('database.connections.tenant', [
            'driver' => $driver,
            'host' => config("database.connections.{$connection}.host"),
            'port' => config("database.connections.{$connection}.port"),
            'database' => $tenant->database,
            'username' => config("database.connections.{$connection}.username"),
            'password' => config("database.connections.{$connection}.password"),
            'charset' => config("database.connections.{$connection}.charset", 'utf8mb4'),
            'collation' => config("database.connections.{$connection}.collation", 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ]);

        // Configure tenant storage
        $storageDriver = config('filesystems.disks.tenant.driver', 'local');
        if ($storageDriver === 'local') {
            Config::set('filesystems.disks.tenant.root', storage_path('app/tenants/'.$tenant->id));
        } elseif ($storageDriver === 's3' || $storageDriver === 'minio') {
            $diskType = $storageDriver === 'minio' ? 'minio' : 's3';
            $diskConfig = config('filesystems.disks.'.$diskType);

            Config::set('filesystems.disks.tenant', [
                'driver' => 's3',
                'key' => $diskConfig['key'],
                'secret' => $diskConfig['secret'],
                'region' => $diskConfig['region'],
                'bucket' => strtolower($diskConfig['bucket'].'-'.$tenant->id),
                'endpoint' => $diskConfig['endpoint'] ?? null,
                'use_path_style_endpoint' => $diskConfig['use_path_style_endpoint'] ?? false,
                'visibility' => 'private',
                'throw' => false,
            ]);
        }

        // Switch connection
        DB::purge('tenant');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');
    }

    /**
     * Switch back to central database
     */
    public function switchToCentral(): void
    {
        $connection = config('database.default');
        DB::setDefaultConnection($connection);
    }

    /**
     * Delete tenant database
     */
    public function deleteTenantDatabase(Tenant $tenant): bool
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        // Delete tenant storage
        $this->deleteTenantStorage($tenant);

        // Drop tenant database
        if ($driver === 'mysql') {
            DB::statement("DROP DATABASE IF EXISTS {$tenant->database}");
        } elseif ($driver === 'pgsql') {
            // Terminate all connections before dropping
            DB::statement("SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = '{$tenant->database}'");
            DB::statement("DROP DATABASE IF EXISTS {$tenant->database}");
        }

        return true;
    }

    /**
     * Delete tenant storage
     */
    protected function deleteTenantStorage(Tenant $tenant): bool
    {
        $storageDriver = config('filesystems.disks.tenant.driver', 'local');

        if ($storageDriver === 'local') {
            // Delete local storage directory
            $tenantDirectory = storage_path('app/tenants/'.$tenant->id);
            if (file_exists($tenantDirectory)) {
                $this->deleteDirectory($tenantDirectory);
            }
        } elseif ($storageDriver === 's3' || $storageDriver === 'minio') {
            // Delete bucket
            $this->deleteTenantBucket($tenant);
        }

        return true;
    }

    /**
     * Delete tenant bucket in MinIO/S3
     */
    protected function deleteTenantBucket(Tenant $tenant): bool
    {
        $diskConfig = config('filesystems.disks.'.(config('filesystems.disks.tenant.driver') === 'minio' ? 'minio' : 's3'));

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => $diskConfig['region'],
            'endpoint' => $diskConfig['endpoint'] ?? null,
            'use_path_style_endpoint' => $diskConfig['use_path_style_endpoint'] ?? false,
            'credentials' => [
                'key' => $diskConfig['key'],
                'secret' => $diskConfig['secret'],
            ],
        ]);

        $bucketName = strtolower($diskConfig['bucket'].'-'.$tenant->id);

        // Check if bucket exists
        if ($s3Client->doesBucketExist($bucketName)) {
            // Delete all objects in bucket
            $objects = $s3Client->listObjects([
                'Bucket' => $bucketName,
            ]);

            if (isset($objects['Contents'])) {
                foreach ($objects['Contents'] as $object) {
                    $s3Client->deleteObject([
                        'Bucket' => $bucketName,
                        'Key' => $object['Key'],
                    ]);
                }
            }

            // Delete bucket
            $s3Client->deleteBucket([
                'Bucket' => $bucketName,
            ]);
        }

        return true;
    }

    /**
     * Recursively delete a directory
     *
     * @param  string  $dir
     */
    protected function deleteDirectory($dir): void
    {
        if (! file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
