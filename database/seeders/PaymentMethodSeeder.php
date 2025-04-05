<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['name' => 'Credit Card', 'type' => 'credit'],
            ['name' => 'PayPal', 'type' => 'paypal'],
            ['name' => 'Bank Transfer', 'type' => 'bank'],
            ['name' => 'Cash', 'type' => 'cash'],
            ['name' => 'Cryptocurrency', 'type' => 'crypto'],
        ];
        
        foreach ($methods as $method) {
            PaymentMethod::create($method);
        }
        
        $this->command->info('Payment methods seeded successfully!');
    }
}
