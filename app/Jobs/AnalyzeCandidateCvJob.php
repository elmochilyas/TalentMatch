<?php

namespace App\Jobs;

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

        $this->analyse->update([
            'statut_analyse' => 'completed',
            'competences_extraites' => ['PHP', 'Laravel', 'JavaScript'],
            'annees_experience' => 3,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français', 'Anglais'],
            'matching_score' => null,
            'points_forts' => ['Expérience en développement web', 'Connaissance de Laravel'],
            'lacunes' => ['Aucune expérience DevOps'],
            'competences_manquantes' => ['Docker'],
            'recommandation' => null,
            'justification' => 'Analyse placeholder — l\'analyse IA réelle sera implémentée dans une prochaine itération.',
        ]);
    }
}
