## ADDED Requirements

### Requirement: AI pipeline documentation
The system SHALL provide `docs/ai-workflow.md` documenting the complete AI pipeline from candidate submission to structured analysis output.

#### Scenario: End-to-end flow documented
- **WHEN** a developer opens `docs/ai-workflow.md`
- **THEN** it SHALL document: form submission → validation → job dispatch → queue processing → AI provider call → structured JSON parsing → database storage → display

#### Scenario: Queue explained
- **WHEN** a developer reads the AI workflow
- **THEN** it SHALL explain why analysis runs as a background job (async, non-blocking) and how the status field tracks progress (pending → processing → completed/failed)

#### Scenario: Structured output documented
- **WHEN** a developer reads the AI workflow
- **THEN** it SHALL show the strict JSON schema returned by the AI, explain each field, and note that invalid/malformed responses are caught safely

#### Scenario: Mocking in tests documented
- **WHEN** a developer reads the AI workflow
- **THEN** it SHALL explain that AI calls are mocked/faked in tests and that real AI requires a provider API key

#### Scenario: Edge cases documented
- **WHEN** a developer reads the AI workflow
- **THEN** it SHALL document edge cases: empty CV rejection, missing skills on offer, invalid AI response, score out of range
