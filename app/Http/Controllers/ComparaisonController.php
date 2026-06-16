<?php

namespace App\Http\Controllers;

use App\Models\AnalyseCandidat;
use App\Models\Offre;

class ComparaisonController extends Controller
{
    public function index(Offre $offre)
    {
        $this->authorize('view', $offre);

        $offre->load(['analysesCandidats.candidat']);

        return view('offres.comparaison', compact('offre'));
    }

    public function compare(Offre $offre)
    {
        $this->authorize('view', $offre);

        $validated = request()->validate([
            'analyse_id_1' => ['required', 'integer', 'exists:analyses_candidats,id'],
            'analyse_id_2' => ['required', 'integer', 'exists:analyses_candidats,id', 'different:analyse_id_1'],
        ]);

        $analyse1 = AnalyseCandidat::with('candidat')
            ->where('id', $validated['analyse_id_1'])
            ->where('offre_id', $offre->id)
            ->first();

        $analyse2 = AnalyseCandidat::with('candidat')
            ->where('id', $validated['analyse_id_2'])
            ->where('offre_id', $offre->id)
            ->first();

        if ($analyse1 === null || $analyse2 === null) {
            return back()->withErrors(['message' => __('Les deux analyses doivent appartenir à cette offre.')]);
        }

        $offre->load(['analysesCandidats.candidat']);

        return view('offres.comparaison', compact('offre', 'analyse1', 'analyse2'));
    }
}
