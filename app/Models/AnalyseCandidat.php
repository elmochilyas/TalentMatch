<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyseCandidat extends Model
{
    protected $table = 'analyses_candidats';

    protected $fillable = [
        'offre_id',
        'candidat_id',
        'statut_analyse',
        'competences_extraites',
        'annees_experience',
        'niveau_etudes',
        'langues',
        'matching_score',
        'points_forts',
        'lacunes',
        'competences_manquantes',
        'recommandation',
        'justification',
        'message_erreur',
    ];

    protected function casts(): array
    {
        return [
            'competences_extraites' => 'array',
            'langues' => 'array',
            'points_forts' => 'array',
            'lacunes' => 'array',
            'competences_manquantes' => 'array',
            'matching_score' => 'integer',
        ];
    }

    public function offre(): BelongsTo
    {
        return $this->belongsTo(Offre::class);
    }

    public function candidat(): BelongsTo
    {
        return $this->belongsTo(Candidat::class);
    }
}
