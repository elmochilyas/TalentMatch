<?php

namespace App\Ai\Tools;

use App\Models\AnalyseCandidat;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCandidateAnalysis implements Tool
{
    public function __construct(protected User $user) {}

    public function description(): Stringable|string
    {
        return 'Retourne l\'analyse structurée d\'un candidat (score, points forts, lacunes, compétences manquantes, recommandation, justification, etc.). Nécessite l\'ID du candidat.';
    }

    public function handle(Request $request): Stringable|string
    {
        $candidatId = (int) $request['candidatId'];

        $analyse = AnalyseCandidat::with('candidat', 'offre')
            ->where('candidat_id', $candidatId)
            ->first();

        if ($analyse === null) {
            return 'Aucun candidat trouvé avec l\'ID '.$candidatId.'.';
        }

        if ($analyse->offre->user_id !== $this->user->id) {
            return 'Vous n\'avez pas accès à l\'analyse de ce candidat.';
        }

        if ($analyse->statut_analyse->value !== 'completed') {
            return 'L\'analyse de ce candidat n\'est pas encore terminée. Statut actuel : '.$analyse->statut_analyse->value.'.';
        }

        $nomCandidat = $analyse->candidat->nom_candidat ?? 'Non renseigné';
        $titreOffre = $analyse->offre->titre ?? 'Non renseigné';

        $parts = [
            "**Candidat :** {$nomCandidat}",
            "**Offre :** {$titreOffre}",
            "**Score de correspondance :** {$analyse->matching_score}/100",
            "**Recommandation :** {$this->formatRecommandation($analyse->recommandation?->value)}",
            "**Années d'expérience :** ".($analyse->annees_experience ?? 'Non renseigné'),
            "**Niveau d'études :** ".($analyse->niveau_etudes ?? 'Non renseigné'),
        ];

        if ($analyse->competences_extraites !== null) {
            $parts[] = '**Compétences extraites :** '.implode(', ', $analyse->competences_extraites);
        } else {
            $parts[] = '**Compétences extraites :** Non disponibles';
        }

        if ($analyse->langues !== null) {
            $parts[] = '**Langues :** '.implode(', ', $analyse->langues);
        } else {
            $parts[] = '**Langues :** Non disponibles';
        }

        if ($analyse->points_forts !== null) {
            $parts[] = '**Points forts :** '.implode(', ', $analyse->points_forts);
        } else {
            $parts[] = '**Points forts :** Non disponibles';
        }

        if ($analyse->lacunes !== null) {
            $parts[] = '**Lacunes :** '.implode(', ', $analyse->lacunes);
        } else {
            $parts[] = '**Lacunes :** Non disponibles';
        }

        if ($analyse->competences_manquantes !== null) {
            $parts[] = '**Compétences manquantes :** '.implode(', ', $analyse->competences_manquantes);
        } else {
            $parts[] = '**Compétences manquantes :** Non disponibles';
        }

        $parts[] = '**Justification :** '.($analyse->justification ?? 'Non disponible');

        return implode("\n", $parts);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'candidatId' => $schema->integer()->description('ID du candidat')->required(),
        ];
    }

    protected function formatRecommandation(?string $value): string
    {
        return match ($value) {
            'convoquer' => 'À convoquer',
            'attente' => 'En attente',
            'rejeter' => 'À rejeter',
            default => 'Non renseigné',
        };
    }
}
