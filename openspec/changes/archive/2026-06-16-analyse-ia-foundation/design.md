## Context

The Offres CRUD is already implemented. RH agents can create and manage job offers. The next capability needed is candidate CV submission against an offer, with a database foundation to store both candidate info and the future AI analysis result. This change builds the data layer, submission flow, and job queue placeholder — but deliberately does not call a real AI API (saved for the next change).

## Goals / Non-Goals

**Goals:**
- Create `candidats` and `analyses_candidats` tables and models with proper relationships
- Add candidate CV submission form on the offer show page with validation
- Dispatch a placeholder `AnalyzeCandidateCvJob` after submission (status lifecycle: pending → processing → completed)
- Display submitted candidates and their analysis status on the offer page
- Enforce authorization: only the offer owner can submit/view candidates

**Non-Goals:**
- Real AI/LLM API call — the job sets placeholder values only
- Structured output via `laravel/ai` SDK — that is the next change
- Assistant conversation, tools, or memory
- Candidate comparison features

## Decisions

| Decision | Choice | Rationale | Alternatives Considered |
|---|---|---|---|
| Candidate model separate from analysis | `Candidat` + `AnalyseCandidat` | Normalized design allows a candidate to be analyzed against multiple offers (future reuse). Unique constraint on `offre_id + candidat_id` prevents duplicates per offer. | Single `candidatures` table with embedded JSON — rejected because candidates are entities worth normalizing. |
| `statut_analyse` as string (not PHP enum) | `string` column with validation | Simpler for a status that may gain states later. The `$fillable` + `in:...` rule on the Form Request is sufficient. | PHP backed enum — over-engineering for now; we can upgrade to an enum later if needed. |
| `recommandation` as nullable string | `nullable string` with `in:convoquer,attente,rejeter` | Since no real AI is called, recommendation starts null. When AI is wired, it will be set to one of three values. | Non-nullable with default — null is semantically correct for "not yet analyzed". |
| Placeholder job behavior | Job sets `processing` then immediately `completed` with demo-safe filler data | Gives the user immediate feedback that the pipeline works end-to-end without a real AI call. Next change will swap the body for real AI. | Keep status as `pending` — too invisible; the user sees nothing change. |
| Authorization via offer policy | Existing `OffrePolicy` (or inline `$offre->user_id === auth()->id()`) | Ownership check on the offer implicitly protects all candidate analyses under it. No separate policy needed for `AnalyseCandidat`. | New `AnalyseCandidatPolicy` — unnecessary when access always goes through the offer. |
| Validation in Form Request | `CandidatureStoreRequest` | Keeps controller thin; reusable validation logic; standard Laravel pattern. | Inline validation in controller — mixes concerns. |

## Risks / Trade-offs

| Risk | Mitigation |
|---|---|
| **Unique constraint blocking re-submission**: A candidate cannot be re-submitted for the same offer. If an RH agent fixes a typo, they cannot resubmit. | For now this is acceptable — duplicates would skew results. A future change can add a "resubmit" flow with status reset. |
| **Placeholder job values may confuse users**: If the demo data looks real, users may think AI is already working. | Use obviously generic placeholder text (e.g., "En attente d'analyse IA") so users understand the pipeline is not yet live. |
| **Long CV text**: Large CVs could cause memory issues. | `cv_texte` uses MySQL `LONGTEXT`; validate minimum 20 chars, no arbitrary max. If needed, a future change can add truncation. |
