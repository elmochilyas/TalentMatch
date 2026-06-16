# Final README

## Purpose

Provide a comprehensive project README.md at the repository root that enables new developers and evaluators to understand, install, run, test, and evaluate the TalentMatch application without prior context.

## Requirements

### Requirement: Project README at root

The system SHALL provide a `README.md` file at the project root that enables a new developer or evaluator to understand, install, run, test, and evaluate the project.

#### Scenario: README contains all required sections
- **WHEN** a developer opens `README.md`
- **THEN** it SHALL contain: project title, description, problem/context, feature list, tech stack, installation steps, env configuration, migration instructions, test command, queue worker command, demo scenario, OpenSpec workflow explanation, security notes, known limitations, and project status

#### Scenario: Installation steps are complete
- **WHEN** a developer follows the installation section
- **THEN** they SHALL be able to install dependencies, configure the environment, migrate the database, and start the dev server without additional research

#### Scenario: No secrets exposed
- **WHEN** the README references environment variables
- **THEN** it SHALL NOT include real API keys, secrets, or credentials

#### Scenario: Commands are documented
- **WHEN** a developer reads the commands section
- **THEN** they SHALL find documented commands for: queue worker, tests, migrations, dev server

#### Scenario: Demo scenario is referenced
- **WHEN** a developer reads the README
- **THEN** it SHALL reference the detailed demo scenario in `docs/demo-scenario.md`

### Requirement: AI workflow explanation

The README SHALL explain the AI workflow at a high level and reference the detailed documentation in `docs/ai-workflow.md`.

#### Scenario: AI workflow summary in README
- **WHEN** a developer reads the AI features section
- **THEN** it SHALL explain the end-to-end flow: CV submission → queue → AI analysis → structured output → display

### Requirement: Assistant tools explanation

The README SHALL explain the AI assistant tools (`getCandidateAnalysis`, `getJobRequirements`, `compareCandidates`) and that they prevent hallucination by using real Laravel tools.

#### Scenario: Tools section in README
- **WHEN** a developer reads the assistant section
- **THEN** they SHALL see the three tools with their signatures and a note that they use real database data

### Requirement: OpenSpec workflow explanation

The README SHALL include a section explaining the OpenSpec-driven development approach used in this project.

#### Scenario: OpenSpec section in README
- **WHEN** a developer reads the workflow section
- **THEN** they SHALL understand that specs drive development and where specs are stored
