<?php

use App\Ai\Agents\AssistantAgent;
use App\Ai\Tools\CompareCandidates;
use App\Ai\Tools\GetCandidateAnalysis;
use App\Ai\Tools\GetJobRequirements;
use App\Enums\Recommandation;
use App\Enums\StatutAnalyse;
use App\Models\AnalyseCandidat;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;
use App\Services\AssistantService;
use Laravel\Ai\Tools\Request;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    config()->set('ai.conversations.generate_title', false);

    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->offre = Offre::factory()->create([
        'user_id' => $this->user->id,
        'titre' => 'Développeur Laravel',
        'description' => 'Nous recherchons un développeur Laravel.',
        'competences_requises' => ['PHP', 'Laravel', 'MySQL'],
        'niveau_experience_minimum' => 3,
    ]);

    $this->otherOffre = Offre::factory()->create([
        'user_id' => $this->otherUser->id,
    ]);

    $this->candidat = Candidat::factory()->create([
        'nom_candidat' => 'Jean Dupont',
        'cv_texte' => 'Développeur PHP avec 5 ans d\'expérience.',
    ]);

    $this->otherCandidat = Candidat::factory()->create([
        'nom_candidat' => 'Marie Martin',
    ]);

    $this->analyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'competences_extraites' => ['PHP', 'Laravel', 'MySQL'],
        'annees_experience' => 5,
        'niveau_etudes' => 'Bac+5',
        'langues' => ['Français', 'Anglais'],
        'matching_score' => 85,
        'points_forts' => ['Expérience Laravel', 'Maîtrise de PHP'],
        'lacunes' => ['Pas de compétences DevOps'],
        'competences_manquantes' => ['Docker'],
        'recommandation' => Recommandation::Convoquer,
        'justification' => 'Profil solide correspondant aux exigences.',
    ]);

    $this->otherAnalyse = AnalyseCandidat::create([
        'offre_id' => $this->otherOffre->id,
        'candidat_id' => $this->otherCandidat->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'competences_extraites' => ['JavaScript', 'React'],
        'annees_experience' => 3,
        'niveau_etudes' => 'Bac+3',
        'langues' => ['Français'],
        'matching_score' => 65,
        'points_forts' => ['Expérience frontend'],
        'lacunes' => ['Pas d\'expérience backend'],
        'competences_manquantes' => ['PHP', 'Laravel'],
        'recommandation' => Recommandation::Attente,
        'justification' => 'Profil orienté frontend, correspondance partielle.',
    ]);
});

// ─── Auth & Access ───────────────────────────────────────────────

it('guest cannot access assistant', function () {
    $this->post(
        route('offres.analyses.assistant', [$this->offre, $this->analyse]),
        ['message' => 'Bonjour']
    )->assertRedirectToRoute('login');
});

it('authenticated user can send message to own analysis', function () {
    AssistantAgent::fake([
        'Bienvenue dans la conversation.',  // Bonjour prompt
        'Réponse de l\'assistant.',         // actual user message
    ]);

    actingAs($this->user)
        ->postJson(
            route('offres.analyses.assistant', [$this->offre, $this->analyse]),
            ['message' => 'Parlez-moi de ce candidat']
        )
        ->assertSuccessful()
        ->assertJson(['response' => 'Réponse de l\'assistant.']);
});

it('returns 403 when sending message to another users analysis', function () {
    actingAs($this->user)
        ->postJson(
            route('offres.analyses.assistant', [$this->otherOffre, $this->otherAnalyse]),
            ['message' => 'Parlez-moi de ce candidat']
        )
        ->assertForbidden();
});

it('returns 404 when analyse does not belong to the offre', function () {
    $offre2 = Offre::factory()->create(['user_id' => $this->user->id]);

    actingAs($this->user)
        ->postJson(
            route('offres.analyses.assistant', [$offre2, $this->analyse]),
            ['message' => 'Test']
        )
        ->assertNotFound();
});

it('validates message is required', function () {
    actingAs($this->user)
        ->post(
            route('offres.analyses.assistant', [$this->offre, $this->analyse]),
            ['message' => '']
        )
        ->assertSessionHasErrors(['message']);
});

it('validates message is not too long', function () {
    actingAs($this->user)
        ->post(
            route('offres.analyses.assistant', [$this->offre, $this->analyse]),
            ['message' => str_repeat('a', 2001)]
        )
        ->assertSessionHasErrors(['message']);
});

// ─── Tool: GetCandidateAnalysis ──────────────────────────────────

it('getCandidateAnalysis returns real saved analysis data', function () {
    $tool = new GetCandidateAnalysis($this->user);
    $request = new Request(['candidatId' => $this->candidat->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('Jean Dupont');
    expect($result)->toContain('85');
    expect($result)->toContain('À convoquer');
    expect($result)->toContain('Expérience Laravel');
    expect($result)->toContain('Pas de compétences DevOps');
    expect($result)->toContain('Docker');
    expect($result)->toContain('PHP');
    expect($result)->toContain('Bac+5');
    expect($result)->toContain('Français');
});

it('getCandidateAnalysis handles non existent candidat', function () {
    $tool = new GetCandidateAnalysis($this->user);
    $request = new Request(['candidatId' => 99999]);

    $result = $tool->handle($request);

    expect($result)->toContain('Aucun candidat trouvé');
    expect($result)->toContain('99999');
});

it('getCandidateAnalysis enforces ownership', function () {
    $tool = new GetCandidateAnalysis($this->user);
    $request = new Request(['candidatId' => $this->otherCandidat->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('pas accès');
});

it('getCandidateAnalysis handles non completed status', function () {
    $pendingAnalyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => Candidat::factory()->create()->id,
        'statut_analyse' => StatutAnalyse::Pending,
    ]);

    $tool = new GetCandidateAnalysis($this->user);
    $request = new Request(['candidatId' => $pendingAnalyse->candidat_id]);

    $result = $tool->handle($request);

    expect($result)->toContain('pas encore terminée');
    expect($result)->toContain('pending');
});

it('getCandidateAnalysis handles null fields gracefully', function () {
    $nullAnalyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => Candidat::factory()->create()->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'matching_score' => 50,
        'recommandation' => Recommandation::Attente,
        'justification' => 'Profil moyen.',
    ]);

    $tool = new GetCandidateAnalysis($this->user);
    $request = new Request(['candidatId' => $nullAnalyse->candidat_id]);

    $result = $tool->handle($request);

    expect($result)->toContain('Non disponibles');
    expect($result)->toContain('Non renseigné');
    expect($result)->toContain('50');
});

// ─── Tool: GetJobRequirements ────────────────────────────────────

it('getJobRequirements returns real offer data', function () {
    $tool = new GetJobRequirements($this->user);
    $request = new Request(['offreId' => $this->offre->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('Développeur Laravel');
    expect($result)->toContain('Nous recherchons un développeur Laravel.');
    expect($result)->toContain('PHP');
    expect($result)->toContain('Laravel');
    expect($result)->toContain('MySQL');
    expect($result)->toContain('3 ans');
});

it('getJobRequirements handles non existent offer', function () {
    $tool = new GetJobRequirements($this->user);
    $request = new Request(['offreId' => 99999]);

    $result = $tool->handle($request);

    expect($result)->toContain('Aucune offre trouvée');
});

it('getJobRequirements enforces ownership', function () {
    $tool = new GetJobRequirements($this->user);
    $request = new Request(['offreId' => $this->otherOffre->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('pas accès');
});

// ─── Tool: CompareCandidates ─────────────────────────────────────

it('compareCandidates compares two completed analyses', function () {
    $candidat2 = Candidat::factory()->create(['nom_candidat' => 'Pierre Durand']);
    $analyse2 = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $candidat2->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'competences_extraites' => ['Python', 'Django'],
        'annees_experience' => 4,
        'niveau_etudes' => 'Bac+5',
        'langues' => ['Français'],
        'matching_score' => 72,
        'points_forts' => ['Expérience backend'],
        'lacunes' => ['Pas de Laravel'],
        'competences_manquantes' => ['Laravel'],
        'recommandation' => Recommandation::Attente,
        'justification' => 'Profil solide mais techno différente.',
    ]);

    $tool = new CompareCandidates($this->user);
    $request = new Request(['id1' => $this->candidat->id, 'id2' => $candidat2->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('Jean Dupont');
    expect($result)->toContain('Pierre Durand');
    expect($result)->toContain('85');
    expect($result)->toContain('72');
    expect($result)->toContain('À convoquer');
    expect($result)->toContain('En attente');
});

it('compareCandidates handles same IDs', function () {
    $tool = new CompareCandidates($this->user);
    $request = new Request(['id1' => $this->candidat->id, 'id2' => $this->candidat->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('identiques');
});

it('compareCandidates handles candidates from different offers', function () {
    $autreOffre = Offre::factory()->create(['user_id' => $this->user->id]);
    $candidat2 = Candidat::factory()->create(['nom_candidat' => 'Sophie Lambert']);
    AnalyseCandidat::create([
        'offre_id' => $autreOffre->id,
        'candidat_id' => $candidat2->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'competences_extraites' => ['Python'],
        'annees_experience' => 4,
        'niveau_etudes' => 'Bac+5',
        'langues' => ['Français'],
        'matching_score' => 70,
        'points_forts' => ['Expérience'],
        'lacunes' => ['Rien'],
        'competences_manquantes' => ['Laravel'],
        'recommandation' => Recommandation::Attente,
        'justification' => 'Profil correct.',
    ]);

    $tool = new CompareCandidates($this->user);
    $request = new Request(['id1' => $this->candidat->id, 'id2' => $candidat2->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('offres différentes');
});

it('compareCandidates handles non completed analyses', function () {
    $pendingCandidat = Candidat::factory()->create();
    $pendingAnalyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $pendingCandidat->id,
        'statut_analyse' => StatutAnalyse::Pending,
    ]);

    $tool = new CompareCandidates($this->user);
    $request = new Request(['id1' => $this->candidat->id, 'id2' => $pendingCandidat->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('Comparaison impossible');
    expect($result)->toContain('pending');
});

it('compareCandidates enforces ownership', function () {
    $tool = new CompareCandidates($this->user);
    $request = new Request(['id1' => $this->candidat->id, 'id2' => $this->otherCandidat->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('pas accès');
});

// ─── Follow-up conversation ──────────────────────────────────────

it('follow-up message preserves conversation context', function () {
    AssistantAgent::fake([
        'Démarrage.',                                       // Bonjour (first handle)
        'Première réponse sur le candidat.',                // first question (first handle)
        'Deuxième réponse faisant référence au contexte.',   // follow-up (second handle, no Bonjour)
    ]);

    $service = app(AssistantService::class);

    $firstResponse = $service->handle($this->user, $this->analyse, 'Qui est ce candidat ?');
    expect($firstResponse)->toBe('Première réponse sur le candidat.');

    $secondResponse = $service->handle($this->user, $this->analyse, 'Dites-moi plus');
    expect($secondResponse)->toBe('Deuxième réponse faisant référence au contexte.');
});

it('assistant does not invent data when fields are null', function () {
    $nullCandidat = Candidat::factory()->create(['nom_candidat' => 'Test Null']);
    $nullAnalyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $nullCandidat->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'matching_score' => null,
        'recommandation' => null,
        'justification' => null,
    ]);

    AssistantAgent::fake([
        'Initialisation.',                                                       // Bonjour
        'L\'analyse est disponible mais certaines données sont manquantes.',      // question
    ]);

    actingAs($this->user)
        ->postJson(
            route('offres.analyses.assistant', [$this->offre, $nullAnalyse]),
            ['message' => 'Quel est le score ?']
        )
        ->assertSuccessful()
        ->assertJson(['response' => 'L\'analyse est disponible mais certaines données sont manquantes.']);
});

it('shows appropriate message when analysis is not completed', function () {
    $pendingAnalyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => Candidat::factory()->create()->id,
        'statut_analyse' => StatutAnalyse::Pending,
    ]);

    actingAs($this->user)
        ->get(route('offres.analyses.show', [$this->offre, $pendingAnalyse]))
        ->assertSuccessful()
        ->assertSee('pas encore terminée');
});
