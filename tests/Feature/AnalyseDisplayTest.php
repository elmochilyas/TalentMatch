<?php

use App\Enums\Recommandation;
use App\Enums\StatutAnalyse;
use App\Models\AnalyseCandidat;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->offre = Offre::factory()->create([
        'user_id' => $this->user->id,
        'titre' => 'Développeur Laravel',
        'description' => 'Description du poste.',
    ]);
    $this->otherOffre = Offre::factory()->create([
        'user_id' => $this->otherUser->id,
    ]);
    $this->candidat = Candidat::factory()->create([
        'nom_candidat' => 'Alice Martin',
    ]);
});

// ─── Completed analysis display ───────────────────────────────────

it('displays completed analysis with all fields', function () {
    $analyse = AnalyseCandidat::create([
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

    actingAs($this->user)
        ->get(route('offres.analyses.show', [$this->offre, $analyse]))
        ->assertSuccessful()
        ->assertSee('85')
        ->assertSee('À convoquer')
        ->assertSee('Expérience Laravel')
        ->assertSee('Maîtrise de PHP')
        ->assertSee('Pas de compétences DevOps')
        ->assertSee('Docker')
        ->assertSee('PHP')
        ->assertSee('Laravel')
        ->assertSee('MySQL')
        ->assertSee('Bac+5')
        ->assertSee('Français')
        ->assertSee('Anglais')
        ->assertSee('Profil solide correspondant aux exigences.');
});

it('displays correct recommendation labels', function () {
    $convoquer = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => Candidat::factory()->create()->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'matching_score' => 80,
        'recommandation' => Recommandation::Convoquer,
        'justification' => 'Bon profil.',
    ]);
    $attente = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => Candidat::factory()->create()->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'matching_score' => 60,
        'recommandation' => Recommandation::Attente,
        'justification' => 'Profil moyen.',
    ]);
    $rejeter = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => Candidat::factory()->create()->id,
        'statut_analyse' => StatutAnalyse::Completed,
        'matching_score' => 30,
        'recommandation' => Recommandation::Rejeter,
        'justification' => 'Profil insuffisant.',
    ]);

    actingAs($this->user)
        ->get(route('offres.analyses.show', [$this->offre, $convoquer]))
        ->assertSuccessful()
        ->assertSee('À convoquer');

    actingAs($this->user)
        ->get(route('offres.analyses.show', [$this->offre, $attente]))
        ->assertSuccessful()
        ->assertSee('En attente');

    actingAs($this->user)
        ->get(route('offres.analyses.show', [$this->offre, $rejeter]))
        ->assertSuccessful()
        ->assertSee('À rejeter');
});

// ─── Failed analysis display ─────────────────────────────────────

it('displays error message for failed analysis', function () {
    $analyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat->id,
        'statut_analyse' => StatutAnalyse::Failed,
        'message_erreur' => 'Erreur : réponse AI invalide.',
    ]);

    actingAs($this->user)
        ->get(route('offres.analyses.show', [$this->offre, $analyse]))
        ->assertSuccessful()
        ->assertSee('Analyse échouée')
        ->assertSee('Erreur : réponse AI invalide.');
});

// ─── Pending/processing analysis display ─────────────────────────

it('shows pending indicator for pending analysis', function () {
    $analyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat->id,
        'statut_analyse' => StatutAnalyse::Pending,
    ]);

    actingAs($this->user)
        ->get(route('offres.analyses.show', [$this->offre, $analyse]))
        ->assertSuccessful()
        ->assertSee('Analyse en attente')
        ->assertDontSee('Score de correspondance');
});

it('shows processing indicator for processing analysis', function () {
    $analyse = AnalyseCandidat::create([
        'offre_id' => $this->offre->id,
        'candidat_id' => $this->candidat->id,
        'statut_analyse' => StatutAnalyse::Processing,
    ]);

    actingAs($this->user)
        ->get(route('offres.analyses.show', [$this->offre, $analyse]))
        ->assertSuccessful()
        ->assertSee('Analyse en cours')
        ->assertDontSee('Score de correspondance');
});
