<?php

use App\Ai\Agents\AnalyseCvAgent;
use App\Enums\Recommandation;
use App\Jobs\AnalyzeCandidateCvJob;
use App\Models\AnalyseCandidat;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->offre = Offre::factory()->create([
        'user_id' => $this->user->id,
        'titre' => 'Développeur Laravel',
        'description' => 'Nous recherchons un développeur Laravel expérimenté.',
        'competences_requises' => ['PHP', 'Laravel', 'MySQL'],
        'niveau_experience_minimum' => 3,
    ]);
    $this->candidat = Candidat::factory()->create([
        'nom_candidat' => 'Jean Dupont',
        'cv_texte' => 'Développeur PHP avec 5 ans d\'expérience en Laravel et MySQL.',
    ]);
    $this->analyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat->id,
        'statut_analyse' => 'pending',
    ]);
});

it('sets analysis to processing then completed on valid AI output', function () {
    AnalyseCvAgent::fake();

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->statut_analyse->value)->toBe('completed');
});

it('saves valid structured output correctly', function () {
    AnalyseCvAgent::fake();

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->competences_extraites)->toBeArray();
    expect($this->analyse->annees_experience)->toBeInt();
    expect($this->analyse->niveau_etudes)->toBeString();
    expect($this->analyse->langues)->toBeArray();
    expect($this->analyse->matching_score)->toBeInt();
    expect($this->analyse->points_forts)->toBeArray();
    expect($this->analyse->lacunes)->toBeArray();
    expect($this->analyse->competences_manquantes)->toBeArray();
    expect($this->analyse->justification)->toBeString();
    expect($this->analyse->justification)->not->toBeEmpty();
});

it('saves matching score as integer between 0 and 100', function () {
    AnalyseCvAgent::fake();

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->matching_score)->toBeInt();
    expect($this->analyse->matching_score)->toBeGreaterThanOrEqual(0);
    expect($this->analyse->matching_score)->toBeLessThanOrEqual(100);
});

it('saves recommendation as valid enum', function () {
    AnalyseCvAgent::fake();

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->recommandation)->toBeInstanceOf(Recommandation::class);
});

it('accepts score of 0 as valid', function () {
    AnalyseCvAgent::fake([
        [
            'competences_extraites' => ['PHP'],
            'annees_experience' => 0,
            'niveau_etudes' => 'Bac+2',
            'langues' => ['Français'],
            'matching_score' => 0,
            'points_forts' => ['Motivé'],
            'lacunes' => ['Manque d\'expérience'],
            'competences_manquantes' => ['Laravel', 'MySQL'],
            'recommandation' => 'rejeter',
            'justification' => 'Profil junior sans compétences requises.',
        ],
    ]);

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->statut_analyse->value)->toBe('completed');
    expect($this->analyse->matching_score)->toBe(0);
});

it('accepts score of 100 as valid', function () {
    AnalyseCvAgent::fake([
        [
            'competences_extraites' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
            'annees_experience' => 10,
            'niveau_etudes' => 'Bac+8',
            'langues' => ['Français', 'Anglais', 'Allemand'],
            'matching_score' => 100,
            'points_forts' => ['Expert Laravel', 'Architecture logicielle'],
            'lacunes' => [],
            'competences_manquantes' => [],
            'recommandation' => 'convoquer',
            'justification' => 'Profil parfaitement adapté au poste.',
        ],
    ]);

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->statut_analyse->value)->toBe('completed');
    expect($this->analyse->matching_score)->toBe(100);
});

it('fails on invalid score outside 0-100', function () {
    AnalyseCvAgent::fake([
        [
            'competences_extraites' => ['PHP'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => 150,
            'points_forts' => ['Expérience'],
            'lacunes' => ['Rien'],
            'competences_manquantes' => ['Docker'],
            'recommandation' => 'convoquer',
            'justification' => 'Bon candidat.',
        ],
    ]);

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->statut_analyse->value)->toBe('failed');
    expect($this->analyse->message_erreur)->toContain('matching_score');
});

it('fails on invalid recommendation value', function () {
    AnalyseCvAgent::fake([
        [
            'competences_extraites' => ['PHP'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => 75,
            'points_forts' => ['Expérience'],
            'lacunes' => ['Rien'],
            'competences_manquantes' => ['Docker'],
            'recommandation' => 'invalide',
            'justification' => 'Bon candidat.',
        ],
    ]);

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->statut_analyse->value)->toBe('failed');
    expect($this->analyse->message_erreur)->toContain('Recommandation');
});

it('fails on malformed response missing fields', function () {
    AnalyseCvAgent::fake([
        [
            'matching_score' => 75,
        ],
    ]);

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->statut_analyse->value)->toBe('failed');
    expect($this->analyse->message_erreur)->toContain('Champ manquant');
});

it('fails on exception and saves message erreur', function () {
    AnalyseCvAgent::fake()->preventStrayPrompts();

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->statut_analyse->value)->toBe('failed');
    expect($this->analyse->message_erreur)->not->toBeNull();
});

it('handles empty offer required skills safely', function () {
    $offre = Offre::factory()->create([
        'user_id' => $this->user->id,
        'competences_requises' => null,
    ]);

    $candidat = Candidat::factory()->create();
    $analyse = AnalyseCandidat::create([
        'offre_id' => $offre->id,
        'candidat_id' => $candidat->id,
        'statut_analyse' => 'pending',
    ]);

    AnalyseCvAgent::fake();

    $job = new AnalyzeCandidateCvJob($analyse);
    $job->handle();

    $analyse->refresh();

    expect($analyse->statut_analyse->value)->toBe('completed');
});

it('fails on non-array competences extraites', function () {
    AnalyseCvAgent::fake([
        [
            'competences_extraites' => 'PHP',
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => 75,
            'points_forts' => ['Expérience'],
            'lacunes' => ['Rien'],
            'competences_manquantes' => ['Docker'],
            'recommandation' => 'convoquer',
            'justification' => 'Bon candidat.',
        ],
    ]);

    $job = new AnalyzeCandidateCvJob($this->analyse);
    $job->handle();

    $this->analyse->refresh();

    expect($this->analyse->statut_analyse->value)->toBe('failed');
    expect($this->analyse->message_erreur)->toContain('doit être un tableau');
});
