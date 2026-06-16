## 1. Ranking on offer detail page

- [x] 1.1 Update `OffreController::show` to partition analyses into completed scored vs others, sort scored by `matching_score` descending, then merge
- [x] 1.2 Update `resources/views/offres/show.blade.php` candidate table to use the sorted collection (add a "Comparer" link/button near the table header)
- [x] 1.3 Verify the N+1 is prevented with `with('candidat')` (already present)

## 2. Comparison controller and routes

- [x] 2.1 Create `ComparaisonController` with `index` (GET — show form) and `compare` (POST — show results) methods
- [x] 2.2 Add routes: `GET /offres/{offre}/comparaison` and `POST /offres/{offre}/comparaison` in `routes/web.php`
- [x] 2.3 Add ownership check via `$this->authorize('view', $offre)` in both controller methods
- [x] 2.4 Add validation: both analysis IDs exist, belong to the route's offre, and are `completed`

## 3. Comparison views

- [x] 3.1 Create `resources/views/offres/comparaison.blade.php` with a form containing two `<select>` dropdowns listing the offer's analyses
- [x] 3.2 Create side-by-side comparison display: candidate name, score, recommendation, extracted skills, strengths, gaps, missing skills, justification
- [x] 3.3 Add conclusion section highlighting stronger candidate based on score
- [x] 3.4 Add "Comparer" link on the offer show page candidate table

## 4. Existing compareCandidates tool review

- [x] 4.1 Verify `CompareCandidates` tool uses saved data only (already implemented — confirm no AI call)
- [x] 4.2 Ensure the tool enforces ownership, different-offer check, and non-completed status check (already implemented)

## 5. Pest tests

- [x] 5.1 Write test: offer show ranks completed scored candidates by score descending
- [x] 5.2 Write test: candidates without score appear after scored candidates
- [x] 5.3 Write test: guest cannot access comparison page (redirect to login)
- [x] 5.4 Write test: user cannot compare candidates from another user's offer (403)
- [x] 5.5 Write test: user cannot compare candidates from different offers (error message)
- [x] 5.6 Write test: user can compare two completed analyses from their own offer (200, sees data)
- [x] 5.7 Write test: comparison page displays both candidates' saved data
- [x] 5.8 Write test: comparison page shows conclusion section with stronger candidate
- [x] 5.9 Write test: comparison page handles non-completed analyses (error message)
- [x] 5.10 Write test: `compareCandidates` tool returns saved comparison data (already exists — verify)
- [x] 5.11 Write test: `compareCandidates` tool refuses unsafe comparison (already exists — verify)
- [x] 5.12 Write test: no real AI API call is made during comparison (mock/fake verification)

## 6. Final checks

- [x] 6.1 Run Pint to fix code style
- [x] 6.2 Run full test suite to confirm all tests pass
- [x] 6.3 Verify no N+1 queries with Laravel Debugbar or log (eager loading already present)
- [x] 6.4 Commit with message `feat: candidate comparison and ranking (AI-assisted)`
