<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $users = \App\Models\User::all();
        $posts = \App\Models\Post::all();
        
        foreach ($posts as $post) {
            \App\Models\Comment::factory()->count(rand(2, 5))->create([
                'user_id' => $users->random()->id,
                'commentable_id' => $post->id,
                'commentable_type' => \App\Models\Post::class,
            ]);
        }
    }
}
