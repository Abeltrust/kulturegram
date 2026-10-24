<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Admin user
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin User',
                'email' => 'admin@kulturegram.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_admin' => true,
            ]
        );

        // Regular users
        User::factory()->count(10)->create();
    }
}
