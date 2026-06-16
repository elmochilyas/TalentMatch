<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidatureStoreRequest;
use App\Jobs\AnalyzeCandidateCvJob;
use App\Models\AnalyseCandidat;
use App\Models\Candidat;
use App\Models\Offre;

class CandidatureController extends Controller
{
    public function store(CandidatureStoreRequest $request, Offre $offre)
    {
        $candidat = Candidat::create([
            'nom_candidat' => $request->input('nom_candidat'),
            'cv_texte' => $request->input('cv_texte'),
        ]);

        $analyse = AnalyseCandidat::create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        AnalyzeCandidateCvJob::dispatch($analyse);

        return redirect()->route('offres.show', $offre)
            ->with('success', __('Candidature soumise avec succès.'));
    }
}
