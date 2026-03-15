<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CultureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $users = \App\Models\User::all();
        \App\Models\Culture::factory()->count(10)->create([
            'submitted_by' => function() use ($users) {
                return $users->random()->id;
            }
        ]);
    }
}
