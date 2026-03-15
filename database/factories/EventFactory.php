<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->words(3, true) . ' Festival',
            'description' => $this->faker->paragraph(),
            'location' => $this->faker->address(),
            'event_date' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'event_time' => $this->faker->time('H:i:s'),
            'type' => $this->faker->randomElement(['workshop', 'conference', 'cultural', 'exhibition']),
            'organizer_id' => \App\Models\User::factory(),
            'accepts_contributions' => $this->faker->boolean(30),
        ];
    }
}
