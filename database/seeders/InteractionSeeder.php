<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InteractionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $users = \App\Models\User::all();
        $posts = \App\Models\Post::all();
        $cultures = \App\Models\Culture::all();
        
        foreach ($users as $user) {
            // Tap some posts
            foreach ($posts->random(rand(1, 5)) as $post) {
                \App\Models\Interaction::create([
                    'user_id' => $user->id,
                    'interactable_id' => $post->id,
                    'interactable_type' => \App\Models\Post::class,
                    'type' => 'tap',
                ]);
            }
            
            // Resonate with some cultures
            foreach ($cultures->random(rand(1, 3)) as $culture) {
                \App\Models\Interaction::create([
                    'user_id' => $user->id,
                    'interactable_id' => $culture->id,
                    'interactable_type' => \App\Models\Culture::class,
                    'type' => 'resonance',
                ]);
            }
        }
    }
}
