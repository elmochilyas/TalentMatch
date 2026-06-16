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

### Requirement: Background job placeholder
The system SHALL dispatch a job after CV submission that simulates the analysis lifecycle without calling a real AI API.

#### Scenario: Job marks analysis as processing
- **WHEN** `AnalyzeCandidateCvJob` runs
- **THEN** `statut_analyse` SHALL be set to `processing`

#### Scenario: Job marks analysis as completed with placeholder data
- **WHEN** `AnalyzeCandidateCvJob` finishes
- **THEN** `statut_analyse` SHALL be set to `completed`
- **AND** the analysis result fields SHALL contain safe placeholder/demo values

### Requirement: Display candidates on offer page
The offer show page SHALL display submitted candidates with their analysis status.

#### Scenario: Candidates are listed on offer page
- **WHEN** an authenticated user views their offer page that has submitted candidates
- **THEN** each candidate SHALL be displayed with: candidate name, analysis status, score (if available), recommendation (if available), created date

#### Scenario: Empty state is shown
- **WHEN** an authenticated user views their offer page with no submitted candidates
- **THEN** the page SHALL display a clear empty state message

#### Scenario: User cannot see another user's offer candidates
- **WHEN** an authenticated user navigates to another user's offer page
- **THEN** the system SHALL return a 403 Forbidden error
