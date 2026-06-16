# Database Documentation

## Purpose

Document the TalentMatch database model (tables, columns, types, relationships, statuses) in a dedicated `docs/database/README.md` file for developers and evaluators who need to understand the data schema.

## Requirements

### Requirement: Database model documentation

The system SHALL provide `docs/database/README.md` documenting the database model, tables, relationships, and key columns.

#### Scenario: All core tables documented
- **WHEN** a developer opens `docs/database/README.md`
- **THEN** it SHALL document the `users`, `offres`, and `candidatures` tables with their columns, types, and purposes

#### Scenario: Relationships documented
- **WHEN** a developer reads the database docs
- **THEN** they SHALL understand the 1:N relationships: user → offres, offre → candidatures

#### Scenario: Analysis status field documented
- **WHEN** a developer reads the database docs
- **THEN** the `candidatures.analysis_status` enum SHALL be documented with its possible values: pending, processing, completed, failed

#### Scenario: AI analysis data documented
- **WHEN** a developer reads the database docs
- **THEN** the structured AI analysis columns on `candidatures` SHALL be documented (matching_score, points_forts, lacunes, competences_manquantes, recommandation, justification, etc.)

#### Scenario: Conversation memory tables noted
- **WHEN** a developer reads the database docs
- **THEN** it SHALL note that conversation memory uses laravel/ai SDK tables (not custom tables)
