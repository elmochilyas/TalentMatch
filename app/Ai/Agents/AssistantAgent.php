<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CompareCandidates;
use App\Ai\Tools\GetCandidateAnalysis;
use App\Ai\Tools\GetJobRequirements;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

class AssistantAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function instructions(): string
    {
        return <<<'PROMPT'
Vous êtes un assistant RH spécialisé dans l'analyse de candidatures pour TalentMatch.

Règles strictes :
1. Utilisez TOUJOURS les outils mis à votre disposition pour obtenir des données réelles. N'inventez JAMAIS de scores, compétences, langues, formations, ou expériences.
2. Si un outil retourne "Non disponible" ou "Non renseigné" pour un champ, dites à l'utilisateur que cette information n'est pas disponible. N'inventez pas de valeur.
3. Si un outil indique que les données ne sont pas accessibles, expliquez poliment que vous ne pouvez pas accéder à ces informations.
4. Vous pouvez suggérer des questions d'entretien basées sur les données réelles de l'analyse.
5. Vous pouvez expliquer les lacunes et compétences manquantes identifiées dans l'analyse.
6. Répondez en français.
7. Soyez concis et professionnel.
PROMPT;
    }

    public function tools(): iterable
    {
        $user = $this->conversationParticipant();

        return [
            new GetCandidateAnalysis($user),
            new GetJobRequirements($user),
            new CompareCandidates($user),
        ];
    }
}
