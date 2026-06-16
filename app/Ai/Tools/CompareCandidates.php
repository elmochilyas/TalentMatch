<?php

namespace App\Ai\Tools;

use App\Models\AnalyseCandidat;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CompareCandidates implements Tool
{
    public function __construct(protected User $user) {}

    public function description(): Stringable|string
    {
        return 'Compare deux candidats en utilisant leurs analyses sauvegardées. Nécessite les IDs des deux candidats. Ne réexécute pas l\'analyse IA.';
    }

    public function handle(Request $request): Stringable|string
    {
        $id1 = (int) $request['id1'];
        $id2 = (int) $request['id2'];

        if ($id1 === $id2) {
            return 'Les deux IDs sont identiques. Veuillez fournir deux IDs de candidats différents.';
        }

        $analyse1 = AnalyseCandidat::with('candidat', 'offre')
            ->where('candidat_id', $id1)
            ->first();

        $analyse2 = AnalyseCandidat::with('candidat', 'offre')
            ->where('candidat_id', $id2)
            ->first();

        if ($analyse1 === null && $analyse2 === null) {
            return 'Aucun des deux candidats n\'a été trouvé.';
        }

        if ($analyse1 === null) {
            $nom2 = $analyse2->candidat->nom_candidat ?? "Candidat #{$id2}";

            return "Le candidat #{$id1} n'a pas été trouvé. Analyse impossible.";
        }

        if ($analyse2 === null) {
            $nom1 = $analyse1->candidat->nom_candidat ?? "Candidat #{$id1}";

            return "Le candidat #{$id2} n'a pas été trouvé. Analyse impossible.";
        }

        if ($analyse1->offre->user_id !== $this->user->id || $analyse2->offre->user_id !== $this->user->id) {
            return 'Vous n\'avez pas accès à l\'un des candidats.';
        }

        if ($analyse1->offre_id !== $analyse2->offre_id) {
            return 'Les deux candidats ne peuvent pas être comparés car ils appartiennent à des offres différentes.';
        }

        if ($analyse1->statut_analyse->value !== 'completed' || $analyse2->statut_analyse->value !== 'completed') {
            $status1 = $analyse1->statut_analyse->value;
            $status2 = $analyse2->statut_analyse->value;
            $nom1 = $analyse1->candidat->nom_candidat ?? "Candidat #{$id1}";
            $nom2 = $analyse2->candidat->nom_candidat ?? "Candidat #{$id2}";

            return "Comparaison impossible. Statut de {$nom1} : {$status1}. Statut de {$nom2} : {$status2}.";
        }

        $nom1 = $analyse1->candidat->nom_candidat ?? "Candidat #{$id1}";
        $nom2 = $analyse2->candidat->nom_candidat ?? "Candidat #{$id2}";

        $lines = [
            "## Comparaison : {$nom1} vs {$nom2}\n",
            '### Scores',
            "- **{$nom1} :** {$analyse1->matching_score}/100",
            "- **{$nom2} :** {$analyse2->matching_score}/100\n",
            '### Recommandations',
            "- **{$nom1} :** {$this->formatRecommandation($analyse1->recommandation?->value)}",
            "- **{$nom2} :** {$this->formatRecommandation($analyse2->recommandation?->value)}\n",
            "### Années d'expérience",
            "- **{$nom1} :** ".($analyse1->annees_experience ?? 'Non renseigné'),
            "- **{$nom2} :** ".($analyse2->annees_experience ?? 'Non renseigné')."\n",
            '### Compétences extraites',
            "- **{$nom1} :** ".($analyse1->competences_extraites !== null ? implode(', ', $analyse1->competences_extraites) : 'Non disponibles'),
            "- **{$nom2} :** ".($analyse2->competences_extraites !== null ? implode(', ', $analyse2->competences_extraites) : 'Non disponibles')."\n",
            '### Points forts',
            "- **{$nom1} :** ".($analyse1->points_forts !== null ? implode(', ', $analyse1->points_forts) : 'Non disponibles'),
            "- **{$nom2} :** ".($analyse2->points_forts !== null ? implode(', ', $analyse2->points_forts) : 'Non disponibles')."\n",
            '### Lacunes',
            "- **{$nom1} :** ".($analyse1->lacunes !== null ? implode(', ', $analyse1->lacunes) : 'Non disponibles'),
            "- **{$nom2} :** ".($analyse2->lacunes !== null ? implode(', ', $analyse2->lacunes) : 'Non disponibles')."\n",
            '### Compétences manquantes',
            "- **{$nom1} :** ".($analyse1->competences_manquantes !== null ? implode(', ', $analyse1->competences_manquantes) : 'Non disponibles'),
            "- **{$nom2} :** ".($analyse2->competences_manquantes !== null ? implode(', ', $analyse2->competences_manquantes) : 'Non disponibles')."\n",
            '### Justifications',
            "- **{$nom1} :** ".($analyse1->justification ?? 'Non disponible'),
            "- **{$nom2} :** ".($analyse2->justification ?? 'Non disponible'),
        ];

        return implode("\n", $lines);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id1' => $schema->integer()->description('ID du premier candidat')->required(),
            'id2' => $schema->integer()->description('ID du second candidat')->required(),
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
