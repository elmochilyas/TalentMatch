## 1. Test Infrastructure & Setup

- [x] 1.1 `tests/Feature/OffresTest.php` exists with RefreshDatabase (full CRUD coverage)
- [x] 1.2 `tests/Feature/CandidaturesTest.php` exists for candidate submission
- [x] 1.3 `tests/Feature/AnalyseCvJobTest.php` exists for AI analysis job
- [x] 1.4 `tests/Feature/AssistantConversationTest.php` exists for assistant tool tests
- [x] 1.5 `tests/Feature/ComparisonRankingTest.php` exists for comparison/ranking
- [x] 1.6 `tests/Feature/AuthAccessTest.php` created for auth/security
- [x] 1.7 `tests/Feature/AnalyseDisplayTest.php` created for analysis view display

## 2. Authentication & Security Tests

- [x] 2.1 Guest redirected to login for all protected pages (dashboard, offres, analyses, assistant, comparison)
- [x] 2.2 Authenticated user cannot access another user's offers (view, edit, update, delete)
- [x] 2.3 Authenticated user cannot access another user's candidates/analyses

## 3. Offres CRUD Tests

- [x] 3.1 Authenticated user can create a valid offer
- [x] 3.2 Required fields validated (titre, description)
- [x] 3.3 competences_requises stored as JSON array via cast
- [x] 3.4 User lists only their own offers
- [x] 3.5 User can view own offer
- [x] 3.6 User can update own offer
- [x] 3.7 User can delete own offer
- [x] 3.8 User cannot view/update/delete another user's offer

## 4. Candidate Submission Tests

- [x] 4.1 User can submit candidate CV to own offer
- [x] 4.2 Empty CV rejected with validation error
- [x] 4.3 Too-short CV (< 20 chars) rejected
- [x] 4.4 Candidate row and analysis row (status pending) created
- [x] 4.5 Analysis job dispatched on submission (Queue::assertPushed added)
- [x] 4.6 User cannot submit CV to another user's offer

## 5. Structured AI Analysis Tests

- [x] 5.1 AI fake/mock helper exists (AnalyseCvAgent::fake)
- [x] 5.2 Valid fake AI output saves analysis with completed status
- [x] 5.3 Score = 0 and score = 100 accepted as valid
- [x] 5.4 Score < 0 causes failed status with error message
- [x] 5.5 Score > 100 causes failed status with error message
- [x] 5.6 Invalid recommendation causes failed status
- [x] 5.7 Malformed JSON response causes failed status with message_erreur
- [x] 5.8 JSON fields cast as arrays on retrieval
- [x] 5.9 No real external API call (AnalyseCvAgent::fake / Queue::fake pattern)

## 6. Analysis Display Tests

- [x] 6.1 Completed analysis shows score, recommendation, strengths, gaps, missing skills, justification
- [x] 6.2 Recommendation displays correct labels (À convoquer / En attente / À rejeter)
- [x] 6.3 Failed analysis shows message_erreur
- [x] 6.4 Pending/processing analysis shows safe placeholder / loading state
- [x] 6.5 User cannot view another user's candidate analysis

## 7. Assistant Tools & Chat Tests

- [x] 7.1 getCandidateAnalysis returns real saved analysis data from DB
- [x] 7.2 getJobRequirements returns real offer data (titre, description, competences_requises)
- [x] 7.3 compareCandidates returns side-by-side analysis from same offer
- [x] 7.4 All tools enforce ownership (return error for another user's data)
- [x] 7.5 Assistant safely handles missing/empty data without hallucination
- [x] 7.6 Assistant/chat route is auth protected

## 8. Ranking & Comparison Tests

- [x] 8.1 Candidates ranked by score descending on offer show page
- [x] 8.2 Unscored/pending candidates appear after scored ones
- [x] 8.3 Comparing two completed analyses from same offer works
- [x] 8.4 Comparing candidates from different offers is refused
- [x] 8.5 User cannot compare candidates from another user's offer

## 9. Quality & Integration

- [x] 9.1 Full Pest test suite passes (113 tests, 280 assertions)
- [x] 9.2 vendor/bin/pint --format agent run (formatting fixed)
- [x] 9.3 No real API calls made during tests (fakes used throughout)
- [x] 9.4 .env is not committed (gitignored)
- [x] 9.5 No API keys or secrets in tracked files (env() references only)
- [ ] 9.6 Commit with message "feat: final quality and test coverage (AI-assisted)"
