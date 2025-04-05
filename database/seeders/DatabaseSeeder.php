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
            
            // Then seed users (required for payments)
            UserSeeder::class,
            
            // Then seed payment-related data
            PaymentCategorySeeder::class,
            PaymentMethodSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
