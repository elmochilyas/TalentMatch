## Context

TalentMatch is functionally complete. All business features have been implemented and tested. The only remaining deliverable is the final README and supporting documentation to allow the trainer/evaluator to install, run, test, and understand the project without prior context.

## Goals / Non-Goals

**Goals:**
- Create a complete, professional README.md at project root
- Create docs/database/README.md explaining the data model
- Create docs/demo-scenario.md with an end-to-end walkthrough
- Create docs/ai-workflow.md documenting the AI pipeline
- All docs must be clear, accurate, and free of real secrets

**Non-Goals:**
- No new business features, migrations, or code modifications
- No UI redesign or deployment automation
- No changes to existing specs or MCD/MLD
- No changes to AGENTS.md or OpenSpec workflow config

## Decisions

- **README structure**: Follows Laravel project conventions (description → features → stack → install → run → test → demo). This is the standard evaluators expect.
- **Separate docs/ files for depth**: The README gives an overview; deep-dives on DB, demo flow, and AI pipeline live in `docs/` so the README stays scannable.
- **No API key placeholders in .env.example**: The example env will include keys like `AI_API_KEY=` with empty values and a comment directing the user to configure their provider.
- **Demo scenario follows a single happy path**: One RH agent creates an offer, submits a CV, views analysis, asks the assistant questions. This keeps the demo focused and repeatable.
- **All commands are copy-paste ready**: Each shell command is presented as a standalone block with a brief explanation, not embedded in prose.
- **Markdown over other formats**: Plain Markdown renders well on GitHub, GitLab, and local editors without tooling dependencies.

## Risks / Trade-offs

- [Stale README] → The README references current file/class names. If the codebase changes later, docs must be updated separately.
- [Missing edge case in demo] → The demo covers one successful flow. Evaluators who try edge cases (e.g., failed analysis) will need to explore on their own or refer to test output.
- [Oversimplification] → The README intentionally omits deep internals. The `docs/` sub-files provide depth for those who need it.
