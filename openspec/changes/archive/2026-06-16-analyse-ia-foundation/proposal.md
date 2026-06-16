## Why

TalentMatch enables RH agents to prescreen candidates by submitting CV text for AI-powered analysis against a job offer. Without candidate submission and a database foundation to store analysis results, the core value proposition cannot be delivered. This change establishes the data model, submission workflow, and background queue foundation required before the real AI call can be wired up.

## What Changes

- Create `candidats` table and Eloquent model
- Create `analyses_candidats` table and Eloquent model
- Add `hasMany AnalyseCandidat` relationship to existing `Offre` model
- Add candidate submission form with validation on the offer show page
- Add route, controller, and Form Request for CV submission
- Create `AnalyzeCandidateCvJob` as a placeholder background job (no real AI call yet)
- Display submitted candidates and their analysis status on the offer show page
- Enforce authorization: only the owning RH agent can submit/view candidates for their offers
- Write Pest tests for submission, validation, authorization, and data integrity

## Capabilities

### New Capabilities
- `analyse-ia-foundation`: Database models (candidats, analyses_candidats), candidate CV submission with validation, background job placeholder, and UI display of submitted candidates with analysis status

### Modified Capabilities
- *(None — offre-crud remains unchanged; this adds new functionality alongside it)*

## Impact

- **Database**: Two new tables (`candidats`, `analyses_candidats`); foreign keys on `offres` table; unique constraint on `offre_id + candidat_id`
- **Models**: New `Candidat` and `AnalyseCandidat` models; updated `Offre` model with `analyses` relationship
- **Controllers/Requests**: New `CandidatureController` and `CandidatureStoreRequest` for CV submission
- **Jobs**: New `AnalyzeCandidateCvJob` (placeholder – no real AI yet)
- **Views**: Offer show page extended with candidate submission form and candidate/analysis list table
- **Routes**: New `POST` route for CV submission; possibly new named route for viewing
- **Tests**: 10+ Pest tests covering submission, validation, authorization, and relationships
