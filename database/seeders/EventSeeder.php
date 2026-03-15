<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $users = \App\Models\User::all();
        \App\Models\Event::factory()->count(5)->create([
            'organizer_id' => function() use ($users) {
                return $users->random()->id;
            }
        ])->each(function ($event) use ($users) {
            $event->attendees()->attach(
                $users->random(rand(2, 5))->pluck('id')->toArray()
            );
        });
    }
}
