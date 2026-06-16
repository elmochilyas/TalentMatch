# Offre CRUD

## Purpose

Define the requirements for managing job offers (`offres`) within TalentMatch. Authenticated RH agents can create, list, view, edit, and delete their own offers. Offers serve as targets for future candidate CV submissions and AI analysis.

## Requirements

### Requirement: RH agent can create a job offer
The system SHALL allow an authenticated RH agent to create a new job offer with titre, description, competences_requises (optional, comma-separated stored as JSON array), and niveau_experience_minimum (optional integer). The offer SHALL be associated with the authenticated user. Unauthenticated users SHALL be redirected to login.

#### Scenario: Authenticated user creates a valid offer
- **WHEN** an authenticated RH agent navigates to `/offres/create`, fills in all required fields, and submits the form
- **THEN** the offer is saved to the `offres` table with the agent's `user_id`, and the agent is redirected to the offer's show page

#### Scenario: Unauthenticated user attempts to create an offer
- **WHEN** a guest user navigates to `/offres/create`
- **THEN** the user is redirected to the login page

#### Scenario: Validation fails on create
- **WHEN** an authenticated agent submits the create form with missing required fields (e.g., empty `titre` or empty `description`)
- **THEN** the form is re-displayed with validation errors

### Requirement: RH agent can list their job offers
The system SHALL display a paginated list of the authenticated RH agent's job offers on the index page. Each offer SHALL show its titre, niveau_experience_minimum, and the number of candidate analyses (0 until candidatures are implemented). Unauthenticated users SHALL be redirected to login.

#### Scenario: Authenticated user views their offers
- **WHEN** an authenticated RH agent navigates to `/offres`
- **THEN** the index page displays a list of offers belonging only to that agent

#### Scenario: Unauthenticated user views offers
- **WHEN** a guest user navigates to `/offres`
- **THEN** the user is redirected to the login page

### Requirement: RH agent can view a single job offer
The system SHALL allow an authenticated RH agent to view the full details of one of their own offers, including titre, description, competences_requises, and niveau_experience_minimum.

#### Scenario: Authenticated user views their own offer
- **WHEN** an authenticated RH agent navigates to `/offres/{id}` for an offer they own
- **THEN** the show page displays all offer details

#### Scenario: Authenticated user views another agent's offer
- **WHEN** an authenticated RH agent navigates to `/offres/{id}` for an offer owned by a different agent
- **THEN** a 403 Forbidden response is returned

### Requirement: RH agent can edit their job offer
The system SHALL allow an authenticated RH agent to update the titre, description, competences_requises, and niveau_experience_minimum of their own offer.

#### Scenario: Authenticated user edits their own offer
- **WHEN** an authenticated RH agent navigates to `/offres/{id}/edit`, modifies fields, and submits
- **THEN** the offer is updated and the agent is redirected to the offer's show page

#### Scenario: Authenticated user edits another agent's offer
- **WHEN** an authenticated RH agent navigates to `/offres/{id}/edit` for an offer owned by a different agent
- **THEN** a 403 Forbidden response is returned

#### Scenario: Validation fails on update
- **WHEN** an authenticated agent submits the edit form with empty `titre`
- **THEN** the form is re-displayed with validation errors

### Requirement: RH agent can delete their job offer
The system SHALL allow an authenticated RH agent to delete one of their own offers from the database.

#### Scenario: Authenticated user deletes their own offer
- **WHEN** an authenticated RH agent clicks the delete button on their own offer
- **THEN** the offer is deleted from the database and the agent is redirected to the offers index page

#### Scenario: Authenticated user deletes another agent's offer
- **WHEN** an authenticated RH agent attempts to delete an offer owned by a different agent
- **THEN** a 403 Forbidden response is returned
