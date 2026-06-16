<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssistantMessageRequest;
use App\Models\AnalyseCandidat;
use App\Models\Offre;
use App\Services\AssistantService;

class AssistantController extends Controller
{
    public function __invoke(
        AssistantMessageRequest $request,
        Offre $offre,
        AnalyseCandidat $analyse
    ) {
        if ($analyse->offre_id !== $offre->id) {
            abort(404);
        }

        $response = app(AssistantService::class)->handle(
            $request->user(),
            $analyse,
            $request->input('message'),
        );

        return response()->json([
            'response' => $response,
        ]);
    }
}
