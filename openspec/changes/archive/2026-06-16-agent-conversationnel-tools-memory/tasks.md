## 1. Tools

- [x] 1.1 Create `app/Ai/Tools/` directory structure
- [x] 1.2 Implement `GetCandidateAnalysis` tool (ownership check, status check, formatted output, null field handling)
- [x] 1.3 Implement `GetJobRequirements` tool (ownership check, formatted output)
- [x] 1.4 Implement `CompareCandidates` tool (dual ownership check, status checks, comparison logic, incompatibility handling)
- [x] 1.5 Register tools in Agent (via `tools()` method with user injection from conversation context)

## 2. Service & Controller

- [x] 2.1 Create `app/Services/AssistantService.php` (conversation lookup/create, tool registration, AI message dispatch, response extraction)
- [x] 2.2 Create `app/Http/Controllers/AssistantController.php` with `__invoke` method (thin — delegates to service)
- [x] 2.3 Create `app/Http/Requests/AssistantMessageRequest.php` (message validation + ownership check)

## 3. Routes & Views

- [x] 3.1 Add POST route `offres/{offre}/analyses/{analyse}/assistant` (name: `offres.analyses.assistant`)
- [x] 3.2 Add chat Blade partial `resources/views/offres/partials/assistant-chat.blade.php` using Alpine.js
- [x] 3.3 Include chat partial in `offres/analyse-show.blade.php`

## 4. Tests

- [x] 4.1 Write test for guest redirect on assistant route
- [x] 4.2 Write test for authenticated user can send message to own analysis
- [x] 4.3 Write test for 403 on another user's analysis
- [x] 4.4 Write test for `getCandidateAnalysis` returns real saved analysis data
- [x] 4.5 Write test for `getCandidateAnalysis` handles non-existent ID
- [x] 4.6 Write test for `getCandidateAnalysis` enforces ownership
- [x] 4.7 Write test for `getCandidateAnalysis` handles non-completed status
- [x] 4.8 Write test for `getCandidateAnalysis` handles null fields
- [x] 4.9 Write test for `getJobRequirements` returns real offer data
- [x] 4.10 Write test for `getJobRequirements` enforces ownership
- [x] 4.11 Write test for `compareCandidates` compares two completed analyses
- [x] 4.12 Write test for `compareCandidates` handles different offers
- [x] 4.13 Write test for `compareCandidates` handles non-completed analysis
- [x] 4.14 Write test for `compareCandidates` enforces ownership
- [x] 4.15 Write test for follow-up message preserves conversation context
- [x] 4.16 Write test for assistant refuses to invent data when fields are null
- [x] 4.17 Write test for message validation (empty, too long)

## 5. Lint & Validate

- [x] 5.1 Run `vendor/bin/pint --format agent` for code style
- [x] 5.2 Run full test suite to confirm all tests pass
- [x] 5.3 Final review of assistant behavior for safety rules
