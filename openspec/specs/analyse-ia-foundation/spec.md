# Analyse IA Foundation

## Purpose

Define the requirements for the candidate submission and analysis foundation within TalentMatch. This covers the database models (`candidats`, `analyses_candidats`), candidate CV submission against a job offer, a background job placeholder for AI analysis, and the display of submitted candidates with their analysis status on the offer show page.

## Requirements

### Requirement: Database models and migrations
The system SHALL provide `candidats` and `analyses_candidats` tables matching the validated MLD.

#### Scenario: Candidats table exists with correct columns
- **WHEN** migration is run
- **THEN** the `candidats` table SHALL exist with columns: `id` (bigint auto-increment PK), `nom_candidat` (varchar 255 NOT NULL), `cv_texte` (longtext NOT NULL), `created_at`, `updated_at`

#### Scenario: Analyses candidats table exists with correct columns
- **WHEN** migration is run
- **THEN** the `analyses_candidats` table SHALL exist with columns: `id` (bigint auto-increment PK), `offre_id` (bigint FK NOT NULL), `candidat_id` (bigint FK NOT NULL), `statut_analyse` (varchar 20 NOT NULL default 'pending'), `competences_extraites` (json nullable), `annees_experience` (smallint unsigned nullable), `niveau_etudes` (varchar 255 nullable), `langues` (json nullable), `matching_score` (tinyint unsigned nullable), `points_forts` (json nullable), `lacunes` (json nullable), `competences_manquantes` (json nullable), `recommandation` (varchar 20 nullable), `justification` (text nullable), `message_erreur` (text nullable), `created_at`, `updated_at`

#### Scenario: Unique constraint prevents duplicate analysis
- **WHEN** two rows with the same `offre_id` and `candidat_id` are inserted
- **THEN** the second insert SHALL fail with a unique constraint violation

#### Scenario: Foreign keys cascade on delete
- **WHEN** an offre or candidat is deleted
- **THEN** corresponding analyses_candidats rows SHALL be deleted

### Requirement: Eloquent relationships and casts
The system SHALL provide Candidat and AnalyseCandidat models with correct relationships and casts.

#### Scenario: AnalyseCandidat belongs to Offre
- **WHEN** an AnalyseCandidat instance is loaded
- **THEN** `$analyse->offre` SHALL return the related Offre model

#### Scenario: AnalyseCandidat belongs to Candidat
- **WHEN** an AnalyseCandidat instance is loaded
- **THEN** `$analyse->candidat` SHALL return the related Candidat model

#### Scenario: Offre has many AnalyseCandidat
- **WHEN** an Offre instance with analyses is loaded
- **THEN** `$offre->analyses` SHALL return a collection of AnalyseCandidat models

#### Scenario: Candidat has many AnalyseCandidat
- **WHEN** a Candidat instance with analyses is loaded
- **THEN** `$candidat->analyses` SHALL return a collection of AnalyseCandidat models

#### Scenario: JSON columns are cast to arrays
- **WHEN** an AnalyseCandidat is retrieved with non-null `competences_extraites`, `langues`, `points_forts`, `lacunes`, or `competences_manquantes`
- **THEN** the value SHALL be a PHP array, not a string

#### Scenario: Matching score is cast to integer
- **WHEN** an AnalyseCandidat is retrieved
- **THEN** `$analyse->matching_score` SHALL be an integer or null

### Requirement: Candidate CV submission
An authenticated RH agent SHALL be able to submit a candidate CV against one of their own offers from the offer show page.

#### Scenario: Successful submission
- **WHEN** an authenticated user submits valid `nom_candidat` and `cv_texte` for their own offer
- **THEN** a Candidat row SHALL be created
- **AND** an AnalyseCandidat row SHALL be created linked to the offer and candidat with `statut_analyse = 'pending'`
- **AND** `AnalyzeCandidateCvJob` SHALL be dispatched
- **AND** the user SHALL be redirected back with a success message

#### Scenario: Missing nom candidat is rejected
- **WHEN** a submission has an empty `nom_candidat`
- **THEN** the system SHALL return a validation error for `nom_candidat`

#### Scenario: Empty CV is rejected
- **WHEN** a submission has an empty `cv_texte`
- **THEN** the system SHALL return a validation error for `cv_texte`

#### Scenario: Too-short CV is rejected
- **WHEN** a submission has a `cv_texte` shorter than 20 characters
- **THEN** the system SHALL return a validation error for `cv_texte`

#### Scenario: Guest cannot submit
- **WHEN** an unauthenticated visitor submits to any offer
- **THEN** the system SHALL redirect to login

#### Scenario: User cannot submit to another user's offer
- **WHEN** an authenticated user submits to an offer they do not own
- **THEN** the system SHALL return a 403 Forbidden error

### Requirement: Background AI analysis job
The system SHALL dispatch a job after CV submission that performs real structured AI analysis using the configured AI provider.

#### Scenario: Job marks analysis as processing
- **WHEN** `AnalyzeCandidateCvJob` runs
- **THEN** `statut_analyse` SHALL be set to `processing`

#### Scenario: Job calls AI provider with structured output
- **WHEN** `AnalyzeCandidateCvJob` executes
- **THEN** it SHALL call the configured AI provider via `laravel/ai` with a schema for strict JSON output
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
