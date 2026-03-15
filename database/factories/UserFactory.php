<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->name();
        return [
            'name' => $name,
            'username' => strtolower(str_replace(' ', '.', $name)) . $this->faker->numberBetween(1, 99),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'bio' => $this->faker->sentence(),
            'location' => $this->faker->city() . ', ' . $this->faker->country(),
            'cultural_background' => $this->faker->randomElement(['Yoruba', 'Igbo', 'Hausa', 'Scottish', 'Maori', 'Navajo', 'Japanese']),
            'languages' => [$this->faker->languageCode(), $this->faker->languageCode()],
            'cultural_interests' => [$this->faker->word(), $this->faker->word()],
            'is_online' => $this->faker->boolean(20),
            'last_seen' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
