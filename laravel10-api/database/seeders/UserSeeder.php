<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin'),
                'role' => 'Administrator',
            ]
        );

        // Regular user
        User::updateOrCreate(
            ['email' => 'user@mail.com'],
            [
                'name' => 'user',
                'password' => Hash::make('user'),
                'role' => 'user',
            ]
        );
    }
}
