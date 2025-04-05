<?php

namespace Database\Seeders;

use App\Models\PaymentCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Subscription', 'description' => 'Monthly or yearly subscription', 'type' => 'income'],
            ['name' => 'One-time Purchase', 'description' => 'One-time purchase', 'type' => 'expense'],
            ['name' => 'Service Fee', 'description' => 'Service fee', 'type' => 'income'],
            ['name' => 'Maintenance', 'description' => 'Maintenance', 'type' => 'expense'],
            ['name' => 'Support', 'description' => 'Support', 'type' => 'expense'],
        ];
        
        foreach ($categories as $category) {
            PaymentCategory::create($category);
        }
        
        $this->command->info('Payment categories seeded successfully!');
    }
}
