<?php

use App\Ai\Tools\CompareCandidates;
use App\Enums\Recommandation;
use App\Enums\StatutAnalyse;
use App\Models\AnalyseCandidat;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;
use Laravel\Ai\Tools\Request;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->offre = Offre::factory()->create([
        'user_id' => $this->user->id,
        'titre' => 'Développeur Laravel',
    ]);

    $this->otherOffre = Offre::factory()->create([
        'user_id' => $this->otherUser->id,
    ]);

    $this->candidat1 = Candidat::factory()->create(['nom_candidat' => 'Alice']);
    $this->candidat2 = Candidat::factory()->create(['nom_candidat' => 'Bob']);
    $this->candidat3 = Candidat::factory()->create(['nom_candidat' => 'Charlie']);

    $this->analyse1 = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat1->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'competences_extraites' => ['PHP', 'Laravel'],
        'annees_experience' => 5,
        'niveau_etudes' => 'Bac+5',
        'langues' => ['Français'],
        'matching_score' => 85,
        'points_forts' => ['Expérience Laravel'],
        'lacunes' => ['Pas de DevOps'],
        'competences_manquantes' => ['Docker'],
        'recommandation' => Recommandation::Convoquer,
        'justification' => 'Excellent profil.',
    ]);

    $this->analyse2 = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat2->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'competences_extraites' => ['Python', 'Django'],
        'annees_experience' => 3,
        'niveau_etudes' => 'Bac+3',
        'langues' => ['Français'],
        'matching_score' => 62,
        'points_forts' => ['Autonome'],
        'lacunes' => ['Pas de Laravel'],
        'competences_manquantes' => ['Laravel', 'MySQL'],
        'recommandation' => Recommandation::Attente,
        'justification' => 'Profil correct mais techno différente.',
    ]);
});

// ─── Ranking on offer show page ────────────────────────────────

it('ranks completed scored candidates by score descending on offer show', function () {
    $analyse3 = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat3->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'matching_score' => 45,
        'points_forts' => ['Motivé'],
        'recommandation' => Recommandation::Rejeter,
        'justification' => 'Profil insuffisant.',
    ]);

    actingAs($this->user)
        ->get(route('offres.show', $this->offre))
        ->assertSuccessful()
        ->assertSeeInOrder(['85', '62', '45']);
});

it('places candidates without score after scored candidates', function () {
    AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat3->id,
        'statut_analyse' => StatutAnalyse::Pending,
    ]);

    $response = actingAs($this->user)
        ->get(route('offres.show', $this->offre))
        ->assertSuccessful()
        ->assertSee('Alice')
        ->assertSee('Bob')
        ->assertSee('Charlie');

    $content = $response->getContent();

    $posAlice = strpos($content, 'Alice');
    $posBob = strpos($content, 'Bob');
    $posCharlie = strpos($content, 'Charlie');

    expect($posAlice)->toBeLessThan($posCharlie);
    expect($posBob)->toBeLessThan($posCharlie);
});

// ─── Comparison page security ─────────────────────────────────

it('redirects guest to login on comparison page', function () {
    $this->get(route('offres.comparaison.index', $this->offre))
        ->assertRedirect(route('login'));
});

it('returns 403 when user tries to compare on another user offer', function () {
    $user2 = User::factory()->create();

    actingAs($user2)
        ->get(route('offres.comparaison.index', $this->offre))
        ->assertForbidden();
});

it('returns error when comparing candidates from different offers', function () {
    $analyseFromOther = AnalyseCandidat::create([
        'offre_id' => $this->otherOffre->id,
        'candidat_id' => $this->candidat3->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'matching_score' => 70,
    ]);

    actingAs($this->otherUser)
        ->post(route('offres.comparaison.compare', $this->otherOffre), [
            'analyse_id_1' => $analyseFromOther->id,
            'analyse_id_2' => $this->analyse1->id,
        ])
        ->assertSessionHasErrors();
});

// ─── Comparison page functionality ────────────────────────────

it('can compare two completed analyses from own offer', function () {
    actingAs($this->user)
        ->post(route('offres.comparaison.compare', $this->offre), [
            'analyse_id_1' => $this->analyse1->id,
            'analyse_id_2' => $this->analyse2->id,
        ])
        ->assertSuccessful()
        ->assertSee('Alice')
        ->assertSee('Bob');
});

it('displays saved data for both candidates on comparison', function () {
    actingAs($this->user)
        ->post(route('offres.comparaison.compare', $this->offre), [
            'analyse_id_1' => $this->analyse1->id,
            'analyse_id_2' => $this->analyse2->id,
        ])
        ->assertSuccessful()
        ->assertSee('85')
        ->assertSee('62')
        ->assertSee('PHP')
        ->assertSee('Python')
        ->assertSee('Expérience Laravel')
        ->assertSee('Autonome')
        ->assertSee('Pas de DevOps')
        ->assertSee('Pas de Laravel')
        ->assertSee('Docker')
        ->assertSee('Laravel')
        ->assertSee('Excellent profil.')
        ->assertSee('Profil correct mais techno différente.');
});

it('shows conclusion with stronger candidate', function () {
    actingAs($this->user)
        ->post(route('offres.comparaison.compare', $this->offre), [
            'analyse_id_1' => $this->analyse1->id,
            'analyse_id_2' => $this->analyse2->id,
        ])
        ->assertSuccessful()
        ->assertSee('Conclusion')
        ->assertSee('Alice')
        ->assertSee('85');
});

it('shows message when comparing non completed analyses', function () {
    $pendingAnalyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat3->id,
        'statut_analyse' => StatutAnalyse::Pending,
    ]);

    actingAs($this->user)
        ->post(route('offres.comparaison.compare', $this->offre), [
            'analyse_id_1' => $this->analyse1->id,
            'analyse_id_2' => $pendingAnalyse->id,
        ])
        ->assertSuccessful()
        ->assertSee('Comparaison limitée');
});

// ─── compareCandidates tool ───────────────────────────────────

it('compareCandidates tool uses saved data', function () {
    $tool = new CompareCandidates($this->user);
    $request = new Request(['id1' => $this->candidat1->id, 'id2' => $this->candidat2->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('Alice');
    expect($result)->toContain('Bob');
    expect($result)->toContain('85');
    expect($result)->toContain('62');
});

it('compareCandidates tool enforces ownership', function () {
    $tool = new CompareCandidates($this->otherUser);
    $request = new Request(['id1' => $this->candidat1->id, 'id2' => $this->candidat2->id]);

    $result = $tool->handle($request);

    expect($result)->toContain('pas accès');
});
