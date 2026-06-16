<?php

namespace Database\Factories;

use App\Models\Offre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offre>
 */
class OffreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titre' => fake()->jobTitle(),
            'description' => fake()->paragraph(),
            'competences_requises' => fake()->randomElements(['PHP', 'JavaScript', 'Python', 'Laravel', 'React', 'SQL'], rand(1, 4)),
            'niveau_experience_minimum' => fake()->optional()->numberBetween(0, 10),
        ];
    }
}
