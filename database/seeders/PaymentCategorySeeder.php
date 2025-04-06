<?php

namespace Database\Seeders;

use App\Models\PaymentCategory;
use App\Models\PaymentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy ID của loại thu chi
        $incomeType = PaymentType::where('name', 'Thu nhập')->first();
        $expenseType = PaymentType::where('name', 'Chi tiêu')->first();
        
        if (!$incomeType || !$expenseType) {
            $this->command->error('Payment types not found. Please run PaymentTypeSeeder first.');
            return;
        }
        
        // Danh mục thu nhập
        $incomeCategories = [
            [
                'name' => 'Lương',
                'description' => 'Tiền lương hàng tháng',
                'payment_type_id' => $incomeType->id,
                'color' => '#2196F3', // Blue
                'icon' => 'money-bill',
                'is_active' => true,
            ],
            [
                'name' => 'Đầu tư',
                'description' => 'Thu nhập từ đầu tư',
                'payment_type_id' => $incomeType->id,
                'color' => '#4CAF50', // Green
                'icon' => 'chart-line',
                'is_active' => true,
            ],
            [
                'name' => 'Tiết kiệm',
                'description' => 'Lãi tiết kiệm',
                'payment_type_id' => $incomeType->id,
                'color' => '#9C27B0', // Purple
                'icon' => 'piggy-bank',
                'is_active' => true,
            ],
        ];
        
        // Danh mục chi tiêu
        $expenseCategories = [
            [
                'name' => 'Ăn uống',
                'description' => 'Chi phí ăn uống hàng ngày',
                'payment_type_id' => $expenseType->id,
                'color' => '#FF9800', // Orange
                'icon' => 'utensils',
                'is_active' => true,
            ],
            [
                'name' => 'Sinh hoạt',
                'description' => 'Điện, nước, gas,...',
                'payment_type_id' => $expenseType->id,
                'color' => '#E91E63', // Pink
                'icon' => 'home',
                'is_active' => true,
            ],
            [
                'name' => 'Di chuyển',
                'description' => 'Xăng xe, taxi, phí gửi xe,...',
                'payment_type_id' => $expenseType->id,
                'color' => '#795548', // Brown
                'icon' => 'car',
                'is_active' => true,
            ],
        ];
        
        // Tạo các danh mục
        foreach (array_merge($incomeCategories, $expenseCategories) as $category) {
            PaymentCategory::create($category);
        }
        
        $this->command->info('Payment categories seeded successfully!');
    }
}
