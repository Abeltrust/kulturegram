<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LiveStreamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $users = \App\Models\User::all();
        \App\Models\LiveStream::factory()->count(3)->create([
            'user_id' => function() use ($users) {
                return $users->random()->id;
            }
        ]);
    }
}
