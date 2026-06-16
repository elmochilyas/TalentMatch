## 1. README.md

- [x] 1.1 Create project README with title, description, problem/context, feature list, tech stack
- [x] 1.2 Add installation section with composer install, npm install, cp .env.example, key:generate, migrate, npm run dev
- [x] 1.3 Add environment variables reference (no real secrets)
- [x] 1.4 Add queue worker documentation (php artisan queue:work)
- [x] 1.5 Add test command documentation (php artisan test / vendor/bin/pest)
- [x] 1.6 Add AI features overview section (submission → queue → analysis → display)
- [x] 1.7 Add assistant tools section with the three tool signatures and explanation
- [x] 1.8 Add OpenSpec workflow explanation section
- [x] 1.9 Add security notes, known limitations, and project status
- [x] 1.10 Add references to docs/demo-scenario.md and docs/ai-workflow.md

## 2. Database Documentation

- [x] 2.1 Create docs/database/README.md documenting the three core tables (users, offres, candidatures)
- [x] 2.2 Document relationships (1:N user→offres, offre→candidatures)
- [x] 2.3 Document analysis_status enum values (pending, processing, completed, failed)
- [x] 2.4 Document structured AI analysis columns (matching_score, points_forts, lacunes, etc.)
- [x] 2.5 Note that conversation memory uses laravel/ai SDK tables

## 3. Demo Scenario

- [x] 3.1 Create docs/demo-scenario.md with step-by-step walkthrough (register, create offer, submit CV, view analysis)
- [x] 3.2 Add assistant interaction section (getCandidateAnalysis, getJobRequirements, compareCandidates)
- [x] 3.3 Include key architectural talking points (OpenSpec, queue, structured output, tools, memory)

## 4. AI Workflow Documentation

- [x] 4.1 Create docs/ai-workflow.md documenting the end-to-end AI pipeline
- [x] 4.2 Document queue/async processing and status tracking
- [x] 4.3 Document structured JSON schema and all fields
- [x] 4.4 Document mocking in tests and real AI configuration requirements
- [x] 4.5 Document edge cases (empty CV, missing skills, invalid responses, score out of range)
