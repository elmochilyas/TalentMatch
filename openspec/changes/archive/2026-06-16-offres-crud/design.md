## Context

TalentMatch is a Laravel 13 + Blade + Breeze application using MySQL. This change introduces the `offres` (job offers) CRUD as the first business entity. The database MCD/MLD has been validated: `users` (1) → (N) `offres` (1) → (N) `candidatures`. Only the `offres` table is created here; `candidatures` and AI analysis come later.

Currently the app has only Breeze auth scaffolding. There are no business models, policies, or Blade views beyond auth pages.

## Goals / Non-Goals

**Goals:**
- Authenticated RH agents can create, list, view, edit, and delete their own job offers
- Offers store: titre, description, competences_requises (JSON array), niveau_experience_minimum
- Ownership-scoped: agents only see/manage their own offers
- Full test coverage with Pest

**Non-Goals:**
- Candidate submission or analysis
- AI integration or queue jobs
- Conversational assistant
- Cross-agent admin features

## Decisions

| Decision | Choice | Rationale | Alternatives Considered |
|---|---|---|---|
| Model name | `Offre` (French) | Matches project conventions and domain language (`offres` table, `Offre` class) | `JobOffer` — inconsistent with other French names expected in the domain |
| Table name | `offres` | Standard Laravel snake_plural, matches French naming | `job_offers` — less idiomatic for this project |
| Field naming | French: `titre`, `description`, `competences_requises`, `niveau_experience_minimum` | Consistency with domain language; French is the project language | English names — would create confusion with future French-named columns |
| Ownership enforcement | Laravel Policy (`OffrePolicy`) | Standard Laravel pattern; policy is the idiomatic way to authorize resource access | Middleware — less flexible, harder to test in isolation, doesn't integrate with `$this->authorize()` in controllers |
| Validation | Form Request classes (`StoreOffreRequest`, `UpdateOffreRequest`) | Keeps controllers thin, separates validation logic, reusable for API later | Inline validation in controller — harder to test and maintain |
| Required skills storage | JSON cast on `competences_requises` | Native MySQL JSON column + Eloquent cast = automatic array serialization/deserialization; comma-separated string input in form | Separate `competences` table — over-engineered for a simple array; no pivot data needed |
| N+1 prevention | Eager load `user` relationship + `withCount('candidatures')` on index | Standard Laravel eager loading; `withCount` avoids loading candidatures just for the count | Lazy loading — causes N+1 queries on index |
| UI | Blade + Tailwind CSS (Breeze style) | Matches existing scaffolding; Breeze layouts are already Tailwind-based | Livewire or Inertia — overkill for this CRUD; not in the stack yet |
| Route definition | `Route::resource('offres', OffreController::class)->middleware(['auth', 'verified'])` | Standard Laravel resource routing with auth middleware | Manual route registration — more verbose, less idiomatic |

## Risks / Trade-offs

- **French field names** → Potential confusion for non-French-speaking developers later. Mitigation: the naming is consistent with the domain and project conventions documented in specs.
- **String-to-array skill input** → Users might enter skills inconsistently (comma-separated, newline-separated). Mitigation: normalize on save (trim, split by comma, remove empties) in Form Request or model mutator.
- **Policy for ownership** → Works well now but will need updating when/if admin roles are introduced. Mitigation: policy gates are query-based (`$user->id === $offre->user_id`), easy to extend with role checks.
- **`withCount` on `candidatures`** → Will be 0 until candidatures exist. No performance concern at this stage; `candidatures` table won't exist yet, so this is a forward-looking query that returns 0 gracefully.

## Open Questions

- Should `niveau_experience_minimum` be an enum (junior/confirmé/senior) or free integer? Decision: free integer (years), simpler and more flexible for diverse job offers.
- Should `competences_requises` be required or optional? Decision: optional — an offer can be posted without specific skills listed.
