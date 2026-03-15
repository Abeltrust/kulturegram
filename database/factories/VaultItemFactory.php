<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VaultItem>
 */
class VaultItemFactory extends Factory
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
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['photos', 'documents', 'videos', 'audio']),
            'file_path' => 'vault/' . $this->faker->word() . '.jpg',
            'file_size' => $this->faker->numberBetween(100, 5000) . ' KB',
            'is_hidden' => $this->faker->boolean(20),
            'access_level' => $this->faker->randomElement(['private', 'family', 'research', 'public']),
            'metadata' => null,
        ];
    }
}
