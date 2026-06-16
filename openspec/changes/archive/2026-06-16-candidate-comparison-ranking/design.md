## Context

TalentMatch already has offers, candidates, structured AI analysis (`analyses_candidats`), and an assistant with tools (`compareCandidates`, `getCandidateAnalysis`, `getJobRequirements`). The offer detail page lists candidates in an unsorted table. RH agents must manually scan individual analysis pages to compare candidates.

## Goals / Non-Goals

**Goals:**
- Offer detail page ranks candidates by `matching_score` descending (completed scored first).
- New comparison page lets RH agents select two analyses from the same offer and view saved data side-by-side.
- Comparison uses only saved `analyses_candidats` data — no re-running AI.
- The existing `compareCandidates` assistant tool continues to work correctly.
- Ownership/security enforced through the related offer.

**Non-Goals:**
- Re-running AI analysis.
- Changing the scoring algorithm.
- Export PDF.
- Real-time auto-refresh.
- README or deployment changes.

## Decisions

1. **In-view sorting for ranking (no separate service class)**
   - The ranking logic is a simple sort of the loaded collection in the controller. A dedicated service class would be over-engineered for a `sortBy` + `partition` operation.
   - Alternative considered: database-level `ORDER BY` — but status and null-score logic is easier to express in PHP via collection partitioning.
   - Decision: Use collection partitioning in the `show` method: separate completed scored analyses from others, sort scored by `matching_score` descending, then concatenate.

2. **Comparison as a separate controller action not a modal**
   - Dedicated page (`GET|POST /offres/{offre}/comparaison`) is simpler to implement and test than a modal with JavaScript state.
   - The GET route shows a form with two `<select>` dropdowns listing analyses; the POST route processes and shows results.
   - Alternative considered: inline comparison on the offer detail page — rejected as it would clutter the listing.
   - Decision: New `ComparaisonController` with `index` and `show` (or `compare`) methods.

3. **Comparison uses `analyse_candidat` IDs, not `candidat` IDs**
   - The existing `compareCandidates` assistant tool uses `candidat_id` (Candidat model). For the web comparison, passing `analyse_candidat` IDs is more precise (a candidate could appear in multiple offers).
   - The controller will look up both analyses by their IDs and render side-by-side in Blade.
   - Alternative considered: reuse tool output — but parsed markdown is harder to display in a styled table.
   - Decision: Controller queries analyses directly; tool stays separate for the assistant.

4. **Ownership checked via the offer policy**
   - The comparison routes are nested under `{offre}` (like existing routes). The policy `view` check on the offer ensures the RH agent owns it.
   - Both analyses must have `offre_id` matching the route `offre`.
   - Edge case: if analyses are from a different `offre_id`, return a clear error.

## Risks / Trade-offs

- [Risk] An RH agent selects two analyses where one is pending/processing/failed. → Mitigation: validate `statut_analyse === 'completed'` for both and show a clear error if not.
- [Risk] The page could be slow if an offer has hundreds of analyses. → Mitigation: N+1 prevention via `with('candidat')`; keep it simple for now since typical offers have <50 candidates.
- [Risk] The `compareCandidates` assistant tool queries by `candidat_id` which could be ambiguous if the same candidate appears in multiple offers. → Mitigation: this is an existing behavior; the tool already checks ownership and offers, so it would only return the first match. This is acceptable and documented demo note.
