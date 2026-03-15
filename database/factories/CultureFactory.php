<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Culture>
 */
class CultureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'region' => $this->faker->country(),
            'description' => $this->faker->paragraphs(2, true),
            'category' => $this->faker->randomElement(['Tradition', 'Ritual', 'Language', 'Artifact', 'Music', 'Culinary']),
            'language' => $this->faker->languageCode(),
            'historical_period' => $this->faker->randomElement(['Ancient', 'Medieval', 'Colonial', 'Modern', 'Pre-colonial']),
            'significance' => $this->faker->sentence(),
            'rituals' => $this->faker->sentence(),
            'community_role' => $this->faker->sentence(),
            'endangerment_level' => $this->faker->randomElement(['Safe', 'Vulnerable', 'Endangered', 'Critically Endangered', 'Extinct']),
            'current_practitioners' => $this->faker->numberBetween(100, 1000000),
            'transmission_methods' => $this->faker->sentence(),
            'preservation_efforts' => $this->faker->sentence(),
            'challenges' => $this->faker->sentence(),
            'future_vision' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending_review', 'approved', 'featured']),
            'submitted_by' => \App\Models\User::factory(),
        ];
    }
}
