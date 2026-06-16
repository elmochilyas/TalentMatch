# Assistant Conversationnel

## Problem
RH agents cannot interact conversationally with candidate analysis data. They must manually scan the analysis detail page, compare candidates by switching tabs, and lack a way to ask follow-up questions about specific analyses.

## User stories covered
- TAL-9: Rh agent asks a question about a specific analyzed candidate
- TAL-10: Conversation memory persists across follow-up messages
- TAL-11: Real Laravel tools provide data to the assistant

## Scope
- Chat interface embedded in the analysis detail page
- Three Laravel AI tools: `getCandidateAnalysis`, `getJobRequirements`, `compareCandidates`
- Conversation memory via existing laravel/ai SDK tables
- Ownership enforcement (user can only chat about their own analyses)

## Out of scope
- Re-running CV analysis
- Editing analysis results
- Standalone candidate comparison dashboard
- Deploy / infrastructure

## Data model impact
None. The existing `agent_conversations` and `agent_conversation_messages` tables from the laravel/ai SDK migration are sufficient. No new columns, tables, or indexes.

## UI impact
- A chat section appended below the existing analysis detail on `offres/analyse-show.blade.php`
- Message list (user messages on right, assistant messages on left) with loading spinner
- Text input with submit button
- Alpine.js-driven fetch for POST and response rendering

## Validation rules
- Message text MUST be required, string, min 1, max 2000 characters
- Analysis MUST exist and belong to an offer owned by the authenticated user
- Analysis MUST be in `completed` status (not `pending`, `processing`, or `failed`)
- If analysis is not completed, the assistant SHALL return a message saying analysis is not yet available

## Acceptance criteria
1. Authenticated RH agent can send a message about their own candidate analysis
2. Assistant returns a response based on real tool data (not hallucinated)
3. Follow-up questions in the same conversation retain context
4. Assistant does not invent scores, skills, languages, education, or experience
5. Assistant clearly says when data is unavailable
6. Guest user gets redirected to login
7. User cannot send messages about another user's analysis (403)
8. `getCandidateAnalysis` returns real saved analysis data
9. `getJobRequirements` returns real offer data
10. `compareCandidates` compares two saved analyses using structured data only
11. If data fields are null/missing, assistant reports them as unavailable
12. If analysis status is not `completed`, assistant explains the status

## Tests to add
- Guest cannot access assistant chat
- Authenticated user can ask a question about their own analysis
- User cannot ask about another user's analysis
- `getCandidateAnalysis` returns real saved analysis data
- `getJobRequirements` returns real offer data
- `compareCandidates` compares two saved analyses
- Assistant refuses or safely handles missing/null data fields
- Follow-up question preserves conversation context
- Analysis in pending/processing/failed state returns appropriate message
- Message validation (empty, too long)

## Edge cases
- Analysis is still `pending` or `processing`: assistant explains it is not ready
- Analysis is `failed`: assistant explains the error
- `getCandidateAnalysis` called with non-existent ID: returns not found message
- `getJobRequirements` called with non-existent offer ID: returns not found message
- `compareCandidates` with candidates from different offers: returns incompatibility message
- `compareCandidates` with one or both analyses not completed: returns status explanation
- Null analysis fields (competences_extraites, langues, etc.): assistant reports them as unavailable
- Message too long (>2000 chars): validation error

## Security / authorization rules
- All routes behind `auth` and `verified` middleware
- Ownership check: `$analyse->offre->user_id === auth()->id()`
- Each tool verifies ownership before returning data
- No raw API keys or prompt injection exposure

## Demo explanation notes
- "Tools vs hallucination: the assistant uses `getCandidateAnalysis` which queries the actual analyses_candidats table. If I ask about a score, it comes from the database, not the AI's imagination."
- "Memory: notice I can ask a follow-up question and the assistant remembers what we discussed because the laravel/ai SDK persists conversation history in the `agent_conversation_messages` table."
- "Ownership: I tried to access another user's analysis and got a 403. The tool checks ownership before returning data."

## ADDED Requirements

### Requirement: Assistant tool getCandidateAnalysis
The system SHALL provide a `getCandidateAnalysis(int $candidatId)` tool that returns structured candidate analysis data from the database. The tool SHALL verify that the authenticated user owns the related offer before returning data. The tool SHALL return the candidate name, offer title, matching score, recommendation, justification, extracted skills, strengths, gaps, missing skills, languages, education level, and years of experience. If the analysis is not in `completed` status, the tool SHALL return the current status instead. If the candidate ID does not exist, the tool SHALL return a not-found message.

#### Scenario: getCandidateAnalysis returns real saved data
- **WHEN** the assistant calls `getCandidateAnalysis` with a valid candidate ID linked to a completed analysis owned by the authenticated user
- **THEN** the tool returns the full structured analysis data from the database (score, recommendation, strengths, gaps, etc.)

#### Scenario: getCandidateAnalysis handles non-existent ID
- **WHEN** the assistant calls `getCandidateAnalysis` with a non-existent candidate ID
- **THEN** the tool returns a message indicating the candidate was not found

#### Scenario: getCandidateAnalysis enforces ownership
- **WHEN** the assistant calls `getCandidateAnalysis` with a candidate ID whose analysis belongs to another user's offer
- **THEN** the tool returns a message indicating the analysis is not accessible

#### Scenario: getCandidateAnalysis handles non-completed status
- **WHEN** the assistant calls `getCandidateAnalysis` with a candidate ID whose analysis is still `pending`, `processing`, or `failed`
- **THEN** the tool returns the current status and explains that the analysis is not yet available

#### Scenario: getCandidateAnalysis handles null data fields
- **WHEN** the assistant calls `getCandidateAnalysis` and some analysis fields are null (e.g., competences_extraites is null)
- **THEN** the tool returns the available data and indicates which fields are not available

### Requirement: Assistant tool getJobRequirements
The system SHALL provide a `getJobRequirements(int $offreId)` tool that returns job offer requirements (title, description, required skills, minimum experience) from the database. The tool SHALL verify that the authenticated user owns the offer.

#### Scenario: getJobRequirements returns real offer data
- **WHEN** the assistant calls `getJobRequirements` with a valid offer ID owned by the authenticated user
- **THEN** the tool returns the offer title, description, required skills, and minimum experience level

#### Scenario: getJobRequirements handles non-existent offer
- **WHEN** the assistant calls `getJobRequirements` with a non-existent offer ID
- **THEN** the tool returns a message indicating the offer was not found

#### Scenario: getJobRequirements enforces ownership
- **WHEN** the assistant calls `getJobRequirements` with an offer ID belonging to another user
- **THEN** the tool returns a message indicating the offer is not accessible

### Requirement: Assistant tool compareCandidates
The system SHALL provide a `compareCandidates(int $id1, int $id2)` tool that compares two candidates using their saved structured analysis data. The tool SHALL verify that both candidate analyses belong to offers owned by the authenticated user. The tool SHALL NOT call the CV analysis AI job again. If the candidates are not comparable (different offers, or one or both analyses are not completed), the tool SHALL return a clear explanation.

#### Scenario: compareCandidates compares two completed analyses
- **WHEN** the assistant calls `compareCandidates` with two valid candidate IDs whose analyses are both `completed` and linked to offers owned by the authenticated user
- **THEN** the tool returns a formatted comparison of both analyses (scores side by side, strengths, gaps, missing skills, recommendations)

#### Scenario: compareCandidates handles candidates from different offers
- **WHEN** the assistant calls `compareCandidates` with candidate IDs that belong to different offers
- **THEN** the tool returns a message explaining they cannot be compared because they belong to different offers

#### Scenario: compareCandidates handles non-completed analysis
- **WHEN** the assistant calls `compareCandidates` and one or both analyses are not in `completed` status
- **THEN** the tool returns a message explaining the current status of each analysis

#### Scenario: compareCandidates enforces ownership
- **WHEN** the assistant calls `compareCandidates` and one of the analyses belongs to another user's offer
- **THEN** the tool returns a message indicating the data is not accessible

### Requirement: Assistant chat interface
The system SHALL provide a chat interface embedded in the analysis detail page. The user SHALL type a message and receive an AI response based on real tool data. The chat SHALL use existing laravel/ai SDK conversation memory so follow-up questions retain context. The chat SHALL be protected by auth and ownership checks.

#### Scenario: Guest redirected to login
- **WHEN** an unauthenticated user tries to access the assistant chat route
- **THEN** they are redirected to the login page

#### Scenario: User can ask about their own candidate
- **WHEN** an authenticated user sends a message about a candidate analysis linked to their own offer
- **THEN** the assistant responds using real tool data from the database

#### Scenario: User cannot ask about another user's analysis
- **WHEN** an authenticated user sends a message about a candidate analysis linked to another user's offer
- **THEN** the system returns a 403 Forbidden response

#### Scenario: Conversation memory persists
- **WHEN** a user sends a follow-up message in the same conversation
- **THEN** the assistant references information from previous messages in the same conversation

#### Scenario: No hallucinated data
- **WHEN** the assistant encounters null analysis fields
- **THEN** the assistant explicitly states the data is unavailable rather than inventing values

#### Scenario: Analysis not completed
- **WHEN** a user sends a message about an analysis that is `pending`, `processing`, or `failed`
- **THEN** the assistant returns a message explaining the analysis status and that it is not yet available for questions
