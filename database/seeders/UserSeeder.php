<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test admin user
        \App\Models\User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Create test regular user
        \App\Models\User::create([
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => bcrypt('password123'),
        ]);
    }
}
