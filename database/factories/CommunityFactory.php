<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Community>
 */
class CommunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true) . ' Community',
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(['Art', 'History', 'Music', 'Food', 'Social']),
            'is_private' => $this->faker->boolean(10),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
