<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in the correct order to respect dependencies
        $this->call([
            
            // User
            UserSeeder::class,
            
            // Dữ liệu thu chi
            PaymentTypeSeeder::class,
            PaymentCategorySeeder::class,
            PaymentMethodSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
