<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_candidat',
        'cv_texte',
    ];

    public function analysesCandidats(): HasMany
    {
        return $this->hasMany(AnalyseCandidat::class);
    }
}
