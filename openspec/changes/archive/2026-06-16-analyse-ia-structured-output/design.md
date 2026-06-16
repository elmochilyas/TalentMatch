## Context

TalentMatch currently has:
- `Offre`, `Candidat`, and `AnalyseCandidat` models with full migrations and casts
- `StatutAnalyse` and `Recommandation` PHP enums defined but not wired into the model casts
- `AnalyzeCandidateCvJob` that sets hardcoded placeholder data (no real AI)
- CV submission flow that dispatches the job with `statut_analyse = 'pending'`
- Offer show page that displays status, score, and recommendation (currently null for real analyses)
- `config/ai.php` already configured with multiple providers including `openai` and `groq`
- `laravel/ai` SDK at `^0.8.1` with structured output via anonymous agents and dedicated agent classes

The job needs to be upgraded to call the AI, validate the structured response, and save real analysis data.

## Goals / Non-Goals

**Goals:**
- Replace placeholder logic in `AnalyzeCandidateCvJob` with real AI structured analysis
- Build a prompt from the offer context (title, description, required skills, experience) and candidate CV
- Use `laravel/ai` anonymous agent with `schema()` to enforce structured JSON output
- Validate the AI response (correct types, score 0–100, valid recommendation) before saving
- Handle all failure modes (invalid JSON, missing fields, invalid values) gracefully
- Wire `StatutAnalyse` and `Recommandation` enums into `AnalyseCandidat` casts
- Display real analysis data on the offer show page and candidate detail page
- Test all paths with faked/mocked AI — no real API calls in tests

**Non-Goals:**
- No conversational assistant
- No `getCandidateAnalysis`, `getJobRequirements`, or `compareCandidates` tools
- No conversation memory
- No new migrations or tables
- No candidate comparison feature

## Decisions

### 1. Anonymous agent vs dedicated agent class
**Decision:** Use **anonymous agent** (`agent(schema: ...)`) inside the job.
**Rationale:** The structured output schema is well-defined and self-contained. Creating a dedicated agent class adds a file with no reuse benefit (no tools, no conversation, no middleware). The anonymous agent is simpler and keeps the logic close to where it's used. If the schema grows or needs reuse, it can be extracted later.
**Alternative considered:** Dedicated `AnalyseCvAgent` class — rejected because there's no tool, middleware, or memory requirement yet, making it overhead without benefit.

### 2. AI provider and model configuration
**Decision:** Use the existing `config/ai.php` default provider (`openai`). RH agents configure via `.env`: `OPENAI_API_KEY`, `AI_MODEL` (default `gpt-4o-mini`).
**Rationale:** The SDK already supports provider failover and multiple providers. `openai` is the default. No code changes needed to `config/ai.php`.
**Alternative considered:** Hardcoding Groq — rejected because provider choice should be environment-driven.

### 3. Structured output enforcement
**Decision:** Use the `laravel/ai` schema API with `$schema->string()->required()`, `$schema->integer()->min(0)->max(100)->required()`, `$schema->array()->items($schema->string())`, and `$schema->string()->enum(['convoquer', 'attente', 'rejeter'])`.
**Rationale:** The SDK validates the schema on the provider side where supported, and provides predictable typed output. Combined with a post-hoc validation check, this gives defense-in-depth.
**Alternative considered:** Free-form text + regex parse — rejected because it's fragile and doesn't leverage SDK capabilities.

### 4. Post-hoc validation
**Decision:** After receiving the structured response, run a validation helper that checks:
- `matching_score` is int 0–100
- `recommandation` is one of the allowed values
- `competences_extraites`, `langues`, `points_forts`, `lacunes`, `competences_manquantes` are arrays
- `justification` is a non-empty string
If validation fails, log the issue and set `statut_analyse = 'failed'` with a descriptive `message_erreur`.
**Rationale:** Although the schema enforces types, some providers may not fully support JSON schema constraints (e.g., `enum` or `min`/`max`). A validation layer ensures data integrity regardless of provider.
**Alternative considered:** Trusting the AI output entirely — rejected because corrupted data is worse than failed status.

### 5. Enum wiring
**Decision:** Add `StatutAnalyse` and `Recommandation` enum casts to `AnalyseCandidat::$casts`.
**Rationale:** Enums already exist and are the Laravel-idiomatic way to handle constrained string values. This makes the model type-safe and self-documenting.
**Alternative considered:** Keeping raw strings — rejected because enums provide type safety and are already defined.

### 6. Job retry behavior
**Decision:** Keep the job non-retryable by default (`$tries = 1`). Failed analyses stay in `failed` status for the RH agent to see.
**Rationale:** A failed AI call is unlikely to succeed on retry without changes (e.g., API key issue, prompt problem). The RH agent should see the error and re-submit if needed. If retry is desired later, it can be added.

### 7. Prompt structure
**Decision:** Build a multi-part prompt in the job with clear sections:
- System instruction describing the role and output rules
- Offer details (title, description, required skills, experience level)
- Candidate CV text
- Instructions to produce the strict JSON structure
**Rationale:** A well-structured prompt improves output quality and consistency.

### 8. Testing approach
**Decision:** Use `agent()->fake()` to mock the anonymous agent. Since anonymous agents can't be faked by class name, use `AgentFacade::fake()` or mock the `agent()` function result.
**Rationale:** The SDK testing docs focus on dedicated agent classes. For anonymous agents, we can mock the `agent()` function by wrapping the AI call in a testable service or by using `Agent::fake()` on a dedicated agent if we switch.
**Fallback:** If faking anonymous agents proves difficult, extract the AI logic into a small service class that can be mocked, or switch to a dedicated agent class specifically for testing.

## Risks / Trade-offs

- **API cost** — Each CV analysis calls the AI provider. For high volume, this could become expensive. Mitigation: RH agents are the only users; volume is inherently low.
- **API latency** — AI calls can take 10–30 seconds. Mitigation: Already handled by queue — user gets immediate feedback, analysis completes in background.
- **Provider schema support** — Not all providers fully support JSON schema `min`/`max`/`enum`. Mitigation: Post-hoc validation catches any schema gaps.
- **Prompt injection** — CV text could contain instructions that influence the AI. Mitigation: The system instruction is placed before the CV text; the prompt clearly separates offer context from CV content.
- **Anonymous agent testing** — The `agent()` function may not have a straightforward fake mechanism. Mitigation: If needed, extract to a small service class with interface for test mocking.

## Migration Plan

1. Add enum casts to `AnalyseCandidat` model (no migration needed)
2. Rewrite `AnalyzeCandidateCvJob::handle()` with real AI call and validation
3. Update UI views to display real analysis data
4. Write tests with mocked AI responses
5. Update `.env.example` with AI configuration variables
