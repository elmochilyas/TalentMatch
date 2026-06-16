## 1. Model & Migration

- [x] 1.1 Create `Offre` model with migration via `php artisan make:model Offre -m`
- [x] 1.2 Define migration columns: `id`, `user_id` (foreign constrained), `titre` (string 255), `description` (text), `competences_requises` (json, nullable), `niveau_experience_minimum` (integer, nullable), `timestamps`
- [x] 1.3 Add `$casts` to `Offre` model: `'competences_requises' => 'array'`
- [x] 1.4 Add `$fillable` or `$guarded` to `Offre` model
- [x] 1.5 Run migration

## 2. Relationships & Policy

- [x] 2.1 Add `hasMany(Offre::class)` relationship to `User` model
- [x] 2.2 Add `belongsTo(User::class)` relationship to `Offre` model
- [x] 2.3 Generate `OffrePolicy` via `php artisan make:policy OffrePolicy --model=Offre`
- [x] 2.4 Implement `view`, `create`, `update`, `delete` policy methods with ownership check (`$user->id === $offre->user_id`)
- [x] 2.5 Register `OffrePolicy` in `AuthServiceProvider` (or use auto-discovery with model+policy naming convention)

## 3. Form Requests

- [x] 3.1 Create `StoreOffreRequest` via `php artisan make:request StoreOffreRequest`
- [x] 3.2 Add validation rules: `titre` required|string|max:255, `description` required|string, `competences_requises` nullable|string, `niveau_experience_minimum` nullable|integer|min:0
- [x] 3.3 Add `authorize()` method returning `true` (policy handles authorization)
- [x] 3.4 Create `UpdateOffreRequest` via `php artisan make:request UpdateOffreRequest` (same rules as store)
- [x] 3.5 Normalize `competences_requises` string to array (trim, split by comma, remove empties) via `prepareForValidation()` or model mutator

## 4. Controller

- [x] 4.1 Create `OffreController` via `php artisan make:controller OffreController --resource --model=Offre`
- [x] 4.2 Implement `index()`: return paginated offers for authenticated user with `withCount('candidatures')`
- [x] 4.3 Implement `create()`: return view with empty offre
- [x] 4.4 Implement `store(StoreOffreRequest $request)`: create offer with `user_id = auth()->id()` and normalized `competences_requises`, redirect to show
- [x] 4.5 Implement `show(Offre $offre)`: authorized via `$this->authorize('view', $offre)`, return view
- [x] 4.6 Implement `edit(Offre $offre)`: authorized, return edit view
- [x] 4.7 Implement `update(UpdateOffreRequest $request, Offre $offre)`: authorized, update, redirect to show
- [x] 4.8 Implement `destroy(Offre $offre)`: authorized, delete, redirect to index

## 5. Routes

- [x] 5.1 Register `Route::resource('offres', OffreController::class)->middleware(['auth', 'verified'])` in `routes/web.php`
- [x] 5.2 Name all routes following Laravel resource conventions (auto-named by `resource()`)

## 6. Blade Views

- [x] 6.1 Create `offres/index.blade.php`: list of offers with titre, niveau_experience_minimum, candidatures count, links to show/edit/delete
- [x] 6.2 Create `offres/create.blade.php`: form for new offer with fields for titre, description, competences_requises (textarea or comma-separated input), niveau_experience_minimum
- [x] 6.3 Create `offres/show.blade.php`: display all offer fields
- [x] 6.4 Create `offres/edit.blade.php`: edit form pre-filled with existing data
- [x] 6.5 Extend Breeze `layouts/app.blade.php` for all views, use Tailwind styling consistent with Breeze

## 7. Navigation Update

- [x] 7.1 Add "Mes offres" navigation link to Breeze navigation (authenticated section) pointing to `route('offres.index')`

## 8. Tests

- [x] 8.1 Create `OffreTest` via `php artisan make:test --pest OffreTest`
- [x] 8.2 Test that guest is redirected to login for all offer routes
- [x] 8.3 Test that authenticated user can create an offer
- [x] 8.4 Test that authenticated user sees only their own offers on index
- [x] 8.5 Test that authenticated user can view their own offer
- [x] 8.6 Test that authenticated user gets 403 viewing another user's offer
- [x] 8.7 Test that authenticated user can edit their own offer
- [x] 8.8 Test that authenticated user gets 403 editing another user's offer
- [x] 8.9 Test validation errors on create (empty titre, empty description)
- [x] 8.10 Test that authenticated user can delete their own offer
- [x] 8.11 Test that authenticated user gets 403 deleting another user's offer
- [x] 8.12 Test that `competences_requises` is stored as JSON array

## 9. Final Checks

- [x] 9.1 Run `vendor/bin/pint --format agent` to fix code style
- [x] 9.2 Run `php artisan test --compact --filter=OffreTest` to verify all tests pass
