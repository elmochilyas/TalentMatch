<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class AnalyseCvAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'Tu es un expert en analyse de CV et en recrutement technique. '
            ."Analyse le CV du candidat par rapport à l'offre d'emploi fournie. "
            ."Sois objectif et précis. N'invente PAS de compétences, langues, "
            .'formations ou expériences qui ne sont pas explicitement mentionnées '
            ."dans le CV. Si le CV est peu clair ou manque d'informations, "
            .'mentionne cette incertitude dans la justification.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'competences_extraites' => $schema->array()
                ->items($schema->string())
                ->required(),

            'annees_experience' => $schema->integer()
                ->min(0)
                ->required(),

            'niveau_etudes' => $schema->string()
                ->required(),

            'langues' => $schema->array()
                ->items($schema->string())
                ->required(),

            'matching_score' => $schema->integer()
                ->min(0)
                ->max(100)
                ->required(),

            'points_forts' => $schema->array()
                ->items($schema->string())
                ->required(),

            'lacunes' => $schema->array()
                ->items($schema->string())
                ->required(),

            'competences_manquantes' => $schema->array()
                ->items($schema->string())
                ->required(),

            'recommandation' => $schema->string()
                ->enum(['convoquer', 'attente', 'rejeter'])
                ->required(),

            'justification' => $schema->string()
                ->required(),
        ];
    }
}
