<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'content' => $this->faker->paragraph(),
            'location' => $this->faker->city(),
            'chapter' => $this->faker->randomElement(['Roots & Foundation', 'University & Cultural Awakening', 'Software Architecture Mastery', 'Cultural Tech Innovation', 'Community Building', 'Heritage Preservation']),
            'privacy' => $this->faker->randomElement(['public', 'private', 'vault']),
            'type' => $this->faker->randomElement(['text', 'image', 'video', 'audio']),
            'tags' => $this->faker->words(3),
            'is_featured' => $this->faker->boolean(10),
        ];
    }
}
