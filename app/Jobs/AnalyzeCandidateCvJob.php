<?php

namespace App\Jobs;

use App\Ai\Agents\AnalyseCvAgent;
use App\Models\AnalyseCandidat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalyzeCandidateCvJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AnalyseCandidat $analyse
    ) {}

    public function handle(): void
    {
        $this->analyse->update(['statut_analyse' => 'processing']);

        try {
            $this->analyse->loadMissing(['offre', 'candidat']);

            $prompt = $this->buildPrompt();

            $response = (new AnalyseCvAgent)->prompt($prompt);

            $data = $this->validateResponse($response);

            $this->analyse->update([
                'statut_analyse' => 'completed',
                'competences_extraites' => $data['competences_extraites'],
                'annees_experience' => $data['annees_experience'],
                'niveau_etudes' => $data['niveau_etudes'],
                'langues' => $data['langues'],
                'matching_score' => $data['matching_score'],
                'points_forts' => $data['points_forts'],
                'lacunes' => $data['lacunes'],
                'competences_manquantes' => $data['competences_manquantes'],
                'recommandation' => $data['recommandation'],
                'justification' => $data['justification'],
            ]);
        } catch (\Throwable $e) {
            $this->analyse->update([
                'statut_analyse' => 'failed',
                'message_erreur' => $e->getMessage(),
            ]);
        }
    }

    private function buildPrompt(): string
    {
        $offre = $this->analyse->offre;
        $candidat = $this->analyse->candidat;

        $competencesRequises = $offre->competences_requises
            ? implode(', ', $offre->competences_requises)
            : 'Aucune compétence spécifiée';

        $prompt = "Offre d'emploi :\n";
        $prompt .= "Titre : {$offre->titre}\n";
        $prompt .= "Description : {$offre->description}\n";
        $prompt .= "Compétences requises : {$competencesRequises}\n";
        $prompt .= 'Expérience minimum : '.($offre->niveau_experience_minimum ?? 'Non spécifiée')." année(s)\n\n";

        $prompt .= "CV du candidat :\n";
        $prompt .= "Nom : {$candidat->nom_candidat}\n";
        $prompt .= "Texte du CV :\n{$candidat->cv_texte}\n\n";

        $prompt .= "Analyse le CV par rapport à l'offre et retourne l'analyse structurée au format JSON spécifié.";

        return $prompt;
    }

    private function validateResponse(mixed $response): array
    {
        $data = json_decode(json_encode($response), true);

        if (! is_array($data)) {
            throw new \InvalidArgumentException("La réponse de l'IA n'est pas un tableau structuré.");
        }

        $requiredFields = [
            'competences_extraites', 'annees_experience', 'niveau_etudes',
            'langues', 'matching_score', 'points_forts', 'lacunes',
            'competences_manquantes', 'recommandation', 'justification',
        ];

        foreach ($requiredFields as $field) {
            if (! array_key_exists($field, $data)) {
                throw new \InvalidArgumentException("Champ manquant : {$field}");
            }
        }

        $arrayFields = ['competences_extraites', 'langues', 'points_forts', 'lacunes', 'competences_manquantes'];
        foreach ($arrayFields as $field) {
            if (! is_array($data[$field])) {
                throw new \InvalidArgumentException("Le champ {$field} doit être un tableau.");
            }
        }

        $score = $data['matching_score'];
        if (! is_int($score) || $score < 0 || $score > 100) {
            throw new \InvalidArgumentException(
                'Le matching_score doit être un entier entre 0 et 100, reçu : '.json_encode($score)
            );
        }

        $validRecommendations = ['convoquer', 'attente', 'rejeter'];
        if (! in_array($data['recommandation'], $validRecommendations, true)) {
            throw new \InvalidArgumentException(
                "Recommandation invalide : {$data['recommandation']}. Valeurs autorisées : ".implode(', ', $validRecommendations)
            );
        }

        if (! is_string($data['justification']) || empty(trim($data['justification']))) {
            throw new \InvalidArgumentException('La justification ne peut pas être vide.');
        }

        return $data;
    }
}
