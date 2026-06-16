## 1. Database Migrations

- [x] 1.1 Create migration for `candidats` table (id, nom_candidat, cv_texte, timestamps)
- [x] 1.2 Create migration for `analyses_candidats` table (id, offre_id FK, candidat_id FK, statut_analyse default pending, competences_extraites json nullable, annees_experience nullable, niveau_etudes nullable, langues json nullable, matching_score nullable, points_forts json nullable, lacunes json nullable, competences_manquantes json nullable, recommandation nullable, justification text nullable, message_erreur text nullable, timestamps, unique on offre_id + candidat_id)

## 2. Models

- [x] 2.1 Create `Candidat` model with `$fillable`, `$guarded`, and `hasMany analyseCandidats` relationship
- [x] 2.2 Create `AnalyseCandidat` model with `$fillable`, casts (json columns → array, matching_score → integer, statut_analyse → string, recommandation → string), and `belongsTo offre`, `belongsTo candidat` relationships
- [x] 2.3 Update `Offre` model with `hasMany analyseCandidats` relationship

## 3. Enum Classes (optional but recommended)

- [x] 3.1 Create `StatutAnalyse` enum (pending, processing, completed, failed) if using backed enum
- [x] 3.2 Create `Recommandation` enum (convoquer, attente, rejeter) if using backed enum

## 4. Form Request

- [x] 4.1 Create `CandidatureStoreRequest` with rules: nom_candidat required string max 255, cv_texte required string min 20, and authorization check that the offer belongs to the authenticated user

## 5. Controller and Routes

- [x] 5.1 Create `CandidatureController` with a `store` action that accepts validated request, creates Candidat and AnalyseCandidat with pending status, dispatches `AnalyzeCandidateCvJob`, and redirects back with success
- [x] 5.2 Add POST route for CV submission bound to offer (e.g., `offres.candidatures.store`)

## 6. Job

- [x] 6.1 Create `AnalyzeCandidateCvJob` that sets `statut_analyse` to `processing`, then sets `statut_analyse` to `completed` with safe placeholder values (demo text for strengths/gaps, null for AI-specific fields like matching_score, recommandation)

## 7. Views

- [x] 7.1 Extend offer show page with candidate submission form (nom_candidat input + cv_texte textarea + submit button)
- [x] 7.2 Add candidate/analysis list below the form showing: candidate name, status badge, score (if available), recommendation (if available), created date
- [x] 7.3 Add empty state when no candidates submitted yet
- [x] 7.4 Display validation errors for the submission form

## 8. Tests

- [x] 8.1 Test guest cannot submit CV (redirected to login)
- [x] 8.2 Test authenticated user can submit CV to own offer (candidate + analysis created, status pending, job dispatched)
- [x] 8.3 Test user cannot submit CV to another user's offer (403)
- [x] 8.4 Test empty CV is rejected (validation error)
- [x] 8.5 Test too-short CV (less than 20 chars) is rejected (validation error)
- [x] 8.6 Test candidate row is created on successful submission
- [x] 8.7 Test analysis row is created with pending status on successful submission
- [x] 8.8 Test analysis belongs to correct offer and candidate
- [x] 8.9 Test offer show page displays submitted candidates
- [x] 8.10 Test user cannot see candidates from another user's offer (403)
