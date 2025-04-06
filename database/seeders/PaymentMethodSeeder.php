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
            [
                'name' => 'Tiền mặt',
                'description' => 'Thanh toán bằng tiền mặt',
                'color' => '#4CAF50', // Green
                'icon' => 'money-bill',
                'is_active' => true,
            ],
            [
                'name' => 'Thẻ ngân hàng',
                'description' => 'Thanh toán bằng thẻ ATM, thẻ tín dụng',
                'color' => '#2196F3', // Blue
                'icon' => 'credit-card',
                'is_active' => true,
            ],
            [
                'name' => 'Ví điện tử',
                'description' => 'Thanh toán qua ví điện tử như Momo, ZaloPay, VNPay',
                'color' => '#9C27B0', // Purple
                'icon' => 'wallet',
                'is_active' => true,
            ],
            [
                'name' => 'Chuyển khoản',
                'description' => 'Thanh toán bằng chuyển khoản ngân hàng',
                'color' => '#FF9800', // Orange
                'icon' => 'exchange-alt',
                'is_active' => true,
            ],
        ];
        
        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate($method);
        }
        
        $this->command->info('Payment methods seeded successfully!');
    }
}
