<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // Create a user for the first tenant
        User::create([
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => Hash::make('password'),
        ]);
        
        $this->command->info('Users seeded successfully!');
    }
}
