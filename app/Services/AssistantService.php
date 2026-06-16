<?php

namespace App\Services;

use App\Ai\Agents\AssistantAgent;
use App\Models\AnalyseCandidat;
use App\Models\User;
use Laravel\Ai\Models\Conversation;

class AssistantService
{
    public function handle(User $user, AnalyseCandidat $analyse, string $message): string
    {
        $agent = (new AssistantAgent);

        $conversation = $this->resolveConversation($user, $analyse);

        if ($conversation !== null) {
            $agent->continue($conversation->id, as: $user);
        } else {
            $conversation = $agent->forUser($user)->prompt('Bonjour');
            Conversation::where('id', $conversation->conversationId)
                ->update(['title' => 'analyse:'.$analyse->id]);
            $agent = (new AssistantAgent)->continue($conversation->conversationId, as: $user);
        }

        $response = $agent->prompt($message);

        return $response->text;
    }

    protected function resolveConversation(User $user, AnalyseCandidat $analyse): ?Conversation
    {
        return $user->conversations()
            ->where('title', 'analyse:'.$analyse->id)
            ->latest('updated_at')
            ->first();
    }
}
