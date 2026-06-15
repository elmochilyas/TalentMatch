<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/ai (AI) - v0
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>

=== talentmatch project rules ===

# TalentMatch — Automated Candidate Prescreening

## Overview

TalentMatch is a Laravel application that automates candidate prescreening. RH agents create job offers, submit candidate CV text, and the AI returns a structured analysis (score, strengths, gaps, missing skills, recommendation, justification). A conversational assistant answers questions about candidates using real Laravel tools and persistent conversation memory.

## Stack

- **Laravel** — backend framework
- **Blade** — templating (with Breeze scaffolding for auth)
- **Breeze** — authentication scaffolding for RH agents
- **MySQL** — database
- **laravel/ai SDK** — AI interactions and conversation memory
- **Laravel Boost** — MCP server tools (prefer over shell/file alternatives)
- **OpenSpec** — specification-driven development
- **Pest** — testing framework
- **Queues / Jobs** — background AI analysis

## Workflow Rules

1. **No business feature code before an OpenSpec proposal is created and applied.** Always start by defining the spec.
2. **No business migrations before MCD/MLD validation.** Validate the data model before migration files are written.
3. **Every feature must be implemented against its spec.** The spec is the source of truth.
4. **Keep specs inside `specs/` or the OpenSpec folder structure.** Do not scatter spec files.
5. **Commit regularly with messages mentioning AI-assisted work.** Example: `"feat: candidate CV submission (AI-assisted)"`.
6. **Use feature branches** matching the feature: `feature/offres-crud`, `feature/analyse-ia`, `feature/agent-conversationnel`.

### Spec Content Requirements

Every spec must include all of the following:
- **Problem** — what problem does this feature solve?
- **User stories covered** — which scenarios are addressed?
- **Scope** — what is included in this feature?
- **Out of scope** — what is explicitly not included?
- **Data model impact** — new tables, columns, indexes, relationships?
- **UI impact** — new or modified views, components?
- **Validation rules** — what input rules apply?
- **Acceptance criteria** — how do we know it's done?
- **Tests to add** — what tests must be written?
- **Edge cases** — known edge cases and how to handle them?
- **Security / authorization rules** — who can do what?
- **Demo explanation notes** — what to say during demo?

## Required Project Features

- **Auth** — RH agent authentication via Breeze.
- **CRUD Job Offers** — full CRUD for job offers managed by RH agents.
- **Candidate CV Submission** — form-based CV text submission linked to a job offer.
- **Background AI Analysis** — queue/job-driven analysis after CV submission.
- **Structured Output** — strict JSON schema for candidate analysis results.
- **Eloquent Casts** — use casts for arrays, enums, and score value objects.
- **AI Assistant** — conversational interface backed by real Laravel tools (see below).

## AI Assistant — Laravel Tools

The assistant must use these real tools (not hallucinated data):

| Tool | Signature | Description |
|---|---|---|
| `getCandidateAnalysis` | `getCandidateAnalysis(int $candidateId)` | Returns structured analysis for a candidate |
| `getJobRequirements` | `getJobRequirements(int $offerId)` | Returns requirements for a job offer |
| `compareCandidates` | `compareCandidates(int $id1, int $id2)` | Compares two candidates side by side |

**Persistent conversation memory** is handled through the `laravel/ai` SDK tables. Do not implement custom memory storage.

## AI Safety Rules

1. **The assistant must not invent candidate data.** Only return what the tools provide.
2. **The assistant must use real Laravel tools** when answering about candidates or offers — never fabricate responses.
3. **If data is missing, the assistant must say so** instead of guessing or generating plausible-looking values.
4. **CV analysis must respect the strict JSON structure** defined in the spec. Do not deviate from the schema.
5. **Handle edge cases:**
   - Empty CV submission — return an error/validation message, do not attempt analysis.
   - Offer without required skills — analysis should note missing requirements gracefully.
   - Invalid AI response (malformed JSON, missing fields) — catch and log, return a user-friendly error.
   - Low-score candidates — the analysis should still be returned; no silent rejection.

## Demo Expectations

The coding assistant should be prepared to explain:

- **Tools vs hallucination** — how `getCandidateAnalysis` and similar tools prevent AI from inventing data.
- **Queue vs synchronous analysis** — why AI analysis runs as a background job (user gets immediate feedback, analysis completes asynchronously).
- **Structured output** — how strict JSON schemas ensure reliable, parseable AI responses.
- **OpenSpec specs** — where they live, how they drive development.
- **Commits mentioning AI usage** — commit history showing AI-assisted development patterns.

## Laravel Architecture Standards

- Use Form Request classes for create/update/submit validation.
- Use policies or ownership checks so RH agents only access their own offers and candidates.
- Use Eloquent relationships clearly.
- Use eager loading to avoid N+1 queries.
- Use enum for recommendation:
  - `convoquer`
  - `attente`
  - `rejeter`
- Use Eloquent casts for:
  - required skills arrays
  - extracted skills arrays
  - languages arrays
  - strengths arrays
  - gaps arrays
  - missing skills arrays
  - recommendation enum
  - matching score integer
- Keep controllers thin.
- Put business logic in services/jobs/actions when needed.
- Use clear route names.
- Use pagination where lists can grow.

## Database / MCD / MLD Standards

- MCD/MLD must be validated before business migrations.
- Core entities:
  - `users` / RH agents (existing Breeze table)
  - `offres` — belongs to a user
  - `candidatures` — belongs to an offre, stores raw CV text + structured AI result + status
- Define cardinalities clearly in specs (1:N, etc.).
- Define primary keys, foreign keys, nullable fields, indexes, and enum values.
- Analysis status: `pending`, `processing`, `completed`, `failed`.
- Conversation memory uses `laravel/ai` SDK tables — do not create custom memory tables unless justified.

## AI Structured Output Standards

The CV analysis must return this strict JSON structure:

```json
{
  "competences_extraites": ["string"],
  "annees_experience": "integer",
  "niveau_etudes": "string",
  "langues": ["string"],
  "matching_score": "integer 0-100",
  "points_forts": ["string"],
  "lacunes": ["string"],
  "competences_manquantes": ["string"],
  "recommandation": "convoquer | attente | rejeter",
  "justification": "string"
}
```

Rules:
- The AI must not return free-form unstructured text for saved analysis.
- The AI must not invent experience, skills, languages, or education.
- If the CV text is unclear, the AI must mention uncertainty in `justification`.
- The matching score must be between 0 and 100.
- The recommendation must match the score and justification.
- Invalid AI responses must be handled safely.
- Empty CV must be rejected before the job is dispatched.
- Offer without required skills must be handled as an edge case.

## Queue / Job Standards

- Candidate analysis must run in background using a job.
- The UI must not freeze while AI analysis is running.
- Store analysis status: `pending`, `processing`, `completed`, `failed`.
- Failed AI calls must be visible to the RH agent.
- Jobs must be retry-safe where possible.

## AI Assistant / Tool Standards

- The assistant must answer using real Laravel tools for candidate and offer data.
- Required tools (defined as Laravel AI SDK tools):
  - `getCandidateAnalysis(int $candidatId)` — returns structured analysis for a candidate
  - `getJobRequirements(int $offreId)` — returns requirements for a job offer
  - `compareCandidates(int $id1, int $id2)` — compares two candidates side by side
- The assistant must not invent candidate information.
- If a user asks about a candidate, the assistant must retrieve the candidate analysis through a tool.
- If a user asks about an offer, the assistant must retrieve job requirements through a tool.
- If a user asks to compare candidates, the assistant must use `compareCandidates`.
- Conversation memory must support follow-up questions in the same conversation (via `laravel/ai` SDK tables).

## Security Standards

- Authenticated RH agents only (Breeze middleware).
- Users must only access their own offers and related candidate analyses (policy / ownership check).
- Validate all user input (Form Requests).
- Do not expose raw prompts or API keys.
- Do not store secrets in code.
- Keep `.env` out of Git.
- Sanitize / display CV text safely in Blade (`{{ $cvText }}` — never unescaped `{!! !!}`).

## Testing Standards

- Use Pest for all tests.
- Test auth protection (unauthenticated redirect).
- Test ownership rules (cannot access another user's offers/candidates).
- Test offer CRUD (create, read, update, delete).
- Test CV submission validation (empty CV, missing offer, etc.).
- Test analysis status flow (pending -> processing -> completed / failed).
- Test structured output mapping / casts.
- Test edge cases:
  - Empty CV submission
  - Missing required skills on offer
  - Invalid AI response (malformed JSON, missing fields)
  - Score outside 0-100
  - Unauthorized access
- For AI features, use fakes/mocks where possible instead of calling real API in tests.

## UI Standards

- Keep UI simple and clean with Blade + Tailwind CSS.
- Dashboard should show RH agent's offers with key info.
- Offer detail should show criteria and candidates ordered by score.
- Candidate detail should show:
  - Score (0-100)
  - Recommendation with visible label:
    - À convoquer
    - En attente
    - À rejeter
  - Strengths (`points_forts`)
  - Gaps (`lacunes`)
  - Missing skills (`competences_manquantes`)
  - Justification
- Show analysis status clearly (pending / processing / completed / failed).
- Use loading states during analysis processing.

## Demo / Explanation Notes

The project must be easy to explain during demo. Be prepared to answer:

- **Why OpenSpec before code?** — Ensures every feature is intentional, scoped, and validated before implementation. Prevents scope creep and misalignment.
- **Why queue instead of synchronous AI analysis?** — AI calls can take 10-30 seconds. A queue lets the user submit and continue working. The analysis completes in the background.
- **Why structured output instead of plain text?** — Structured JSON guarantees parseable, reliable data for display in tables, sorting, filtering, and comparisons. Plain text would be inconsistent.
- **Why tools instead of assistant guessing?** — Real Laravel tools (`getCandidateAnalysis`, etc.) return actual database data. Without tools, the AI would hallucinate candidate profiles.
- **Why memory instead of stateless chat?** — The `laravel/ai` SDK conversation tables let users ask follow-up questions like "and what about this other candidate?" with full context.
- **What was AI-generated and what was manually reviewed?** — Specs, architecture decisions, and critical security rules are manually reviewed. Boilerplate code, migrations following the MCD, test scaffolding, and Blade templates can be AI-generated.
