<?php

namespace App\Ai\Tools;

use App\Models\Offre;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetJobRequirements implements Tool
{
    public function __construct(protected User $user) {}

    public function description(): Stringable|string
    {
        return 'Retourne les exigences d\'une offre d\'emploi (titre, description, compétences requises, niveau d\'expérience minimum). Nécessite l\'ID de l\'offre.';
    }

    public function handle(Request $request): Stringable|string
    {
        $offreId = (int) $request['offreId'];

        $offre = Offre::find($offreId);

        if ($offre === null) {
            return 'Aucune offre trouvée avec l\'ID '.$offreId.'.';
        }

        if ($offre->user_id !== $this->user->id) {
            return 'Vous n\'avez pas accès à cette offre.';
        }

        $parts = [
            "**Titre :** {$offre->titre}",
            "**Description :** {$offre->description}",
        ];

        if ($offre->competences_requises !== null) {
            $parts[] = '**Compétences requises :** '.implode(', ', $offre->competences_requises);
        } else {
            $parts[] = '**Compétences requises :** Aucune compétence spécifique requise';
        }

        $parts[] = "**Niveau d'expérience minimum :** ".($offre->niveau_experience_minimum !== null ? "{$offre->niveau_experience_minimum} ans" : 'Non spécifié');

        return implode("\n", $parts);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'offreId' => $schema->integer()->description('ID de l\'offre d\'emploi')->required(),
        ];
    }
}
