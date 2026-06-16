<?php

use App\Models\Offre;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('redirects guest to login for index', function () {
    get(route('offres.index'))->assertRedirectToRoute('login');
});

it('redirects guest to login for create', function () {
    get(route('offres.create'))->assertRedirectToRoute('login');
});

it('redirects guest to login for store', function () {
    $this->post(route('offres.store'))->assertRedirectToRoute('login');
});

it('redirects guest to login for show', function () {
    $offre = Offre::factory()->create(['user_id' => $this->user]);
    get(route('offres.show', $offre))->assertRedirectToRoute('login');
});

it('redirects guest to login for edit', function () {
    $offre = Offre::factory()->create(['user_id' => $this->user]);
    get(route('offres.edit', $offre))->assertRedirectToRoute('login');
});

it('redirects guest to login for update', function () {
    $offre = Offre::factory()->create(['user_id' => $this->user]);
    $this->put(route('offres.update', $offre))->assertRedirectToRoute('login');
});

it('redirects guest to login for destroy', function () {
    $offre = Offre::factory()->create(['user_id' => $this->user]);
    $this->delete(route('offres.destroy', $offre))->assertRedirectToRoute('login');
});

it('can create an offer', function () {
    actingAs($this->user)
        ->post(route('offres.store'), [
            'titre' => 'Développeur Laravel',
            'description' => 'Nous recherchons un développeur Laravel.',
            'competences_requises' => 'PHP, JavaScript, Laravel',
            'niveau_experience_minimum' => 3,
        ])
        ->assertRedirect();

    assertDatabaseHas('offres', [
        'user_id' => $this->user->id,
        'titre' => 'Développeur Laravel',
    ]);
});

it('stores competences_requises as json array', function () {
    actingAs($this->user)
        ->post(route('offres.store'), [
            'titre' => 'Développeur Laravel',
            'description' => 'Description du poste.',
            'competences_requises' => 'PHP, JavaScript, Laravel',
        ]);

    $offre = Offre::where('titre', 'Développeur Laravel')->first();

    expect($offre->competences_requises)->toBe(['PHP', 'JavaScript', 'Laravel']);
});

it('lists only authenticated user offers on index', function () {
    $myOffer = Offre::factory()->create(['user_id' => $this->user, 'titre' => 'Mon offre']);
    $otherOffer = Offre::factory()->create(['user_id' => $this->otherUser, 'titre' => 'Autre offre']);

    actingAs($this->user)
        ->get(route('offres.index'))
        ->assertSee($myOffer->titre)
        ->assertDontSee($otherOffer->titre);
});

it('can view own offer', function () {
    $offre = Offre::factory()->create(['user_id' => $this->user]);

    actingAs($this->user)
        ->get(route('offres.show', $offre))
        ->assertSuccessful()
        ->assertSee($offre->titre);
});

it('gets 403 viewing another users offer', function () {
    $offre = Offre::factory()->create(['user_id' => $this->otherUser]);

    actingAs($this->user)
        ->get(route('offres.show', $offre))
        ->assertForbidden();
});

it('can edit own offer', function () {
    $offre = Offre::factory()->create(['user_id' => $this->user]);

    actingAs($this->user)
        ->get(route('offres.edit', $offre))
        ->assertSuccessful();
});

it('gets 403 editing another users offer', function () {
    $offre = Offre::factory()->create(['user_id' => $this->otherUser]);

    actingAs($this->user)
        ->get(route('offres.edit', $offre))
        ->assertForbidden();
});

it('can update own offer', function () {
    $offre = Offre::factory()->create(['user_id' => $this->user]);

    actingAs($this->user)
        ->put(route('offres.update', $offre), [
            'titre' => 'Titre mis à jour',
            'description' => $offre->description,
        ])
        ->assertRedirect();

    expect($offre->fresh()->titre)->toBe('Titre mis à jour');
});

it('gets 403 updating another users offer', function () {
    $offre = Offre::factory()->create(['user_id' => $this->otherUser]);

    actingAs($this->user)
        ->put(route('offres.update', $offre), [
            'titre' => 'Titre mis à jour',
            'description' => $offre->description,
        ])
        ->assertForbidden();
});

it('can delete own offer', function () {
    $offre = Offre::factory()->create(['user_id' => $this->user]);

    actingAs($this->user)
        ->delete(route('offres.destroy', $offre))
        ->assertRedirect();

    assertDatabaseMissing('offres', ['id' => $offre->id]);
});

it('gets 403 deleting another users offer', function () {
    $offre = Offre::factory()->create(['user_id' => $this->otherUser]);

    actingAs($this->user)
        ->delete(route('offres.destroy', $offre))
        ->assertForbidden();
});

it('validates required fields on create', function () {
    actingAs($this->user)
        ->post(route('offres.store'), [
            'titre' => '',
            'description' => '',
        ])
        ->assertSessionHasErrors(['titre', 'description']);
});
