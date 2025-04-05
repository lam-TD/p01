<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Payment::factory()->count(1000)->create();
        $this->command->info('Payments seeded successfully!');
    }
}
