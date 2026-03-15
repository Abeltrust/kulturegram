<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LifeChapter>
 */
class LifeChapterFactory extends Factory
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
            'title' => $this->faker->randomElement(['Roots & Foundation', 'University & Cultural Awakening', 'Software Architecture Mastery', 'Cultural Tech Innovation', 'Community Building', 'Heritage Preservation']),
            'description' => $this->faker->sentence(),
            'period' => $this->faker->year() . '-' . $this->faker->year(),
            'location' => $this->faker->city(),
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
