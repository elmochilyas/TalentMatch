# Final Quality Tests

## Purpose

Define the requirements for comprehensive Pest test coverage across all existing TalentMatch features. Tests must verify auth/security boundaries, CRUD operations, candidate submission, AI analysis, display, assistant chat, and candidate comparison/ranking — without calling real AI APIs.

## Requirements

### Requirement: Authentication and security tests
The test suite SHALL verify that unauthenticated users cannot access protected pages, authenticated users can access their own data, and users cannot access offers or analyses belonging to another user.

#### Scenario: Guest is redirected to login
- **WHEN** an unauthenticated user navigates to a protected page (e.g., `/offres`, `/offres/create`, `/offres/1`)
- **THEN** the user is redirected to the login page

#### Scenario: User cannot access another user's offer
- **WHEN** an authenticated user tries to view, edit, update, or delete an offer belonging to another user
- **THEN** a 403 or 404 response is returned

### Requirement: Offres CRUD tests
The test suite SHALL verify that authenticated users can create, list, view, update, and delete their own offers, that required fields are validated, that required skills are stored as JSON/array, and that users cannot modify another user's offers.

#### Scenario: Authenticated user creates a valid offer
- **WHEN** an authenticated user submits valid offer data (titre, description, competences_requises)
- **THEN** the offer is created, competences_requises is stored as a JSON array, and the user is redirected

#### Scenario: Required fields are validated on create
- **WHEN** an authenticated user submits an offer with missing titre or description
- **THEN** validation errors are returned and the offer is not saved

#### Scenario: User lists only their own offers
- **WHEN** an authenticated user navigates to the offers index
- **THEN** only offers belonging to that user are displayed

#### Scenario: User updates own offer
- **WHEN** an authenticated user submits valid update data for their own offer
- **THEN** the offer is updated in the database

#### Scenario: User deletes own offer
- **WHEN** an authenticated user deletes their own offer
- **THEN** the offer is removed from the database

#### Scenario: User cannot update another user's offer
- **WHEN** an authenticated user tries to update an offer belonging to another user
- **THEN** a 403 or 404 response is returned

#### Scenario: User cannot delete another user's offer
- **WHEN** an authenticated user tries to delete an offer belonging to another user
- **THEN** a 403 or 404 response is returned

### Requirement: Candidate submission tests
The test suite SHALL verify that authenticated users can submit a CV to their own offer, that empty or too-short CVs are rejected, that the candidate and analysis rows are created correctly with pending status, and that the analysis job is dispatched.

#### Scenario: User submits valid CV to own offer
- **WHEN** an authenticated user submits a CV text (minimum 20 characters) for their own offer
- **THEN** a candidature row is created, an analyse row is created with "pending" status, and the analysis job is dispatched

#### Scenario: Empty CV is rejected
- **WHEN** an authenticated user submits an empty CV
- **THEN** validation fails with an error message

#### Scenario: Too-short CV is rejected
- **WHEN** an authenticated user submits a CV shorter than 20 characters
- **THEN** validation fails with an error message

#### Scenario: User cannot submit CV to another user's offer
- **WHEN** an authenticated user tries to submit a CV to an offer belonging to another user
- **THEN** a 403 or 404 response is returned

### Requirement: Structured AI analysis tests
The job SHALL handle valid AI output, save analysis with completed status, respect score bounds (0-100), and handle invalid AI responses by setting failed status with an error message. No real external API calls SHALL be made in tests.

#### Scenario: Valid AI output is saved as completed
- **WHEN** the analysis job processes a valid fake AI response with score 0-100 and valid recommendation
- **THEN** the analysis status is "completed" and all JSON fields are saved correctly

#### Scenario: Score outside 0-100 causes failed status
- **WHEN** the AI returns a score below 0 or above 100
- **THEN** the analysis status is "failed" with an appropriate error message

#### Scenario: Invalid recommendation causes failed status
- **WHEN** the AI returns an unrecognized recommendation value
- **THEN** the analysis status is "failed" with an appropriate error message

#### Scenario: Malformed JSON response causes failed status
- **WHEN** the AI returns unparseable JSON
- **THEN** the analysis status is "failed" and message_erreur is populated

#### Scenario: JSON fields are cast as arrays
- **WHEN** an analysis is retrieved from the database
- **THEN** competences_extraites, langues, points_forts, lacunes, and competences_manquantes are arrays (not strings)

### Requirement: Analysis display tests
The test suite SHALL verify that completed analyses display score, recommendation, strengths, gaps, missing skills, and justification; failed analyses show error message; pending/processing analyses have safe display; and users cannot view another user's analysis.

#### Scenario: Completed analysis shows all fields
- **WHEN** an authenticated user views a completed analysis for their own candidate
- **THEN** the score, recommendation label (À convoquer / En attente / À rejeter), points_forts, lacunes, competences_manquantes, and justification are visible

#### Scenario: Failed analysis shows error message
- **WHEN** an authenticated user views a failed analysis for their own candidate
- **THEN** the error message is displayed

#### Scenario: Pending analysis shows safe display
- **WHEN** an authenticated user views a pending or processing analysis for their own candidate
- **THEN** a loading or "analysis in progress" indicator is displayed instead of analysis data

#### Scenario: User cannot view another user's analysis
- **WHEN** an authenticated user tries to view a candidate/analysis belonging to another user
- **THEN** a 403 or 404 response is returned

### Requirement: Assistant tools and chat tests
The `getCandidateAnalysis` tool SHALL return real saved analysis data, `getJobRequirements` SHALL return real offer data, `compareCandidates` SHALL use saved data only, all tools SHALL enforce ownership, and the assistant SHALL safely handle missing data without hallucination.

#### Scenario: getCandidateAnalysis returns real data
- **WHEN** the assistant calls getCandidateAnalysis with a valid candidate ID from the current user's offer
- **THEN** it returns the saved structured analysis from the database

#### Scenario: getJobRequirements returns real offer data
- **WHEN** the assistant calls getJobRequirements with a valid offer ID belonging to the current user
- **THEN** it returns the offer's titre, description, competences_requises, and niveau_experience_minimum

#### Scenario: compareCandidates returns data from same offer
- **WHEN** the assistant calls compareCandidates with two candidate IDs from the same offer belonging to the current user
- **THEN** it returns a comparison of their structured analyses

#### Scenario: Tools enforce ownership
- **WHEN** the assistant calls any tool with an ID belonging to another user
- **THEN** the tool returns an error or empty result, not another user's data

#### Scenario: Assistant refuses to hallucinate missing data
- **WHEN** a candidate has no analysis or fields are null
- **THEN** the assistant reports that the data is missing rather than fabricating values

### Requirement: Ranking and comparison tests
The offer show page SHALL rank completed scored candidates by score descending; unscored/pending candidates SHALL appear after scored candidates; users SHALL be able to compare two completed analyses from the same offer; comparing candidates from different offers SHALL be refused; and users SHALL NOT compare candidates from another user's offer.

#### Scenario: Completed candidates ranked by score descending
- **WHEN** an authenticated user views an offer with multiple completed analyses
- **THEN** candidates are ordered by matching_score from highest to lowest

#### Scenario: Unscored/pending candidates appear after scored
- **WHEN** an offer has both completed and pending/processing analyses
- **THEN** completed (scored) candidates appear first, pending candidates appear after

#### Scenario: Compare two candidates from same offer
- **WHEN** an authenticated user compares two completed analyses from their own offer
- **THEN** the comparison shows both analyses side by side

#### Scenario: Comparing candidates from different offers is refused
- **WHEN** an authenticated user tries to compare candidates from different offers
- **THEN** the system returns an error message

#### Scenario: User cannot compare candidates from another user's offer
- **WHEN** an authenticated user tries to compare candidates from an offer belonging to another user
- **THEN** a 403 or 404 response is returned
