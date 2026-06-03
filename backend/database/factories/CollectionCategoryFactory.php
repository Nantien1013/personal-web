<?php

namespace Database\Factories;

use App\Models\CollectionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionCategory>
 */
class CollectionCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->unique()->word(),
            'group'         => $this->faker->randomElement(['theme', 'source', 'media_type']),
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
