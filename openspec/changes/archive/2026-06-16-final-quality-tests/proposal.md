## Why

TalentMatch's core features (offres CRUD, CV submission, AI analysis, assistant chat, comparison/ranking) were built feature-by-feature without comprehensive test coverage. As the project approaches demo readiness, we need a quality hardening pass: full Pest test coverage, regression fixes, and verified authorization boundaries. Jira TAL-17 tracks this effort.

## What Changes

- Add comprehensive Pest test coverage for all existing features
- Fix any regressions or bugs discovered during testing
- Add test helpers/fakes for AI and queue mocking
- Ensure auth/ownership boundaries are verified in tests
- No new features, no MCD/MLD changes, no real AI API calls in tests

## Capabilities

### New Capabilities

- `final-quality-tests`: Comprehensive test coverage for all existing TalentMatch features including auth, offres CRUD, candidate submission, AI analysis, assistant tools, comparison/ranking

### Modified Capabilities

- _None — existing specs remain unchanged; only test coverage is added._

## Impact

- `tests/` — new Pest test files for auth, offres CRUD, candidates, analysis, assistant, comparison
- `app/` — minimal fixes only (if regressions found during testing)
- `phpunit.xml` or `phpunit` config — no changes expected
- CI — test suite must pass before merge
- No database migration, no new routes, no new jobs, no new views
