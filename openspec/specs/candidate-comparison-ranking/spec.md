# Candidate Comparison & Ranking

## Problem

RH agents need to quickly compare candidates and see them ranked by relevance to make informed hiring decisions. Without ranking and comparison, agents must manually scan individual analysis pages or switch tabs to evaluate who to prioritize.

## User stories covered

- TAL-12: Bonus — Comparer deux candidats pour une même offre
- TAL-13: Bonus — Classer automatiquement les candidats par score

## Scope

- Offer detail page ranks analyzed candidates by `matching_score` descending
- Completed scored analyses appear first; pending/processing/failed analyses appear after
- A dedicated comparison page lets the RH agent select two analyses from the same offer and view saved data side-by-side
- A conclusion section highlights which candidate is stronger based on saved data
- The existing `compareCandidates` assistant tool continues to use only saved data

## Out of scope

- Re-running AI analysis
- Changing the scoring algorithm
- PDF export
- README finalization
- Deployment

## Data model impact

None. Uses existing `analyses_candidats` table and its structured fields.

## UI impact

- **Offer detail page**: The candidate table is sorted — completed scored candidates first (by score descending), then pending/processing/failed candidates.
- **Comparison page** (`GET|POST /offres/{offre}/comparaison`): New page with a two-select form and a side-by-side result view.
- **Offer detail page**: Add a "Comparer" button/link pointing to the comparison page.

## Validation rules

- Both selected analysis IDs MUST exist and belong to the route's `offre`
- The offer MUST be owned by the authenticated user
- Both analyses MUST be in `completed` status (for the comparison result view)
- The comparison page GET form SHALL show all analyses of the offer regardless of status
- On POST, if either analysis is not completed, a clear message SHALL be displayed instead of data

## Acceptance criteria

1. Offer detail page ranks completed scored candidates by `matching_score` descending
2. Candidates without a score (pending, processing, failed) appear after scored candidates
3. RH agent can navigate to the comparison page from the offer detail
4. RH agent can select two analyses and see them side by side
5. Comparison uses saved `analyses_candidats` data only — no AI re-analysis
6. Security/ownership enforced: guest redirected, other user's offer returns 403
7. If analyses are from different offers, a clear error is shown
8. If either analysis is not completed, a clear error is shown
9. A conclusion section compares scores and highlights the stronger candidate
10. The existing `compareCandidates` assistant tool continues to work correctly

## Edge cases

- Only one completed analysis exists: comparison page shows an appropriate message
- Both analyses have the same score: conclusion states they are tied
- Analysis fields are null: "Non disponible" is displayed
- Offer has zero analyses: comparison form shows empty selects
- User navigates directly to comparison URL with invalid IDs: validation error returned
- `compareCandidates` tool called with analyses from different offers: returns incompatibility message
- `compareCandidates` tool called with non-completed analyses: returns status explanation

## Security / authorization rules

- All routes behind `auth` and `verified` middleware
- Ownership enforced via `OffrePolicy::view` on the route's `offre`
- Both analysis IDs validated to belong to the route's `offre`

## Demo explanation notes

- "Ranking: candidates are sorted by matching score on the offer detail page. The sort is done in PHP after loading, not in SQL, because we need to partition by status first."
- "Comparison: the side-by-side view uses saved analysis data only. No AI call is made during comparison. The page checks that both analyses belong to the same offer and are completed before showing results."
- "Security: the comparison routes are nested under the offer route, and we use the existing OffrePolicy to verify ownership."

## Requirements

### Requirement: Rank candidates by score on offer detail

The offer detail page SHALL display analyzed candidates ordered by `matching_score` descending. Completed analyses with a score SHALL appear first. Analyses with `pending`, `processing`, or `failed` status (or with a null `matching_score`) SHALL appear after completed scored analyses.

#### Scenario: Completed scored candidates appear first in descending order

- **WHEN** an offer has multiple completed analyses with different scores
- **THEN** the offer detail page lists them sorted by `matching_score` descending before any non-completed analyses

#### Scenario: Non-completed analyses appear after scored ones

- **WHEN** an offer has both completed scored analyses and pending/processing/failed analyses
- **THEN** the pending/processing/failed analyses are displayed after all completed scored analyses

### Requirement: Compare two candidates side by side

The system SHALL provide a comparison page at `GET|POST /offres/{offre}/comparaison` where the RH agent can select two analyses from the same offer and view their saved data side by side. The comparison SHALL display: candidate name, score, recommendation, extracted skills, strengths, gaps, missing skills, and justification for each. A conclusion section SHALL highlight which candidate is stronger based on score and analysis data. The page SHALL NOT call any AI analysis. If either analysis is not completed, a clear message SHALL be displayed instead of data.

#### Scenario: User can compare two completed analyses from their own offer

- **WHEN** an authenticated user selects two completed analyses from their own offer
- **THEN** the comparison page shows both candidates' saved data side by side with a conclusion section

#### Scenario: Guest cannot access comparison page

- **WHEN** an unauthenticated user accesses the comparison page
- **THEN** they are redirected to login

#### Scenario: User cannot compare analyses from another user's offer

- **WHEN** an authenticated user accesses the comparison page for an offer belonging to another user
- **THEN** the system returns a 403 Forbidden response

#### Scenario: User cannot compare analyses from different offers

- **WHEN** an authenticated user submits a comparison with two analyses that belong to different offers
- **THEN** the system returns a clear error message

#### Scenario: Comparison page handles non-completed analyses

- **WHEN** a user submits a comparison with one or both analyses not in `completed` status
- **THEN** the system shows a clear message explaining the status instead of comparison data

#### Scenario: Comparison conclusion identifies stronger candidate

- **WHEN** two completed analyses with different scores are compared
- **THEN** the conclusion section identifies the candidate with the higher score as stronger for the offer

### Requirement: compareCandidates tool uses saved data only

The existing `compareCandidates` assistant tool SHALL continue to use saved database data only. The tool SHALL NOT call any AI analysis. The tool SHALL verify ownership, compare only candidates from the same offer, and require both analyses to be completed. The tool SHALL return a clear explanation when comparison is not possible.

#### Scenario: compareCandidates returns saved comparison data

- **WHEN** the assistant calls `compareCandidates` with two valid candidate IDs whose analyses are completed and owned by the user
- **THEN** the tool returns a formatted comparison using only saved database data

#### Scenario: compareCandidates refuses candidates from different offers

- **WHEN** the assistant calls `compareCandidates` with candidates from different offers
- **THEN** the tool returns an incompatibility message

#### Scenario: compareCandidates refuses non-completed analyses

- **WHEN** the assistant calls `compareCandidates` with one or both analyses not completed
- **THEN** the tool returns a message with the current status of each analysis

#### Scenario: compareCandidates refuses missing candidates

- **WHEN** the assistant calls `compareCandidates` with one or both IDs that do not exist
- **THEN** the tool returns a not-found message
