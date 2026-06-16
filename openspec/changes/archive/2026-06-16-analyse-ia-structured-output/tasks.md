## 1. Enum Wiring & Model Casts

- [x] 1.1 Add `StatutAnalyse` enum casts and `Recommandation` enum casts to `AnalyseCandidat` model
- [x] 1.2 Verify existing enums (`App\Enums\StatutAnalyse`, `App\Enums\Recommandation`) are complete and match required values
- [x] 1.3 Add `AnalyseStatus` and `RecommendationType` enums if not already present (as fallback names)

## 2. AI Configuration

- [x] 2.1 Add `AI_PROVIDER`, `AI_MODEL` environment variables to `.env.example`
- [x] 2.2 Verify `config/ai.php` has a default provider and model configured
- [x] 2.3 Add `ai.php` config file if it does not exist with provider settings

## 3. AnalyzeCandidateCvJob — Real AI Implementation

- [x] 3.1 Update `AnalyzeCandidateCvJob::handle()`: load analysis with related offre and candidat, set `statut_analyse` to `processing`, save
- [x] 3.2 Build AI prompt from offer data (title, description, competences_requises, experience_requise) and candidate CV text
- [x] 3.3 Call AI provider using dedicated `AnalyseCvAgent` with `schema()` defining the strict JSON structure
- [x] 3.4 Parse the AI response and validate all fields (types, score 0-100, valid recommendation enum, array fields, non-empty justification)
- [x] 3.5 Save validated analysis fields to `analyses_candidats` and set `statut_analyse` to `completed`
- [x] 3.6 Handle validation failures: set `statut_analyse` to `failed` with descriptive `message_erreur`
- [x] 3.7 Handle exceptions: catch and set `statut_analyse` to `failed` with sanitized `message_erreur` (no secrets exposed)
- [x] 3.8 Handle empty/missing offer required skills edge case safely

## 4. UI — Display Real Analysis Data

- [x] 4.1 Update offer show page candidate list to display real score, recommendation label, and analysis status (processing indicator, error message for failed)
- [x] 4.2 Create candidate detail page to show full analysis data: score, recommendation with label, extracted skills, experience, education, languages, strengths, gaps, missing skills, justification
- [x] 4.3 Add recommendation label mapping helper: `convoquer` → "À convoquer", `attente` → "En attente", `rejeter` → "À rejeter"

## 5. Testing

- [x] 5.1 Write test: job sets analysis to processing then completed on valid AI output
- [x] 5.2 Write test: valid structured output is saved correctly (all fields)
- [x] 5.3 Write test: arrays are saved and cast correctly (competences_extraites, langues, points_forts, lacunes, competences_manquantes)
- [x] 5.4 Write test: score is saved as integer 0-100
- [x] 5.5 Write test: recommendation is saved as valid enum value
- [x] 5.6 Write test: invalid score outside 0-100 causes failed status
- [x] 5.7 Write test: invalid recommendation value causes failed status
- [x] 5.8 Write test: malformed/missing field AI response causes failed status
- [x] 5.9 Write test: failed AI call exception saves `message_erreur`
- [x] 5.10 Write test: empty/missing offer required skills handled safely (job still completes)
- [x] 5.11 Write test: no real external API call is made during tests (mock/fake verification)

## 6. Final Verification

- [x] 6.1 Run `vendor/bin/pint --format agent` to fix code style
- [x] 6.2 Run full test suite: `php artisan test --compact`
- [ ] 6.3 Verify UI renders correctly for all analysis states (pending, processing, completed, failed)
