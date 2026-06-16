<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offre extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'competences_requises',
        'niveau_experience_minimum',
    ];

    protected function casts(): array
    {
        return [
            'competences_requises' => 'array',
            'niveau_experience_minimum' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analysesCandidats(): HasMany
    {
        return $this->hasMany(AnalyseCandidat::class);
    }
}
