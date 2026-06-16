# Structured AI Analysis

## Purpose

Define the requirements for the real AI-powered structured CV analysis within TalentMatch. This covers the AI provider call via `laravel/ai`, prompt building from offer context, strict JSON schema enforcement, response validation, error handling, and enum wiring on the AnalyseCandidat model.

## ADDED Requirements

### Requirement: AI analysis job calls provider with structured output

The `AnalyzeCandidateCvJob` SHALL call a configured AI provider via `laravel/ai` to produce a structured CV analysis.

#### Scenario: Job builds prompt from offer and candidate data
- **WHEN** `AnalyzeCandidateCvJob::handle()` executes
- **THEN** the prompt SHALL include: offer title, offer description, required skills (competences_requises), minimum experience level (experience_requise), and candidate CV text (cv_texte)

#### Scenario: Job uses anonymous agent with schema
- **WHEN** the job calls the AI provider
- **THEN** it SHALL use an anonymous `agent()` with a `schema()` defining all required fields, types, and constraints

#### Scenario: Job sets status to processing before AI call
- **WHEN** the job starts executing
- **THEN** `statut_analyse` SHALL be set to `processing` before any AI call

#### Scenario: Job sets status to completed on valid AI response
- **WHEN** the AI returns a valid structured response passing all validation checks
- **THEN** `statut_analyse` SHALL be set to `completed`
- **AND** all analysis fields SHALL be persisted to the database

#### Scenario: Job sets status to failed on invalid response
- **WHEN** the AI response fails validation
- **THEN** `statut_analyse` SHALL be set to `failed`
- **AND** `message_erreur` SHALL contain a descriptive error message

#### Scenario: Job sets status to failed on exception
- **WHEN** an exception occurs during the AI call or validation
- **THEN** `statut_analyse` SHALL be set to `failed`
- **AND** `message_erreur` SHALL contain the exception message

### Requirement: Strict JSON schema enforcement

The AI analysis SHALL produce output matching the defined JSON structure. The system SHALL validate the response before saving.

#### Scenario: All required fields are present
- **WHEN** the AI returns a structured response
- **THEN** it MUST contain all fields: `competences_extraites`, `annees_experience`, `niveau_etudes`, `langues`, `matching_score`, `points_forts`, `lacunes`, `competences_manquantes`, `recommandation`, `justification`

#### Scenario: Matching score is validated as integer 0-100
- **WHEN** `matching_score` is present
- **THEN** it SHALL be an integer between 0 and 100 inclusive
- **AND** if the score is outside this range, the analysis SHALL be marked as `failed`

#### Scenario: Recommendation is validated against allowed values
- **WHEN** `recommandation` is present
- **THEN** it SHALL be one of: `convoquer`, `attente`, `rejeter`
- **AND** if the value is not allowed, the analysis SHALL be marked as `failed`

#### Scenario: Array fields are validated as arrays
- **WHEN** the AI returns `competences_extraites`, `langues`, `points_forts`, `lacunes`, or `competences_manquantes`
- **THEN** each SHALL be a PHP array after casting
- **AND** if any is not an array, the analysis SHALL be marked as `failed`

#### Scenario: Justification is a non-empty string
- **WHEN** `justification` is present
- **THEN** it SHALL be a non-empty string

### Requirement: AI safety rules for data integrity

The AI SHALL NOT invent data. Uncertainty MUST be communicated clearly.

#### Scenario: AI must not invent skills or experience
- **WHEN** the CV text does not mention a specific skill, language, or education
- **THEN** the AI SHALL NOT include it in the output
- **AND** if the CV is unclear, the AI SHALL note uncertainty in `justification`

#### Scenario: Empty offer skills are handled safely
- **WHEN** the offer has empty or null `competences_requises`
- **THEN** the job SHALL still proceed with the prompt omitting required skills or noting they are empty
- **AND** the job SHALL NOT fail due to empty offer skills

### Requirement: Enum wiring on AnalyseCandidat model

The `AnalyseCandidat` model SHALL use `StatutAnalyse` and `Recommandation` PHP enums for the `statut_analyse` and `recommandation` casts.

#### Scenario: StatutAnalyse enum cast works
- **WHEN** an `AnalyseCandidat` is retrieved with a valid `statut_analyse`
- **THEN** `$analyse->statut_analyse` SHALL return a `StatutAnalyse` enum instance

#### Scenario: Recommendation enum cast works
- **WHEN** an `AnalyseCandidat` is retrieved with a valid `recommandation`
- **THEN** `$analyse->recommandation` SHALL return a `Recommandation` enum instance or null

### Requirement: Display real analysis data on offer page

The offer show page SHALL display real (non-placeholder) analysis data for each submitted candidate.

#### Scenario: Completed analysis shows score and recommendation
- **WHEN** an authenticated user views their offer page with a completed analysis
- **THEN** the candidate row SHALL display: matching score (integer), recommendation label (À convoquer / En attente / À rejeter), and analysis status

#### Scenario: Processing analysis shows processing indicator
- **WHEN** an analysis is in `processing` status
- **THEN** the candidate row SHALL display a processing indicator

#### Scenario: Failed analysis shows error message
- **WHEN** an analysis is in `failed` status with a non-null `message_erreur`
- **THEN** the candidate row SHALL display the error message

### Requirement: Display real analysis data on candidate detail page

The candidate detail page SHALL display the full structured analysis result.

#### Scenario: Completed analysis shows all fields
- **WHEN** an authenticated user views a candidate detail page for a completed analysis
- **THEN** the page SHALL display: score (0-100), recommendation label, extracted skills, years experience, education level, languages, strengths (points_forts), gaps (lacunes), missing skills (competences_manquantes), and justification

#### Scenario: Recommendation label matches recommendation value
- **WHEN** `recommandation` is `convoquer`
- **THEN** the label SHALL display "À convoquer"
- **WHEN** `recommandation` is `attente`
- **THEN** the label SHALL display "En attente"
- **WHEN** `recommandation` is `rejeter`
- **THEN** the label SHALL display "À rejeter"

### Requirement: AI provider configuration via environment

The AI provider, model, and API key SHALL be configured via environment variables.

#### Scenario: Default provider is configurable via .env
- **WHEN** `AI_PROVIDER` env var is set
- **THEN** the job SHALL use that provider
- **AND** if not set, the SHALL fall back to a sensible default

#### Scenario: API keys are not exposed in logs or UI
- **WHEN** an exception occurs during the AI call
- **THEN** the full exception message SHALL NOT contain API keys or secrets in the saved `message_erreur`
