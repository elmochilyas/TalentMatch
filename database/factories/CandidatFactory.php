<?php

namespace Database\Factories;

use App\Models\Candidat;
use Illuminate\Database\Eloquent\Factories\Factory;

class CandidatFactory extends Factory
{
    protected $model = Candidat::class;

    public function definition(): array
    {
        return [
            'nom_candidat' => fake()->name(),
            'cv_texte' => fake()->paragraphs(3, true),
        ];
    }
}
