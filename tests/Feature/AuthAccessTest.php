<?php

use App\Models\AnalyseCandidat;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->offre = Offre::factory()->create([
        'user_id' => $this->user->id,
        'titre' => 'Mon offre',
        'description' => 'Description.',
    ]);
    $this->candidat = Candidat::factory()->create(['nom_candidat' => 'Jean Test']);
    $this->analyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat->id,
        'statut_analyse' => 'pending',
    ]);
    $this->otherOffre = Offre::factory()->create([
        'user_id' => $this->otherUser->id,
        'titre' => 'Autre offre',
        'description' => 'Description autre.',
    ]);
});

// ─── Guest redirect ──────────────────────────────────────────────

it('redirects guest to login for dashboard', function () {
    get(route('dashboard'))->assertRedirectToRoute('login');
});

it('redirects guest to login for offre comparison page', function () {
    get(route('offres.comparaison.index', $this->offre))->assertRedirectToRoute('login');
});

it('redirects guest to login for analyse show page', function () {
    get(route('offres.analyses.show', [$this->offre, $this->analyse]))->assertRedirectToRoute('login');
});

it('redirects guest to login for assistant endpoint', function () {
    $this->post(route('offres.analyses.assistant', [$this->offre, $this->analyse]))
        ->assertRedirectToRoute('login');
});

// ─── Ownership checks ────────────────────────────────────────────

it('user gets 403 viewing another users offre', function () {
    actingAs($this->user)
        ->get(route('offres.show', $this->otherOffre))
        ->assertForbidden();
});

it('user gets 403 viewing analysis of another users offre', function () {
    $otherAnalyse = AnalyseCandidat::create([
        'offre_id' => $this->otherOffre->id,
        'candidat_id' => Candidat::factory()->create()->id,
        'statut_analyse' => 'completed',
    ]);

    actingAs($this->user)
        ->get(route('offres.analyses.show', [$this->otherOffre, $otherAnalyse]))
        ->assertForbidden();
});

it('user gets 403 comparing on another users offre', function () {
    actingAs($this->user)
        ->get(route('offres.comparaison.index', $this->otherOffre))
        ->assertForbidden();
});

it('user gets 404 when analysis does not belong to offre', function () {
    $otherOffreSameUser = Offre::factory()->create(['user_id' => $this->user->id]);

    actingAs($this->user)
        ->get(route('offres.analyses.show', [$otherOffreSameUser, $this->analyse]))
        ->assertNotFound();
});
