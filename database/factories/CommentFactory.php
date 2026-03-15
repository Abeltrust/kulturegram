<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'commentable_id' => \App\Models\Post::factory(),
            'commentable_type' => \App\Models\Post::class,
            'content' => $this->faker->sentence(),
            'parent_id' => null,
        ];
    }
}
