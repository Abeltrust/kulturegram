<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LiveStream>
 */
class LiveStreamFactory extends Factory
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
            'title' => $this->faker->words(4, true),
            'description' => $this->faker->sentence(),
            'thumbnail' => null,
            'category' => $this->faker->randomElement(['Heritage', 'Crafts', 'Stories', 'Rituals']),
            'is_live' => $this->faker->boolean(20),
            'viewers_count' => $this->faker->numberBetween(0, 500),
            'scheduled_at' => $this->faker->optional(0.5)->dateTimeBetween('now', '+1 month'),
            'completed_at' => null,
        ];
    }
}
