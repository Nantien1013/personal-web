<?php

namespace Database\Factories;

use App\Models\CollectionWork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionWork>
 */
class CollectionWorkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type'   => $this->faker->randomElement(['anime', 'manga']),
            'title'  => $this->faker->sentence(3),
            'status' => $this->faker->randomElement(['watching', 'completed', 'plan', 'on_hold', 'dropped']),
        ];
    }
}
