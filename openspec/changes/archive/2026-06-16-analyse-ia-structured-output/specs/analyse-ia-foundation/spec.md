# Analyse IA Foundation — Delta for Structured AI Analysis

## Purpose

Delta spec for the modified `analyse-ia-foundation` capability. The background job placeholder is replaced with real AI analysis. The display requirements are updated to show real (non-placeholder) data. Enum casts are wired on the model.

## MODIFIED Requirements

### Requirement: Background job placeholder

The system SHALL dispatch a job after CV submission that performs real structured AI analysis using the configured AI provider.

#### Scenario: Job marks analysis as processing
- **WHEN** `AnalyzeCandidateCvJob` runs
- **THEN** `statut_analyse` SHALL be set to `processing`

#### Scenario: Job calls AI provider with structured output
- **WHEN** `AnalyzeCandidateCvJob` executes
- **THEN** it SHALL call the configured AI provider via `laravel/ai` anonymous agent with a schema for strict JSON output
- **AND** the prompt SHALL include offer details (title, description, required skills, experience level) and candidate CV text

#### Scenario: Job validates and saves structured result
- **WHEN** the AI returns a response passing all validation checks
- **THEN** `statut_analyse` SHALL be set to `completed`
- **AND** the following fields SHALL be saved: `competences_extraites`, `annees_experience`, `niveau_etudes`, `langues`, `matching_score`, `points_forts`, `lacunes`, `competences_manquantes`, `recommandation`, `justification`

#### Scenario: Job handles invalid AI response gracefully
- **WHEN** the AI response fails validation (missing fields, wrong types, invalid score range, invalid recommendation)
- **THEN** `statut_analyse` SHALL be set to `failed`
- **AND** `message_erreur` SHALL contain a descriptive error message

#### Scenario: Job handles exceptions gracefully
- **WHEN** an unexpected exception occurs during AI call or processing
- **THEN** `statut_analyse` SHALL be set to `failed`
- **AND** `message_erreur` SHALL contain the exception message

### Requirement: Display candidates on offer page

The offer show page SHALL display submitted candidates with their analysis status and real analysis data.

#### Scenario: Completed analysis shows score and recommendation
- **WHEN** an authenticated user views their offer page that has a candidate with completed analysis
- **THEN** the candidate row SHALL display: candidate name, analysis status, matching score (integer), recommendation label (À convoquer / En attente / À rejeter), and created date

#### Scenario: Processing analysis shows processing indicator
- **WHEN** an analysis is in `processing` status
- **THEN** the candidate row SHALL display a processing indicator instead of score/recommendation

#### Scenario: Failed analysis shows error message
- **WHEN** an analysis is in `failed` status
- **THEN** the candidate row SHALL display the error status and `message_erreur`

#### Scenario: Empty state is shown
- **WHEN** an authenticated user views their offer page with no submitted candidates
- **THEN** the page SHALL display a clear empty state message

#### Scenario: User cannot see another user's offer candidates
- **WHEN** an authenticated user navigates to another user's offer page
- **THEN** the system SHALL return a 403 Forbidden error

## ADDED Requirements

### Requirement: Eloquent enum casts

The `AnalyseCandidat` model SHALL cast `statut_analyse` to `StatutAnalyse` enum and `recommandation` to `Recommandation` enum.

#### Scenario: StatutAnalyse cast
- **WHEN** an `AnalyseCandidat` is retrieved
- **THEN** `$analyse->statut_analyse` SHALL be a `StatutAnalyse` enum instance

#### Scenario: Recommendation cast
- **WHEN** an `AnalyseCandidat` with a non-null `recommandation` is retrieved
- **THEN** `$analyse->recommandation` SHALL be a `Recommandation` enum instance
- **WHEN** an `AnalyseCandidat` with null `recommandation` is retrieved
- **THEN** `$analyse->recommandation` SHALL be null

### Requirement: AI provider configuration

The AI provider and model SHALL be configurable via environment variables without code changes.

#### Scenario: Provider configured via env
- **WHEN** `AI_PROVIDER` is set in `.env`
- **THEN** the job SHALL use that provider
- **WHEN** `AI_PROVIDER` is not set
- **THEN** the job SHALL use the default provider from `config/ai.php`

#### Scenario: Model configured via env
- **WHEN** `AI_MODEL` is set in `.env`
- **THEN** the job SHALL use that model
- **WHEN** `AI_MODEL` is not set
- **THEN** the job SHALL use the default model from `config/ai.php`
