<?php

use App\Jobs\AnalyzeCandidateCvJob;
use App\Models\AnalyseCandidat;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->offre = Offre::factory()->create(['user_id' => $this->user]);
});

it('redirects guest to login when submitting CV', function () {
    $this->post(route('offres.candidatures.store', $this->offre))
        ->assertRedirectToRoute('login');
});

it('authenticated user can submit CV to own offer', function () {
    actingAs($this->user)
        ->post(route('offres.candidatures.store', $this->offre), [
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => fake()->paragraph(5),
        ])
        ->assertRedirect(route('offres.show', $this->offre));

    assertDatabaseHas('candidats', [
        'nom_candidat' => 'Jean Dupont',
    ]);

    assertDatabaseHas('analyses_candidats', [
        'offre_id' => $this->offre->id,
    ]);
});

it('user cannot submit CV to another users offer', function () {
    $otherOffre = Offre::factory()->create(['user_id' => $this->otherUser]);

    actingAs($this->user)
        ->post(route('offres.candidatures.store', $otherOffre), [
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => fake()->paragraph(5),
        ])
        ->assertForbidden();
});

it('rejects empty nom candidat', function () {
    actingAs($this->user)
        ->post(route('offres.candidatures.store', $this->offre), [
            'nom_candidat' => '',
            'cv_texte' => fake()->paragraph(5),
        ])
        ->assertSessionHasErrors(['nom_candidat']);
});

it('rejects empty CV', function () {
    actingAs($this->user)
        ->post(route('offres.candidatures.store', $this->offre), [
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => '',
        ])
        ->assertSessionHasErrors(['cv_texte']);
});

it('rejects too short CV', function () {
    actingAs($this->user)
        ->post(route('offres.candidatures.store', $this->offre), [
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => 'Courte description',
        ])
        ->assertSessionHasErrors(['cv_texte']);
});

it('creates candidate row on successful submission', function () {
    actingAs($this->user)
        ->post(route('offres.candidatures.store', $this->offre), [
            'nom_candidat' => 'Marie Martin',
            'cv_texte' => fake()->paragraph(5),
        ]);

    expect(Candidat::where('nom_candidat', 'Marie Martin')->exists())->toBeTrue();
});

it('creates analysis row with pending status and dispatches job on submission', function () {
    Queue::fake();

    actingAs($this->user)
        ->post(route('offres.candidatures.store', $this->offre), [
            'nom_candidat' => 'Marie Martin',
            'cv_texte' => fake()->paragraph(5),
        ]);

    Queue::assertPushed(AnalyzeCandidateCvJob::class);

    $candidat = Candidat::where('nom_candidat', 'Marie Martin')->first();

    expect(AnalyseCandidat::where('offre_id', $this->offre->id)
        ->where('candidat_id', $candidat->id)
        ->where('statut_analyse', 'pending')
        ->exists()
    )->toBeTrue();
});

it('analysis belongs to correct offer and candidate', function () {
    actingAs($this->user)
        ->post(route('offres.candidatures.store', $this->offre), [
            'nom_candidat' => 'Pierre Durand',
            'cv_texte' => fake()->paragraph(5),
        ]);

    $analyse = AnalyseCandidat::first();

    expect($analyse->offre->id)->toBe($this->offre->id);
    expect($analyse->candidat->nom_candidat)->toBe('Pierre Durand');
});

it('offer show page displays submitted candidates', function () {
    Queue::fake();

    actingAs($this->user)
        ->post(route('offres.candidatures.store', $this->offre), [
            'nom_candidat' => 'Sophie Lambert',
            'cv_texte' => fake()->paragraph(5),
        ]);

    actingAs($this->user)
        ->get(route('offres.show', $this->offre))
        ->assertSuccessful()
        ->assertSee('Sophie Lambert')
        ->assertSee('En attente');
});

it('user cannot see candidates from another users offer', function () {
    $otherOffre = Offre::factory()->create(['user_id' => $this->otherUser]);

    actingAs($this->user)
        ->get(route('offres.show', $otherOffre))
        ->assertForbidden();
});
