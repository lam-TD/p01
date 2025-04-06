<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Thu nhập',
                'color' => '#4CAF50', // Green
                'icon' => 'arrow-up',
                'is_active' => true,
            ],
            [
                'name' => 'Chi tiêu',
                'color' => '#F44336', // Red
                'icon' => 'arrow-down',
                'is_active' => true,
            ],
        ];
        
        foreach ($types as $type) {
            PaymentType::firstOrCreate($type);
        }
        
        $this->command->info('Payment types seeded successfully!');
    }
} 