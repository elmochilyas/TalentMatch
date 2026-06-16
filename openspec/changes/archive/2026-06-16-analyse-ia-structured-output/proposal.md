## Why

The analyse-ia-foundation phase created the database models, candidate submission flow, and a placeholder job that simulates analysis. To deliver real value, the system must now call the AI to produce genuine structured CV analysis. Without this step, RH agents see only "pending" status and demo data — not actionable insights.

## What Changes

- **AnalyzeCandidateCvJob** is upgraded from placeholder to real AI analysis using `laravel/ai` structured output
- A proper AI prompt is built from offer context (title, description, required skills, experience level) and candidate CV text
- The job validates the AI response against the strict JSON schema before saving
- Invalid AI responses are caught gracefully (failed status + error message)
- UI displays real analysis data (score, recommendation label, strengths, gaps, etc.)
- PHP enums for `AnalyseStatus` and `RecommendationType` are introduced
- The existing analyse-ia-foundation spec gets a delta for the real job behavior
- New structured-ai-analysis spec is added for the AI contract, prompting, and validation
- Tests are written with fake AI responses — no real API calls in tests

## Capabilities

### New Capabilities
- `structured-ai-analysis`: Real AI integration for structured CV analysis — prompt building, AI call via `laravel/ai`, response validation, saving structured output, error handling

### Modified Capabilities
- `analyse-ia-foundation`: The `AnalyzeCandidateCvJob` requirement changes from placeholder simulation to real AI structured analysis. The display requirement gains real analysis data rendering (score, recommendation label, strengths, gaps, missing skills, justification, error state)

## Impact

- **AnalyzeCandidateCvJob** — complete rewrite of the `handle()` method to call AI and validate result
- **AnalyseCandidat model** — add `AnalyseStatus` and `RecommendationType` enum casts
- **New enums** — `AnalyseStatus`, `RecommendationType`
- **New config** — AI provider/model/key via `.env` and `config/ai.php` or similar
- **UI** — offer show page and candidate detail page render real analysis data with recommendation labels
- **Tests** — new feature test class for structured analysis job
- **No new migrations or tables** — the foundation phase already created the schema
