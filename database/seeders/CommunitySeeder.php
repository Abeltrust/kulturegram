<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $users = \App\Models\User::all();
        \App\Models\Community::factory()->count(5)->create([
            'created_by' => function() use ($users) {
                return $users->random()->id;
            }
        ])->each(function ($community) use ($users) {
            $community->members()->attach(
                $users->random(rand(2, 5))->pluck('id')->toArray()
            );
        });
    }
}
