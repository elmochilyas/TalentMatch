## Why

TalentMatch is an automated candidate prescreening application. RH agents need to create and manage job offers before candidates can submit CVs and receive AI analyses. Without a job offer management foundation, the entire candidate analysis pipeline cannot function. This change establishes the core `offres` CRUD so that offers exist as targets for candidate submissions.

## What Changes

- Create `Offre` model with migration (`offres` table)
- Add `User hasMany Offre` / `Offre belongsTo User` relationships
- Build authenticated Blade CRUD: index, create, store, show, edit, update, destroy
- Add `StoreOffreRequest` and `UpdateOffreRequest` form request validators
- Add `OffrePolicy` for ownership-based authorization
- Register resource routes under authentication middleware
- Display analyzed candidates count on offer list (0 until candidate analysis is implemented)
- Write Pest tests covering auth protection, CRUD, ownership, and validation

## Capabilities

### New Capabilities
- `offre-crud`: Full job offer management for authenticated RH agents — create, list, view, edit, and delete offers with title, description, required skills (JSON), and minimum experience level

### Modified Capabilities

None.

## Impact

- **New files**: `Offre` model, migration, `OffrePolicy`, `StoreOffreRequest`, `UpdateOffreRequest`, `OffreController`, 5+ Blade views, Pest test file
- **Modified files**: `User` model (add `hasMany` relationship), `routes/web.php` (add resource routes), possible navigation / dashboard updates
- **Database**: new `offres` table with `user_id`, `titre`, `description`, `competences_requises` (JSON), `niveau_experience_minimum`, timestamps
- **Auth**: all routes gated by `auth` middleware + policy ownership checks
- **No new dependencies**
