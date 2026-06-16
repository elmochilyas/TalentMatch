## Why

TalentMatch is functionally complete (auth, offres CRUD, candidate submission, AI analysis, assistant chat, comparison/ranking, tests) but lacks a final README and delivery documentation. Without these, a trainer or evaluator cannot easily install, run, test, or understand the project. This change closes that gap for final submission.

## What Changes

- Create `README.md` at project root with full installation, usage, testing, and demo instructions
- Create `docs/database/README.md` documenting the database model (MCD/MLD overview)
- Create `docs/demo-scenario.md` with a step-by-step walkthrough for evaluators
- Create `docs/ai-workflow.md` explaining the AI pipeline from submission to structured output

No business logic, no migrations, no new features — documentation only.

## Capabilities

### New Capabilities
- `final-readme`: Project-level README with install, run, test, demo instructions
- `database-docs`: Database model documentation (MCD/MLD overview)
- `demo-scenario`: Step-by-step demo walkthrough for evaluators
- `ai-workflow`: AI pipeline documentation (submission → queue → structured output)

### Modified Capabilities

None — this change does not modify requirement-level behavior of any existing capability.

## Impact

- `README.md` — new file at project root
- `docs/database/README.md` — new documentation
- `docs/demo-scenario.md` — new documentation
- `docs/ai-workflow.md` — new documentation
- No code, migrations, routes, controllers, or views are modified
