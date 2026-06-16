<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOffreRequest;
use App\Http\Requests\UpdateOffreRequest;
use App\Models\Offre;

class OffreController extends Controller
{
    public function index()
    {
        $offres = Offre::where('user_id', auth()->id())
            ->withCount('analysesCandidats')
            ->latest()
            ->paginate(10);

        return view('offres.index', compact('offres'));
    }

    public function create()
    {
        return view('offres.create');
    }

    public function store(StoreOffreRequest $request)
    {
        $offre = auth()->user()->offres()->create(
            $request->validated()
        );

        return redirect()->route('offres.show', $offre);
    }

    public function show(Offre $offre)
    {
        $this->authorize('view', $offre);

        $offre->load(['analysesCandidats.candidat']);

        $analyses = $offre->analysesCandidats;

        $scored = $analyses->filter(
            fn ($a) => $a->statut_analyse->value === 'completed' && $a->matching_score !== null
        )->sortByDesc('matching_score');

        $unscored = $analyses->reject(
            fn ($a) => $a->statut_analyse->value === 'completed' && $a->matching_score !== null
        );

        $offre->setRelation('analysesCandidats', $scored->concat($unscored));

        return view('offres.show', compact('offre'));
    }

    public function edit(Offre $offre)
    {
        $this->authorize('update', $offre);

        return view('offres.edit', compact('offre'));
    }

    public function update(UpdateOffreRequest $request, Offre $offre)
    {
        $this->authorize('update', $offre);

        $offre->update($request->validated());

        return redirect()->route('offres.show', $offre);
    }

    public function destroy(Offre $offre)
    {
        $this->authorize('delete', $offre);

        $offre->delete();

        return redirect()->route('offres.index');
    }
}
