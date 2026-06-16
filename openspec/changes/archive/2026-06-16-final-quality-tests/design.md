## Context

TalentMatch has built-feature-by-feature without systematic test coverage. We have existing specs for offres CRUD, AI analysis, assistant conversationnel, and candidate comparison. The codebase uses Pest for testing but lacks comprehensive coverage for auth boundaries, validation edge cases, AI job error handling, and cross-feature integration.

No new business features, migrations, routes, or views are introduced. This is purely a quality/test hardening pass.

## Goals / Non-Goals

**Goals:**
- Full Pest test coverage across all 7 areas: auth/security, offres CRUD, candidate submission, AI analysis, analysis display, assistant tools/chat, ranking/comparison
- Tests run in isolation with fakes/mocks — no real AI API calls
- Authorization boundaries verified for every protected route/action
- Edge cases covered: empty CV, missing skills, invalid AI responses, score out of range, ownership violations
- Any regressions found during testing are fixed with minimal changes
- Full test suite passes before PR merge

**Non-Goals:**
- No new features or capabilities
- No MCD/MLD changes or migrations
- No UI redesign
- No README or deployment changes
- No real AI API integration in tests

## Decisions

| Decision | Rationale | Alternatives Considered |
|---|---|---|
| Use Pest `fake()` and `Http::fake()` for AI | Avoids external API calls, makes tests deterministic, matches existing Laravel conventions | Mockery (more verbose, not Pest-idiomatic) |
| Use `Queue::fake()` for job assertions | Verifies jobs dispatched without running them; pairs with `assertPushed()` | Running jobs synchronously (slower, couples tests to implementation) |
| Test ownership via two-user pattern | Create user A + user B; assert user A cannot access user B's data — clear, explicit | Scoped factory states (less explicit) |
| One test file per domain area | Matches Laravel/Pest convention; easy to filter with `--filter` | Single monolithic test file (harder to maintain) |
| Use `RefreshDatabase` trait | Clean slate per test prevents cross-test pollution | `DatabaseTransactions` (slower with many tests) |

## Risks / Trade-offs

- [Risk] Mock too much and miss real integration bugs → Mitigation: test controllers end-to-end (with faked AI only), not unit-test in isolation
- [Risk] Tests become brittle if implementation changes → Mitigation: test behavior (response codes, DB state, redirects), not internal method calls
- [Risk] AI JSON schema tests depend on exact structure → Mitigation: test constraints (keys exist, types match, score in range) rather than exact values
