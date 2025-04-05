<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PaymentCategory;
use App\Models\PaymentMethod;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 1, 100000000),
            'description' => $this->faker->sentence,
            // random year between 2020 and 2025
            'payment_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'payment_category_id' => PaymentCategory::all()->random()->id,
            'payment_method_id' => PaymentMethod::all()->random()->id,
            'user_id' => User::all()->random()->id,
        ];
    }
}
