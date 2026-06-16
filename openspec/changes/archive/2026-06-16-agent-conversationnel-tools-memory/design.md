## Context

The structured CV analysis feature is fully implemented. Each candidate analysis contains rich data (score, extracted skills, strengths, gaps, missing skills, languages, recommendation, justification). RH agents can view this data on an analysis detail page, but currently have no way to ask questions, compare candidates conversationally, or explore the data interactively.

The laravel/ai SDK (v0.8.1) provides:
- **Conversation memory** via `agent_conversations` and `agent_conversation_messages` tables (migration already executed)
- **Tool/function calling** — real PHP callables that the AI can invoke, with results injected back into the conversation
- **Structured output** support (already used by the AnalyseCvAgent)

The existing `config/ai.php` is configured. No new migrations or models are needed.

## Goals / Non-Goals

**Goals:**
- Register three real Laravel AI SDK tools: `getCandidateAnalysis`, `getJobRequirements`, `compareCandidates`
- Create a conversational chat interface embedded in the analysis detail view
- Use existing laravel/ai conversation tables for memory (no custom tables)
- Enforce ownership: users can only chat about analyses linked to their own offers
- The assistant answers only from tool data — no hallucinated scores, skills, or experience
- Follow-up questions retain context within the same conversation
- All new code is tested with Pest (no real AI API calls in tests)

**Non-Goals:**
- Re-running or editing CV analysis
- A standalone "compare candidates" dashboard (comparison happens inside chat only)
- Full candidate search or filtering
- Streaming responses (simple request/response for now)
- Deployment or infrastructure changes

## Decisions

### 1. Tool architecture: PHP callables with ownership checks

Each tool is a PHP class implementing Laravel AI's tool contract. The tool receives the authenticated user injected via the container or constructor. Ownership verification (`$user->id === $offre->user_id`) happens inside each tool before returning data.

**Why callables over closures:** Closures in service providers cannot be easily mocked in tests. A dedicated class per tool is testable, can be bound in the container, and follows Laravel conventions.

**Why ownership inside the tool (not the controller):** Tools are called by the AI SDK internally — there is no controller layer between the AI and the tool. The tool is the enforcement boundary.

### 2. Assistant orchestration: a single service class

`AssistantService` manages:
- Creating or resuming a conversation via the laravel/ai SDK
- Registering the three tools with the SDK
- Sending the user message + conversation history to the AI
- Processing tool calls (the SDK handles this automatically)
- Returning the final response text to the controller

**Why a service class instead of a controller:** Keeps the controller thin. The orchestration logic (tool registration, conversation management, AI call) is complex enough to warrant isolation.

### 3. Conversation scoping: one conversation per user per analysis

The conversation is scoped to `(user_id, analysis_id)`. The `agent_conversations.title` field stores a string like `"analysis:{analyseId}"` to make lookups easy. Using `firstOrCreate` ensures we resume the same conversation on subsequent messages.

**Why not one conversation per user:** A user may want separate conversations for different candidates. Scoping by analysis keeps context focused and avoids cross-candidate confusion.

### 4. Chat route: POST endpoint returning JSON

`POST /offres/{offre}/analyses/{analyse}/assistant/message` — accepts `{ "message": "..." }`, returns `{ "response": "..." }`. The frontend renders messages client-side.

**Why JSON over full page reload:** The chat is embeddable within the existing analysis detail page without full page navigation. Alpine.js handles the simple fetch-and-append interaction.

### 5. No custom memory table — use laravel/ai SDK tables

The SDK already provides `agent_conversations` and `agent_conversation_messages` with proper schema. A `$conversation->user_id` scoping column exists. No need for a separate pivot or link table. The SDK's `ConversationMemory` class handles persistence automatically.

### 6. AI provider usage in dev/test

The assistant uses the same provider as the rest of the app (configured in `config/ai.php` — defaulting to OpenAI). In tests, the AI SDK's `fake()` or `mock()` methods prevent real API calls. Tools are unit-tested directly against Eloquent models.

### 7. compareCandidates returns a descriptive comparison string

Rather than returning raw JSON that the AI must interpret (risk of hallucination), the `compareCandidates` tool returns a well-formatted French text string containing the side-by-side data. The AI relays this to the user as-is, reducing the risk of fabricating details.

### 8. Tool definition for getCandidateAnalysis

```php
class GetCandidateAnalysis
{
    public function __construct(protected User $user) {}

    #[ToolDefinition(
        name: 'getCandidateAnalysis',
        description: 'Retourne l\'analyse structurée d\'un candidat (score, points forts, lacunes, compétences manquantes, recommandation, justification, etc.)'
    )]
    public function handle(#[ToolParameter('ID du candidat')] int $candidatId): string
    {
        // Load AnalyseCandidat via candidat_id
        // Verify ownership via offre->user_id
        // Return formatted analysis text
    }
}
```

## Risks / Trade-offs

- **AI still generates natural language response** → The tools return real data, but the AI frames the final answer. Use system instructions that strictly forbid inventing data. If a tool returns no data, the assistant must say "Je ne dispose pas de ces données."
- **Conversation memory might leak context across analyses if scoping is wrong** → Mitigation: always scope conversation lookup by both `user_id` and `analysis_id`. Never allow a bare `user_id` lookup.
- **Tool execution cost** → Each assistant message may trigger 1+ tool calls plus the AI completion. Mitigation: tools are cheap (Eloquent queries). The AI completion is the main cost, acceptable because usage is low (RH agents only).
- **compareCandidates with disparate analyses** → If candidates belong to different offers, or if one analysis is failed/pending, the tool returns a clear message explaining why comparison is not possible.
