## Why

RH agents need to quickly compare candidates and see them ranked by relevance to make informed hiring decisions. Without ranking and comparison, agents must manually scan individual analysis pages to decide who to prioritize. This feature adds immediate value with zero extra AI cost by using already-saved analysis data.

## What Changes

- Offer detail page displays analyzed candidates ranked by `matching_score` descending.
- Completed scored analyses appear first; pending/processing/failed appear after.
- New comparison page where RH agent selects two candidates from the same offer and sees a side-by-side view of their saved analysis data.
- Automatic conclusion section highlighting which candidate is stronger based on scores and analysis.
- The existing `compareCandidates` assistant tool is wired to use real saved database data (was already defined in specs, now fully implemented).
- Security/ownership enforced via the related offer.

## Capabilities

### New Capabilities
- `candidate-comparison-ranking`: Ranking of candidates by score on the offer detail page, and a new comparison page for side-by-side candidate analysis comparison.

### Modified Capabilities

*(No existing spec requirements are changing. The ranking is a UI enhancement on the offer detail page; the comparison is a new page. Both use existing data structures.)*

## Impact

- **Routes**: Two new routes — `GET|POST /offres/{offre}/comparaison`
- **Controllers**: New `ComparaisonController` or comparable action
- **Views**: Update `offres/show` candidate list + new comparison view
- **Tool wiring**: `compareCandidates` tool uses database queries instead of AI
- **Tests**: New Pest tests for ranking, comparison, ownership, edge cases
- **No new migrations**: Uses existing `candidatures` table and its JSON/structured analysis data
- **No new dependencies**: Everything uses existing Laravel, Blade, Tailwind
