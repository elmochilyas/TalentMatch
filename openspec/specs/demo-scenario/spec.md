# Demo Scenario

## Purpose

Provide an end-to-end demo walkthrough in `docs/demo-scenario.md` that guides evaluators through all major features of TalentMatch, including registration, offer creation, CV submission, analysis viewing, assistant interaction, and candidate comparison.

## Requirements

### Requirement: Demo walkthrough document

The system SHALL provide `docs/demo-scenario.md` with a step-by-step walkthrough for an evaluator to demonstrate all major features.

#### Scenario: Single happy-path walkthrough
- **WHEN** an evaluator opens `docs/demo-scenario.md`
- **THEN** it SHALL describe a single end-to-end scenario covering: registration/login, creating an offer, submitting a CV, viewing analysis results, and asking the assistant questions about candidates

#### Scenario: Expected results documented
- **WHEN** an evaluator follows the demo
- **THEN** each step SHALL describe what the user should see and what behavior is expected

#### Scenario: Key architectural explanations included
- **WHEN** an evaluator follows the demo
- **THEN** it SHALL include talking points about: why OpenSpec, why queue, why structured output, why tools, why memory

### Requirement: Demo covers assistant features

The demo scenario SHALL include a section where the evaluator interacts with the AI assistant using the three tools (getCandidateAnalysis, getJobRequirements, compareCandidates).

#### Scenario: Assistant interaction in demo
- **WHEN** the evaluator reaches the assistant section
- **THEN** they SHALL ask about a candidate's analysis, ask about a job offer's requirements, and compare two candidates
