<?php

namespace Database\Factories;

use App\Models\StudyVocabulary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyVocabulary>
 */
class StudyVocabularyFactory extends Factory
{
    protected $model = StudyVocabulary::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'word'        => $this->faker->unique()->word(),
            'meaning'     => $this->faker->sentence(),
            'familiarity' => $this->faker->numberBetween(0, 5),
        ];
    }
}
