# PHP-First Rebuild (Laravel 13 + Livewire 3) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Consolidate the three overlapping layers (broken root HTML, Nuxt3 `frontend/`, API-only Laravel `backend/`) into one PHP-first full-stack Laravel 13 + Livewire 3 app with a sleek minimal-futuristic UI, running cleanly on local SQLite and portable to any PHP 8.3+ host.

**Architecture:** Evolve the existing `backend/` Laravel app in place. Reuse its models, migrations, seeders, and business logic; convert it from API-only to full-stack by adding web routes, Blade layouts, Livewire 3 components (Collection, Vocabulary, Flashcard), session auth (Breeze Livewire stack) with `admin`/`user` role policies, and a Tailwind v4 design-token system. Migrate the two controller helpers (`lookup`, `calculateNextReview`) into tested service classes, then delete the REST API surface.

**Tech Stack:** PHP 8.3, Laravel 13.8, Livewire 3, Alpine.js (bundled with Livewire), Tailwind CSS v4 (already installed, CSS-first `@theme`), Laravel Breeze (Livewire stack) for session auth, SQLite (local) / MySQL (portable), PHPUnit 12 with Livewire test helpers, Vite 8.

## Global Constraints

- **Working directory for all app commands:** `backend/` (the whole app lives here; do not create a second Laravel root).
- **PHP `^8.3`, Laravel `^13.8`** — do not downgrade `composer.json` constraints.
- **Local DB is SQLite** at `backend/database/database.sqlite`; tests use SQLite `:memory:` (already configured in `phpunit.xml`). Schema must also work on **MySQL 8** (avoid SQLite-only SQL).
- **Auth is session-based** (Breeze Livewire). No Sanctum tokens in the new surface. Public registration disabled.
- **Authorization is enforced server-side** in Livewire via Policies (`$this->authorize(...)`), never by hiding UI alone. Guests read-only; `role === 'admin'` writes.
- **Admin seed:** `admin@example.com` / `password123` (via `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env`).
- **Design:** light-first with dark toggle; single cyan/teal accent (`#06B6D4 → #14B8A6`); accessible solids `cyan-600` (light) / `cyan-400` (dark); fonts Space Grotesk (headings) / Inter (body) / JetBrains Mono (data). Subtle motion only; honor `prefers-reduced-motion`; WCAG AA contrast.
- **UI text** stays bilingual matching existing content (Traditional Chinese labels as in seeders/prototypes) where the prototypes used Chinese; new chrome may be English.
- **TDD everywhere:** write the failing test first, watch it fail, implement minimally, watch it pass, commit. Small, frequent commits.
- **Per-task adversary gate:** after a task's tests pass, it is subject to the adversary-review loop (code-review + security-review + edge-case hunt from subagent-driven-development). A task is "done" only when tests pass AND adversary findings are resolved, verified by running the app/suite.
- **Run tests with:** `cd backend && php artisan test` (or a filtered `--filter=Name`). **Build assets with:** `cd backend && npm run build`.
- **Commit message convention:** end with `Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>`. Work on branch `feat/php-livewire-rebuild`.

---

## File Structure (created / modified / deleted)

**Created:**
- `backend/routes/web.php` — all page routes (Breeze also adds `routes/auth.php`).
- `backend/resources/views/layouts/app.blade.php` — shell: nav, theme toggle, footer, meta/OG.
- `backend/resources/views/home.blade.php`, `resume.blade.php`.
- `backend/resources/views/livewire/collection-index.blade.php`, `collection-form.blade.php`, `vocabulary-index.blade.php`, `vocabulary-form.blade.php`, `flashcard.blade.php`.
- `backend/resources/views/components/*` — Blade UI components (card, button, tag, modal, toast, stat, empty-state, skeleton).
- `backend/app/Livewire/CollectionIndex.php`, `CollectionForm.php`, `VocabularyIndex.php`, `VocabularyForm.php`, `Flashcard.php`.
- `backend/app/Services/SpacedRepetition.php`, `VocabularyLookup.php`.
- `backend/app/Policies/CollectionWorkPolicy.php`, `StudyVocabularyPolicy.php`.
- `backend/tests/Unit/SpacedRepetitionTest.php`, `VocabularyLookupTest.php`.
- `backend/tests/Feature/CollectionIndexTest.php`, `CollectionFormTest.php`, `VocabularyIndexTest.php`, `VocabularyFormTest.php`, `FlashcardTest.php`, `AuthTest.php` (rewritten), `PageTest.php`.

**Modified:**
- `backend/bootstrap/app.php` — add `web` routing; keep `admin` alias; drop api-only JSON exception rule after API removal.
- `backend/app/Models/User.php` — add `isAdmin()` helper; drop `HasApiTokens` after Sanctum removal.
- `backend/app/Models/CollectionWork.php` — add query scopes used by Livewire (optional).
- `backend/database/migrations/0001_01_01_000000_create_users_table.php` — `role` default `user`.
- `backend/resources/css/app.css` — design tokens + Tailwind theme.
- `backend/resources/js/app.js` — Alpine theme persistence bootstrap.
- `backend/vite.config.js` — fonts (Space Grotesk, Inter, JetBrains Mono).
- `backend/.env.example` — `DB_CONNECTION=sqlite`, `ADMIN_PASSWORD=password123`, drop `FRONTEND_URL`.
- `backend/database/seeders/AdminSeeder.php` — already env-driven; verify defaults.
- `README.md`, `啟動紀錄.md` — rewrite for PHP-only local setup.

**Deleted (Phase 7, after logic migrated):**
- `backend/routes/api.php`, `backend/app/Http/Controllers/Api/*`.
- `backend/tests/Feature/VocabularyTest.php`, `AuthTest.php` (old API versions — replaced), and API assertions in `CollectionTest.php` (file replaced by `CollectionIndexTest.php`/`CollectionFormTest.php`).
- `frontend/` (entire Nuxt3 tree).
- Root `index.html`, `resume.html`, `collection.html`, `vocabulary.html`, `component-demo.html`.
- Sanctum config/package if unused (`config/sanctum.php`, `laravel/sanctum` from composer) — only if nothing else references it.

---

## Phase 0 — Toolchain & environment bring-up

### Task 0: Verify toolchain and boot the existing app on SQLite

**Files:**
- Modify: `backend/.env` (created from example), `backend/database/database.sqlite` (created)

**Interfaces:**
- Produces: a running Laravel app at `http://localhost:8000` on SQLite, migrations + seeds applied. All later tasks assume this baseline.

- [ ] **Step 1: Verify tool versions**

Run:
```bash
php -v        # expect PHP 8.3.x
composer -V
node -v; npm -v
```
Expected: PHP 8.3+, Composer 2.x, Node 20+. If any is missing, STOP and report to the user (they must install it; e.g. PHP via `winget install php` or a Laravel Herd/XAMPP install) — do not attempt silent workarounds.

- [ ] **Step 2: Install PHP + JS dependencies**

Run:
```bash
cd backend
composer install
npm install
```
Expected: `vendor/` and `node_modules/` populated, no errors.

- [ ] **Step 3: Create `.env`, switch to SQLite, generate key**

Run:
```bash
cd backend
cp .env.example .env
```
Then edit `.env`: set `DB_CONNECTION=sqlite`, and delete/comment the `DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD` lines (SQLite ignores them). Set `ADMIN_EMAIL=admin@example.com`, `ADMIN_PASSWORD=password123`. Then:
```bash
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

- [ ] **Step 4: Migrate + seed, boot, smoke-test**

Run:
```bash
cd backend
php artisan migrate:fresh --seed
php artisan serve
```
In another shell: `curl -s http://localhost:8000/up` → expect HTTP 200 health page. Stop the server (Ctrl-C).
Expected: migrations run, `AdminSeeder` + `CategorySeeder` succeed (35 categories), health endpoint OK.

- [ ] **Step 5: Run the existing test suite (baseline)**

Run: `cd backend && php artisan test`
Expected: existing API tests pass (they still target `api.php`, which is present in Phase 0). Record the result as the green baseline. If red on a fresh box, fix environment before proceeding.

- [ ] **Step 6: Commit**

```bash
git add backend/.env.example
git commit -m "chore: switch local env to sqlite for zero-config dev

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```
(`.env` and `database.sqlite` are gitignored — only the example is committed.)

---

## Phase 1 — Full-stack skeleton: web routing, Livewire, auth, design system

### Task 1: Add web routing and a home route

**Files:**
- Create: `backend/routes/web.php`
- Modify: `backend/bootstrap/app.php:9-13`
- Test: `backend/tests/Feature/PageTest.php`

**Interfaces:**
- Produces: `GET /` renders a Blade view named `home`; web middleware group active (needed by Livewire + Breeze).

- [ ] **Step 1: Write the failing test**

```php
<?php // backend/tests/Feature/PageTest.php
namespace Tests\Feature;

use Tests\TestCase;

class PageTest extends TestCase
{
    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('個人網站');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd backend && php artisan test --filter=PageTest`
Expected: FAIL (route `/` not defined / 404).

- [ ] **Step 3: Register web routing in bootstrap**

Edit `backend/bootstrap/app.php` `->withRouting(...)` to add the `web` entry:
```php
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
```

- [ ] **Step 4: Create the route + minimal view**

`backend/routes/web.php`:
```php
<?php
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
```
`backend/resources/views/home.blade.php` (temporary minimal; replaced in Task 12):
```blade
<!doctype html>
<html lang="zh-TW"><head><meta charset="utf-8"><title>個人網站</title></head>
<body><h1>個人網站</h1></body></html>
```

- [ ] **Step 5: Run test to verify it passes**

Run: `cd backend && php artisan test --filter=PageTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add backend/routes/web.php backend/bootstrap/app.php backend/resources/views/home.blade.php backend/tests/Feature/PageTest.php
git commit -m "feat: add web routing and home route

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

### Task 2: Install Livewire 3

**Files:**
- Modify: `backend/composer.json` (via composer), `backend/resources/views/layouts/app.blade.php` (created next task uses `@livewireStyles`)

**Interfaces:**
- Produces: Livewire installed and discoverable; `@livewireScripts`/`@livewireStyles` directives available.

- [ ] **Step 1: Install**

Run:
```bash
cd backend
composer require livewire/livewire:^3.0
```
Expected: package resolves for Laravel 13. **If it does not resolve** (Livewire 3 not yet compatible with Laravel 13 in this environment), STOP and report to the user with the composer error; do not force-downgrade Laravel.

- [ ] **Step 2: Verify discovery**

Run: `cd backend && php artisan livewire:make Ping --stub= 2>/dev/null; php artisan about | grep -i livewire || php artisan livewire:publish --help >/dev/null && echo "livewire ok"`
Expected: Livewire commands exist. Remove any scratch component created (`app/Livewire/Ping.php`, `resources/views/livewire/ping.blade.php`).

- [ ] **Step 3: Commit**

```bash
git add backend/composer.json backend/composer.lock
git commit -m "chore: install livewire 3

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

### Task 3: Install Breeze (Livewire stack) session auth; fix role default & admin seed

**Files:**
- Modify: `backend/database/migrations/0001_01_01_000000_create_users_table.php:20`, `backend/app/Models/User.php`
- Create (by Breeze): `routes/auth.php`, auth Livewire/Volt views, `app/Livewire/Actions/Logout.php`, dashboard route
- Test: `backend/tests/Feature/AuthTest.php` (rewritten for session auth)

**Interfaces:**
- Consumes: Livewire (Task 2).
- Produces: `GET /login` renders a login form; `POST` login creates a **session**; `Auth::user()->isAdmin()` available; admin seeded as `admin@example.com` / `password123`.

- [ ] **Step 1: Flip the role default to `user`**

Edit users migration line 20:
```php
$table->enum('role', ['admin', 'user'])->default('user');
```

- [ ] **Step 2: Add `isAdmin()` to User model**

In `backend/app/Models/User.php`, add:
```php
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
```

- [ ] **Step 3: Write the failing auth test**

```php
<?php // backend/tests/Feature/AuthTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_admin_can_authenticate_with_session(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'password' => bcrypt('password123')]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
             ->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_registration_route_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
    }
}
```

- [ ] **Step 4: Run to verify it fails**

Run: `cd backend && php artisan test --filter=AuthTest`
Expected: FAIL (no `/login` route yet).

- [ ] **Step 5: Install Breeze (Livewire stack)**

Run:
```bash
cd backend
composer require laravel/breeze --dev
php artisan breeze:install livewire --no-interaction
```
Expected: Breeze scaffolds auth (login/forgot/reset), `routes/auth.php`, and includes it from `routes/web.php`. **If Breeze does not support Laravel 13** here, use the FALLBACK below instead of Breeze.

> **FALLBACK (only if Breeze install fails):** create a single `App\Livewire\Auth\Login` component + `Route::get('/login')` + `Route::post('/logout')` in `routes/web.php`, using `Auth::attempt()` and `Auth::logout()`. No register/reset UI. Keep the same test expectations. Document the deviation in the task's commit message.

- [ ] **Step 6: Disable public registration**

Remove the register + password-reset routes from `routes/auth.php` (keep login/logout). Delete the corresponding register view/component. `GET /register` must 404.

- [ ] **Step 7: Re-seed and re-run tests**

Run:
```bash
cd backend
php artisan migrate:fresh --seed
php artisan test --filter=AuthTest
```
Expected: PASS. Confirm seeded admin: `php artisan tinker --execute="echo App\Models\User::where('email','admin@example.com')->first()->isAdmin() ? 'admin ok' : 'FAIL';"`

- [ ] **Step 8: Commit**

```bash
git add -A
git commit -m "feat: session auth via breeze livewire stack, role default user, admin seed

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

### Task 4: Design-token system, fonts, app layout, theme toggle

**Files:**
- Modify: `backend/resources/css/app.css`, `backend/resources/js/app.js`, `backend/vite.config.js`
- Create: `backend/resources/views/layouts/app.blade.php`, `backend/resources/views/components/{button,card,tag,stat,empty-state,skeleton,modal,toast}.blade.php`
- Test: `backend/tests/Feature/PageTest.php` (extend)

**Interfaces:**
- Produces: `<x-layouts.app>` (or `layouts.app` via `@extends`) with navbar (links: 首頁/簡歷/收藏/單字庫, login/logout), theme toggle persisting to `localStorage`, footer, meta/OG. Reusable `<x-button>`, `<x-card>`, `<x-tag>`, `<x-stat>`, `<x-empty-state>`, `<x-skeleton>`, `<x-modal>`, `<x-toast>` used by all later UI tasks. CSS tokens: `--color-bg`, `--color-surface`, `--color-ink`, `--color-muted`, `--color-line`, `--color-accent`, `--color-accent-strong`.

- [ ] **Step 1: Write the failing test**

Extend `PageTest`:
```php
    public function test_layout_has_nav_and_theme_toggle(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-theme-toggle', $html);
        foreach (['首頁','簡歷','收藏','單字庫'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
    }
```

- [ ] **Step 2: Run to verify it fails**

Run: `cd backend && php artisan test --filter=PageTest`
Expected: FAIL (temporary home has no nav).

- [ ] **Step 3: Define fonts in vite**

Edit `backend/vite.config.js` fonts block:
```js
            fonts: [
                bunny('Space Grotesk', { weights: [500, 600, 700] }),
                bunny('Inter', { weights: [400, 500, 600] }),
                bunny('JetBrains Mono', { weights: [400, 500] }),
            ],
```

- [ ] **Step 4: Define design tokens in `app.css`**

Replace `backend/resources/css/app.css` with Tailwind v4 `@theme` tokens plus light/dark CSS variables:
```css
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../views/**/*.blade.php';
@source '../../app/Livewire/**/*.php';

@theme {
    --font-display: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
    --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'JetBrains Mono', ui-monospace, monospace;
    --color-accent: #06b6d4;      /* cyan-500 */
    --color-accent-strong: #14b8a6; /* teal-500 */
}

:root {
    --color-bg: #f8fafc; --color-surface: #ffffff; --color-ink: #0a0a0a;
    --color-muted: #475569; --color-line: #e2e8f0;
    --color-accent-text: #0891b2; /* cyan-600, AA on light */
}
:root[data-theme="dark"] {
    --color-bg: #020617; --color-surface: #0f172a; --color-ink: #f1f5f9;
    --color-muted: #94a3b8; --color-line: #1e293b;
    --color-accent-text: #22d3ee; /* cyan-400, AA on dark */
}
html { background: var(--color-bg); color: var(--color-ink); }
@media (prefers-reduced-motion: reduce) {
    * { animation: none !important; transition: none !important; }
}
```

- [ ] **Step 5: Theme persistence in `app.js`**

`backend/resources/js/app.js`:
```js
// Apply saved theme before paint (also duplicated inline in layout head to avoid FOUC).
const saved = localStorage.getItem('theme') || 'light';
document.documentElement.dataset.theme = saved;
window.toggleTheme = () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('theme', next);
};
```

- [ ] **Step 6: Build the app layout + UI components**

Create `backend/resources/views/layouts/app.blade.php` with: `<!doctype html>`, `<head>` containing charset/viewport, per-page `<title>`/`@yield('meta')` Open Graph tags, `@vite(['resources/css/app.css','resources/js/app.js'])`, `@livewireStyles`, and an inline `<script>` that sets `data-theme` from `localStorage` before body paint (FOUC guard). `<body>`: a `<nav>` with brand, the four page links (using `route()` names `home`, `resume`, `collection`, `vocabulary`), an `@auth` logout / `@guest` login control, and a `<button data-theme-toggle onclick="toggleTheme()">`. `{{ $slot }}` (component) or `@yield('content')`. Footer. `@livewireScripts`.

Create the eight `components/*.blade.php` using the tokens (e.g. `<x-button variant="primary|ghost">`, `<x-card>`, `<x-tag color>`, `<x-stat label value>`, `<x-empty-state>`, `<x-skeleton>`, `<x-modal>` (Alpine `x-data`/`x-show`), `<x-toast>` (listens for a Livewire browser event `toast`)). Keep each component small and token-driven. Use `route('resume')` etc.; define placeholder routes now (Task 5/6 add real components; for now point `resume`/`collection`/`vocabulary` at temporary `Route::view` stubs so `route()` resolves).

Add temporary routes in `routes/web.php`:
```php
Route::view('/resume', 'resume')->name('resume');       // replaced in Task 12
Route::view('/collection', 'placeholder')->name('collection'); // replaced in Task 8
Route::view('/vocabulary', 'placeholder')->name('vocabulary'); // replaced in Task 9
```
Create trivial `resume.blade.php`/`placeholder.blade.php` extending the layout so pages render.

- [ ] **Step 7: Build assets and run tests**

Run:
```bash
cd backend
npm run build
php artisan test --filter=PageTest
```
Expected: build succeeds; PageTest passes (nav labels + theme toggle present).

- [ ] **Step 8: Manual verification (evidence)**

Run `php artisan serve`, open `/`, confirm: light theme by default, toggle flips to dark and persists on reload, nav renders, no console errors, contrast looks correct. Then stop server.

- [ ] **Step 9: Commit**

```bash
git add -A
git commit -m "feat: design tokens, fonts, app layout, theme toggle, ui components

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Phase 2 — Authorization foundation

### Task 5: Role policies for collection and vocabulary writes

**Files:**
- Create: `backend/app/Policies/CollectionWorkPolicy.php`, `backend/app/Policies/StudyVocabularyPolicy.php`
- Modify: `backend/app/Providers/AppServiceProvider.php` (register policies if not auto-discovered)
- Test: `backend/tests/Unit/PolicyTest.php`

**Interfaces:**
- Consumes: `User::isAdmin()` (Task 3).
- Produces: Gate abilities `create`, `update`, `delete` on `CollectionWork` and `StudyVocabulary`, true only for admins. Livewire components call `$this->authorize('create', CollectionWork::class)` etc.

- [ ] **Step 1: Write the failing test**

```php
<?php // backend/tests/Unit/PolicyTest.php
namespace Tests\Unit;

use App\Models\{CollectionWork, StudyVocabulary, User};
use App\Policies\{CollectionWorkPolicy, StudyVocabularyPolicy};
use PHPUnit\Framework\TestCase;

class PolicyTest extends TestCase
{
    public function test_admin_can_write_collection(): void
    {
        $admin = new User(['role' => 'admin']);
        $this->assertTrue((new CollectionWorkPolicy)->create($admin));
        $this->assertTrue((new CollectionWorkPolicy)->update($admin, new CollectionWork));
    }
    public function test_user_cannot_write_collection(): void
    {
        $user = new User(['role' => 'user']);
        $this->assertFalse((new CollectionWorkPolicy)->create($user));
    }
    public function test_admin_only_vocabulary(): void
    {
        $this->assertTrue((new StudyVocabularyPolicy)->delete(new User(['role'=>'admin']), new StudyVocabulary));
        $this->assertFalse((new StudyVocabularyPolicy)->delete(new User(['role'=>'user']), new StudyVocabulary));
    }
}
```
(Note: `User` must allow `role` in `$fillable` — it already does via the `#[Fillable]` attribute.)

- [ ] **Step 2: Run to verify it fails**

Run: `cd backend && php artisan test --filter=PolicyTest`
Expected: FAIL (policy classes missing).

- [ ] **Step 3: Implement the policies**

`backend/app/Policies/CollectionWorkPolicy.php`:
```php
<?php
namespace App\Policies;

use App\Models\{CollectionWork, User};

class CollectionWorkPolicy
{
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, CollectionWork $work): bool { return $user->isAdmin(); }
    public function delete(User $user, CollectionWork $work): bool { return $user->isAdmin(); }
}
```
`backend/app/Policies/StudyVocabularyPolicy.php` — same shape for `StudyVocabulary`.

- [ ] **Step 4: Run to verify it passes**

Run: `cd backend && php artisan test --filter=PolicyTest`
Expected: PASS. (Laravel 13 auto-discovers policies by naming convention; if a gate resolution test later fails, register explicitly in `AppServiceProvider::boot()` with `Gate::policy(...)`.)

- [ ] **Step 5: Commit**

```bash
git add backend/app/Policies backend/tests/Unit/PolicyTest.php
git commit -m "feat: admin-only write policies for collection and vocabulary

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Phase 3 — Services (logic migration + bug fixes)

### Task 6: SpacedRepetition service (SM-2, edge-case fixed)

**Files:**
- Create: `backend/app/Services/SpacedRepetition.php`
- Test: `backend/tests/Unit/SpacedRepetitionTest.php`

**Interfaces:**
- Consumes: `StudyVocabulary` model (fields `familiarity`, `next_review_at`, `last_reviewed_at`).
- Produces: `SpacedRepetition::calculate(StudyVocabulary $word, string $result): array` returning `[Carbon $nextReviewAt, int $newFamiliarity]`. `$result` ∈ `forgot|vague|remembered|mastered`. Used by `Flashcard` (Task 11).

- [ ] **Step 1: Write the failing tests**

```php
<?php // backend/tests/Unit/SpacedRepetitionTest.php
namespace Tests\Unit;

use App\Models\StudyVocabulary;
use App\Services\SpacedRepetition;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class SpacedRepetitionTest extends TestCase
{
    protected function setUp(): void { parent::setUp(); Carbon::setTestNow('2026-07-04 12:00:00'); }
    protected function tearDown(): void { Carbon::setTestNow(); parent::tearDown(); }

    private function word(array $attrs = []): StudyVocabulary
    {
        return new StudyVocabulary(array_merge(['familiarity' => 2], $attrs));
    }

    public function test_forgot_resets_to_one_day_and_lowers_familiarity(): void
    {
        [$next, $fam] = (new SpacedRepetition)->calculate($this->word(['familiarity'=>3]), 'forgot');
        $this->assertEquals(Carbon::now()->addDay()->toDateString(), $next->toDateString());
        $this->assertSame(2, $fam);
    }

    public function test_forgot_familiarity_floors_at_zero(): void
    {
        [, $fam] = (new SpacedRepetition)->calculate($this->word(['familiarity'=>0]), 'forgot');
        $this->assertSame(0, $fam);
    }

    public function test_new_word_remembered_uses_base_interval(): void
    {
        // no last_reviewed_at/next_review_at -> base interval 1 -> *2 = 2 days
        [$next, $fam] = (new SpacedRepetition)->calculate($this->word(['familiarity'=>1]), 'remembered');
        $this->assertEquals(Carbon::now()->addDays(2)->toDateString(), $next->toDateString());
        $this->assertSame(2, $fam);
    }

    public function test_vague_keeps_interval_and_familiarity(): void
    {
        $w = $this->word([
            'familiarity' => 3,
            'last_reviewed_at' => Carbon::parse('2026-06-27 12:00:00'),
            'next_review_at'   => Carbon::parse('2026-07-04 12:00:00'), // 7-day interval
        ]);
        [$next, $fam] = (new SpacedRepetition)->calculate($w, 'vague');
        $this->assertEquals(Carbon::now()->addDays(7)->toDateString(), $next->toDateString());
        $this->assertSame(3, $fam);
    }

    public function test_mastered_multiplies_interval_by_2_5_and_caps_familiarity(): void
    {
        $w = $this->word([
            'familiarity' => 5,
            'last_reviewed_at' => Carbon::parse('2026-06-30 12:00:00'),
            'next_review_at'   => Carbon::parse('2026-07-04 12:00:00'), // 4-day interval
        ]);
        [$next, $fam] = (new SpacedRepetition)->calculate($w, 'mastered');
        $this->assertEquals(Carbon::now()->addDays(10)->toDateString(), $next->toDateString()); // 4*2.5
        $this->assertSame(5, $fam); // capped
    }

    public function test_overdue_word_does_not_produce_negative_interval(): void
    {
        // next_review_at in the past relative to now, last_reviewed even earlier
        $w = $this->word([
            'familiarity' => 2,
            'last_reviewed_at' => Carbon::parse('2026-06-20 12:00:00'),
            'next_review_at'   => Carbon::parse('2026-06-25 12:00:00'), // 5-day interval, both past
        ]);
        [$next] = (new SpacedRepetition)->calculate($w, 'remembered');
        $this->assertTrue($next->greaterThan(Carbon::now())); // never in the past
        $this->assertEquals(Carbon::now()->addDays(10)->toDateString(), $next->toDateString()); // 5*2
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `cd backend && php artisan test --filter=SpacedRepetitionTest`
Expected: FAIL (service missing).

- [ ] **Step 3: Implement the service**

```php
<?php // backend/app/Services/SpacedRepetition.php
namespace App\Services;

use App\Models\StudyVocabulary;
use Illuminate\Support\Carbon;

class SpacedRepetition
{
    /** @return array{0: Carbon, 1: int} */
    public function calculate(StudyVocabulary $word, string $result): array
    {
        $now      = Carbon::now();
        $interval = $this->currentIntervalDays($word); // always >= 1
        $fam      = (int) ($word->familiarity ?? 0);

        return match ($result) {
            'forgot'     => [$now->copy()->addDay(),                          max(0, $fam - 1)],
            'vague'      => [$now->copy()->addDays($interval),                $fam],
            'remembered' => [$now->copy()->addDays($interval * 2),           min(5, $fam + 1)],
            'mastered'   => [$now->copy()->addDays((int) round($interval * 2.5)), min(5, $fam + 1)],
        };
    }

    private function currentIntervalDays(StudyVocabulary $word): int
    {
        if ($word->next_review_at && $word->last_reviewed_at) {
            // Interval that was scheduled between last review and its due date.
            $days = (int) floor($word->last_reviewed_at->diffInDays($word->next_review_at, absolute: true));
            return max(1, $days);
        }
        return 1;
    }
}
```

- [ ] **Step 4: Run to verify it passes**

Run: `cd backend && php artisan test --filter=SpacedRepetitionTest`
Expected: PASS (all 6).

- [ ] **Step 5: Commit**

```bash
git add backend/app/Services/SpacedRepetition.php backend/tests/Unit/SpacedRepetitionTest.php
git commit -m "feat: SpacedRepetition service with overdue-interval fix

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

### Task 7: VocabularyLookup service (cached, fault-tolerant proxy)

**Files:**
- Create: `backend/app/Services/VocabularyLookup.php`
- Test: `backend/tests/Unit/VocabularyLookupTest.php`

**Interfaces:**
- Consumes: `config('services.dictionary_api')`, `config('services.mymemory_api')` (already defined).
- Produces: `VocabularyLookup::lookup(string $word): array` → keys `word, meaning, part_of_speech, phonetic, audio_url, example`. Caches per word 24h. One failing upstream never throws. Used by `VocabularyForm` (Task 10).

- [ ] **Step 1: Write the failing tests (HTTP faked)**

```php
<?php // backend/tests/Unit/VocabularyLookupTest.php
namespace Tests\Unit;

use App\Services\VocabularyLookup;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VocabularyLookupTest extends TestCase
{
    public function test_merges_both_apis(): void
    {
        Http::fake([
            'api.dictionaryapi.dev/*' => Http::response([[
                'phonetic' => '/tɛst/',
                'phonetics' => [['audio' => 'https://a/test.mp3']],
                'meanings' => [['partOfSpeech' => 'noun',
                    'definitions' => [['example' => 'a test sentence']]]],
            ]], 200),
            'api.mymemory.translated.net/*' => Http::response([
                'responseData' => ['translatedText' => '測試'],
            ], 200),
        ]);

        $r = (new VocabularyLookup)->lookup('test');

        $this->assertSame('test', $r['word']);
        $this->assertSame('測試', $r['meaning']);
        $this->assertSame('noun', $r['part_of_speech']);
        $this->assertSame('/tɛst/', $r['phonetic']);
        $this->assertSame('https://a/test.mp3', $r['audio_url']);
        $this->assertSame('a test sentence', $r['example']);
    }

    public function test_dictionary_failure_still_returns_translation(): void
    {
        Http::fake([
            'api.dictionaryapi.dev/*' => Http::response('not found', 404),
            'api.mymemory.translated.net/*' => Http::response(['responseData' => ['translatedText' => '測試']], 200),
        ]);
        $r = (new VocabularyLookup)->lookup('test');
        $this->assertSame('測試', $r['meaning']);
        $this->assertNull($r['part_of_speech']);
    }

    public function test_total_failure_returns_nulls_without_throwing(): void
    {
        Http::fake(fn () => Http::response('', 500));
        $r = (new VocabularyLookup)->lookup('test');
        $this->assertSame('test', $r['word']);
        $this->assertNull($r['meaning']);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `cd backend && php artisan test --filter=VocabularyLookupTest`
Expected: FAIL (service missing).

- [ ] **Step 3: Implement the service**

```php
<?php // backend/app/Services/VocabularyLookup.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VocabularyLookup
{
    /** @return array{word:string,meaning:?string,part_of_speech:?string,phonetic:?string,audio_url:?string,example:?string} */
    public function lookup(string $word): array
    {
        $word = trim($word);
        return Cache::remember("vocab_lookup:".mb_strtolower($word), now()->addDay(), function () use ($word) {
            return array_merge(
                ['word' => $word, 'meaning' => null, 'part_of_speech' => null,
                 'phonetic' => null, 'audio_url' => null, 'example' => null],
                $this->fromDictionary($word),
                $this->fromTranslator($word),
            );
        });
    }

    private function fromDictionary(string $word): array
    {
        try {
            $res = Http::timeout(5)->get(config('services.dictionary_api')."/api/v2/entries/en/{$word}");
            if (!$res->successful()) return [];
            $data = $res->json()[0] ?? null;
            if (!$data) return [];
            $meaning = $data['meanings'][0] ?? [];
            $audio = collect($data['phonetics'] ?? [])->first(fn ($p) => !empty($p['audio']));
            return [
                'part_of_speech' => $meaning['partOfSpeech'] ?? null,
                'example'        => $meaning['definitions'][0]['example'] ?? null,
                'phonetic'       => $data['phonetic'] ?? null,
                'audio_url'      => $audio['audio'] ?? null,
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    private function fromTranslator(string $word): array
    {
        try {
            $res = Http::timeout(5)->get(config('services.mymemory_api').'/get', [
                'q' => $word, 'langpair' => 'en|zh-TW',
            ]);
            if (!$res->successful()) return [];
            return ['meaning' => $res->json()['responseData']['translatedText'] ?? null];
        } catch (\Throwable) {
            return [];
        }
    }
}
```

- [ ] **Step 4: Run to verify it passes**

Run: `cd backend && php artisan test --filter=VocabularyLookupTest`
Expected: PASS (3). (Test cache store is `array`, so caching is inert per test — fine.)

- [ ] **Step 5: Commit**

```bash
git add backend/app/Services/VocabularyLookup.php backend/tests/Unit/VocabularyLookupTest.php
git commit -m "feat: cached fault-tolerant VocabularyLookup service

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Phase 4 — Collection feature

### Task 8: CollectionIndex Livewire component (tabs, stats, filters, search, sort, view)

**Files:**
- Create: `backend/app/Livewire/CollectionIndex.php`, `backend/resources/views/livewire/collection-index.blade.php`
- Modify: `backend/routes/web.php` (point `collection` at the component)
- Test: `backend/tests/Feature/CollectionIndexTest.php`

**Interfaces:**
- Consumes: `CollectionWork` (+ `categories`), `CollectionCategory`.
- Produces: route `GET /collection` → `\App\Livewire\CollectionIndex`. Public read. Public props: `string $type='all'`, `?string $status=null`, `?int $yearMin=null`, `?int $yearMax=null`, `?string $season=null`, `array $categoryIds=[]`, `string $categoryMode='or'`, `?int $ratingMin=null`, `?int $ratingMax=null`, `bool $favoriteOnly=false`, `string $search=''`, `string $sort='newest'`, `string $view='card'`. Emits `open-collection-form` / listens `collection-saved`,`collection-deleted` to refresh. `CollectionForm` (Task 9) depends on these event names.

- [ ] **Step 1: Write the failing tests**

```php
<?php // backend/tests/Feature/CollectionIndexTest.php
namespace Tests\Feature;

use App\Livewire\CollectionIndex;
use App\Models\{CollectionCategory, CollectionWork};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_renders_component(): void
    {
        $this->get('/collection')->assertOk()->assertSeeLivewire(CollectionIndex::class);
    }

    public function test_lists_and_filters_by_type(): void
    {
        CollectionWork::factory()->create(['type' => 'anime', 'title' => 'A']);
        CollectionWork::factory()->create(['type' => 'manga', 'title' => 'M']);

        Livewire::test(CollectionIndex::class)
            ->assertSee('A')->assertSee('M')
            ->set('type', 'anime')
            ->assertSee('A')->assertDontSee('M');
    }

    public function test_search_matches_title_and_original(): void
    {
        CollectionWork::factory()->create(['title' => '進擊的巨人', 'title_original' => 'Shingeki']);
        CollectionWork::factory()->create(['title' => '孤獨搖滾', 'title_original' => 'Bocchi']);

        Livewire::test(CollectionIndex::class)
            ->set('search', 'Shingeki')
            ->assertSee('進擊的巨人')->assertDontSee('孤獨搖滾');
    }

    public function test_category_and_mode_requires_all_selected(): void
    {
        $love = CollectionCategory::factory()->create(['name' => '戀愛']);
        $school = CollectionCategory::factory()->create(['name' => '校園']);
        $both = CollectionWork::factory()->create(['title' => 'Both']);
        $both->categories()->sync([$love->id, $school->id]);
        $one = CollectionWork::factory()->create(['title' => 'One']);
        $one->categories()->sync([$love->id]);

        Livewire::test(CollectionIndex::class)
            ->set('categoryIds', [$love->id, $school->id])
            ->set('categoryMode', 'and')
            ->assertSee('Both')->assertDontSee('One');
    }

    public function test_stats_reflect_data(): void
    {
        CollectionWork::factory()->count(2)->create(['status' => 'completed']);
        CollectionWork::factory()->create(['status' => 'watching', 'is_favorite' => true]);

        Livewire::test(CollectionIndex::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 3 && $s['completed'] === 2 && $s['favorites'] === 1);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `cd backend && php artisan test --filter=CollectionIndexTest`
Expected: FAIL (component + route missing).

- [ ] **Step 3: Implement the component**

`backend/app/Livewire/CollectionIndex.php` — public props as in Interfaces; `#[Url]` on `type`,`search`,`view` for shareable state; a `getWorksProperty()` building the query with `CollectionWork::with('categories')` applying: `type` (unless `all`), `status`, `yearMin/yearMax` range, `season`, `favoriteOnly`, `ratingMin/ratingMax`, grouped `search` (title OR title_original inside a nested closure — mirror the correct pattern already in `CollectionController::index`), and category filter respecting `categoryMode`:
```php
if ($this->categoryIds) {
    if ($this->categoryMode === 'and') {
        foreach ($this->categoryIds as $cid) {
            $q->whereHas('categories', fn ($c) => $c->where('collection_categories.id', $cid));
        }
    } else {
        $q->whereHas('categories', fn ($c) => $c->whereIn('collection_categories.id', $this->categoryIds));
    }
}
```
Sort: `newest` → `orderByDesc('created_at')`, plus `rating`, `year`, `title` options. `render()` passes `works` (paginated, e.g. `->paginate(24)`) and a `stats` array (total/anime/manga/completed/watching/favorites, mirroring `CollectionController::stats`) and `categories` grouped. Use `WithPagination`. Resetting page on filter change via `updated()` hook.

`backend/resources/views/livewire/collection-index.blade.php` — extends the app layout; renders: type tab bar, stat row (`<x-stat>`), toolbar (search input `wire:model.live.debounce.300ms="search"`, filter controls, sort `<select>`, card/table `$view` toggle, and an `@can('create', App\Models\CollectionWork::class)` "新增" button dispatching `open-collection-form`), then the works grid/table with `<x-card>` per work (cover, title, year+season tag, category `<x-tag>`s, star rating, favorite icon; admin edit/delete controls), an `<x-empty-state>` when none, `<x-skeleton>` while loading (`wire:loading`), and pagination links.

Route (`routes/web.php`), replace the placeholder:
```php
use App\Livewire\CollectionIndex;
Route::get('/collection', CollectionIndex::class)->name('collection');
```

- [ ] **Step 4: Run to verify it passes**

Run: `cd backend && php artisan test --filter=CollectionIndexTest`
Expected: PASS (5).

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: CollectionIndex livewire component with filters, search, stats

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

### Task 9: CollectionForm Livewire component (create/edit modal, authorized)

**Files:**
- Create: `backend/app/Livewire/CollectionForm.php`, `backend/resources/views/livewire/collection-form.blade.php`
- Test: `backend/tests/Feature/CollectionFormTest.php`

**Interfaces:**
- Consumes: `CollectionWorkPolicy` (Task 5), event `open-collection-form` (payload optional `workId`).
- Produces: on save, `sync` categories and dispatch `collection-saved` (refreshes `CollectionIndex`) + a `toast` browser event. Validation rules mirror `CollectionController::store` exactly (type/status enums, rating 0–5, year 1900–2100, season enum, url max 500, `category_ids.* exists`).

- [ ] **Step 1: Write the failing tests**

```php
<?php // backend/tests/Feature/CollectionFormTest.php
namespace Tests\Feature;

use App\Livewire\CollectionForm;
use App\Models\{CollectionCategory, CollectionWork, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_work_with_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cat = CollectionCategory::factory()->create();

        Livewire::actingAs($admin)->test(CollectionForm::class)
            ->set('type', 'anime')->set('title', '進擊的巨人')->set('status', 'completed')
            ->set('categoryIds', [$cat->id])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('collection-saved');

        $this->assertDatabaseHas('collection_works', ['title' => '進擊的巨人', 'type' => 'anime']);
        $work = CollectionWork::first();
        $this->assertEqualsCanonicalizing([$cat->id], $work->categories->pluck('id')->all());
    }

    public function test_validation_rejects_bad_rating(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Livewire::actingAs($admin)->test(CollectionForm::class)
            ->set('type','anime')->set('title','X')->set('status','plan')->set('rating', 9)
            ->call('save')->assertHasErrors('rating');
    }

    public function test_non_admin_cannot_save(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Livewire::actingAs($user)->test(CollectionForm::class)
            ->set('type','anime')->set('title','X')->set('status','plan')
            ->call('save')->assertForbidden();
    }

    public function test_guest_cannot_save(): void
    {
        Livewire::test(CollectionForm::class)
            ->set('type','anime')->set('title','X')->set('status','plan')
            ->call('save')->assertForbidden();
    }

    public function test_admin_can_edit_existing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $work = CollectionWork::factory()->create(['title' => 'Old']);
        Livewire::actingAs($admin)->test(CollectionForm::class)
            ->call('open', $work->id)
            ->assertSet('title', 'Old')
            ->set('title', 'New')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('collection_works', ['id' => $work->id, 'title' => 'New']);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `cd backend && php artisan test --filter=CollectionFormTest`
Expected: FAIL.

- [ ] **Step 3: Implement the component**

`CollectionForm.php`: public props for every editable field + `array $categoryIds=[]`, `?int $workId=null`, `bool $show=false`. `#[On('open-collection-form')] open(?int $workId=null)` loads the work (or resets to defaults) and opens the modal. `rules()` returns the exact validation array from `CollectionController::store` (adapt `unique` not needed here). `save()`:
```php
public function save(): void
{
    $this->authorize($this->workId ? 'update' : 'create',
        $this->workId ? CollectionWork::findOrFail($this->workId) : CollectionWork::class);
    $data = $this->validate();
    $work = $this->workId
        ? tap(CollectionWork::findOrFail($this->workId))->update($data)
        : CollectionWork::create($data);
    $work->categories()->sync($this->categoryIds);
    $this->show = false;
    $this->dispatch('collection-saved');
    $this->dispatch('toast', message: '已儲存', type: 'success');
}
```
Add `delete()` guarded by `$this->authorize('delete', $work)` dispatching `collection-deleted` + toast. `assertForbidden()` passes because `$this->authorize` throws `AuthorizationException` (403) which Livewire surfaces.

`collection-form.blade.php`: an `<x-modal wire:model="show">` with a form; fields switch by `type` (anime → episodes/season/studio; manga → volumes/author). Inputs use `wire:model`. Category multiselect binds `categoryIds`. Save/cancel buttons; validation error display under each field.

- [ ] **Step 4: Run to verify it passes**

Run: `cd backend && php artisan test --filter=CollectionFormTest`
Expected: PASS (5).

- [ ] **Step 5: Wire the form into the index + commit**

Include `<livewire:collection-form />` in `collection-index.blade.php`; ensure the "新增" button dispatches `open-collection-form`, and `CollectionIndex` has `#[On('collection-saved')]` and `#[On('collection-deleted')]` no-op handlers (their presence triggers re-render). Manually verify create/edit/delete in the browser as admin, and that a guest sees no write controls and cannot POST. Commit:
```bash
git add -A
git commit -m "feat: CollectionForm modal with authorization and category sync

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Phase 5 — Vocabulary feature

### Task 10: VocabularyIndex + VocabularyForm (browse/filter fix, lookup, duplicate detection)

**Files:**
- Create: `backend/app/Livewire/VocabularyIndex.php`, `VocabularyForm.php`, and their blade views
- Modify: `backend/routes/web.php` (point `vocabulary` at index)
- Test: `backend/tests/Feature/VocabularyIndexTest.php`, `VocabularyFormTest.php`

**Interfaces:**
- Consumes: `StudyVocabulary`, `VocabularyLookup` (Task 7), `StudyVocabularyPolicy` (Task 5).
- Produces: route `GET /vocabulary` → `VocabularyIndex` with `string $mode='browse'` (browse|add|flashcard), `string $search=''`, `?int $familiarity=null`. `VocabularyForm` exposes `lookup()` (calls the service, pre-fills, sets `auto_filled=true`) and `save()` with duplicate detection.

- [ ] **Step 1: Write the failing VocabularyIndex test (includes the filter-grouping bug fix)**

```php
<?php // backend/tests/Feature/VocabularyIndexTest.php
namespace Tests\Feature;

use App\Livewire\VocabularyIndex;
use App\Models\StudyVocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VocabularyIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_renders(): void
    {
        $this->get('/vocabulary')->assertOk()->assertSeeLivewire(VocabularyIndex::class);
    }

    public function test_search_and_familiarity_filters_combine_correctly(): void
    {
        // Regression for the where()->orWhere() grouping bug in the old API.
        StudyVocabulary::factory()->create(['word' => 'alpha', 'meaning' => 'x', 'familiarity' => 5]);
        StudyVocabulary::factory()->create(['word' => 'alphabet', 'meaning' => 'y', 'familiarity' => 1]);
        StudyVocabulary::factory()->create(['word' => 'beta', 'meaning' => 'alpha soup', 'familiarity' => 5]);

        Livewire::test(VocabularyIndex::class)
            ->set('search', 'alpha')     // matches alpha, alphabet, beta(meaning)
            ->set('familiarity', 5)      // must AND with search -> alpha, beta
            ->assertSee('alpha')->assertSee('beta')->assertDontSee('alphabet');
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `cd backend && php artisan test --filter=VocabularyIndexTest`
Expected: FAIL.

- [ ] **Step 3: Implement VocabularyIndex (grouped search)**

Query builder in `getWordsProperty()`:
```php
$q = StudyVocabulary::query();
if ($this->search !== '') {
    $q->where(function ($sub) {
        $sub->where('word', 'like', "%{$this->search}%")
            ->orWhere('meaning', 'like', "%{$this->search}%");
    });
}
if ($this->familiarity !== null) {
    $q->where('familiarity', $this->familiarity);
}
return $q->orderByDesc('created_at')->paginate(30);
```
`render()` also computes `stats` (total, added_this_week, pending_review, avg_familiarity — mirror `VocabularyController::stats`). Blade view: mode tabs (Browse/Add/Flashcard), and in browse mode the search + familiarity filter + list of `<x-card>` word entries (word, phonetic, part_of_speech tag, meaning, familiarity meter, pronounce button using `audio_url` or `speechSynthesis`). Route:
```php
use App\Livewire\VocabularyIndex;
Route::get('/vocabulary', VocabularyIndex::class)->name('vocabulary');
```

- [ ] **Step 4: Run to verify it passes**

Run: `cd backend && php artisan test --filter=VocabularyIndexTest`
Expected: PASS.

- [ ] **Step 5: Write the failing VocabularyForm tests**

```php
<?php // backend/tests/Feature/VocabularyFormTest.php
namespace Tests\Feature;

use App\Livewire\VocabularyForm;
use App\Models\{StudyVocabulary, User};
use App\Services\VocabularyLookup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class VocabularyFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_prefills_fields(): void
    {
        Http::fake([
            'api.dictionaryapi.dev/*' => Http::response([[
                'phonetic' => '/wɜːrd/', 'phonetics' => [['audio' => 'https://a/word.mp3']],
                'meanings' => [['partOfSpeech' => 'noun', 'definitions' => [['example' => 'a word']]]],
            ]], 200),
            'api.mymemory.translated.net/*' => Http::response(['responseData' => ['translatedText' => '單字']], 200),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)->test(VocabularyForm::class)
            ->set('word', 'word')->call('lookup')
            ->assertSet('meaning', '單字')->assertSet('part_of_speech', 'noun')
            ->assertSet('auto_filled', true);
    }

    public function test_admin_can_save_new_word(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Livewire::actingAs($admin)->test(VocabularyForm::class)
            ->set('word', 'serendipity')->set('meaning', '機緣')
            ->call('save')->assertHasNoErrors()->assertDispatched('vocabulary-saved');
        $this->assertDatabaseHas('study_vocabulary', ['word' => 'serendipity']);
        $this->assertNotNull(StudyVocabulary::first()->next_review_at); // scheduled +1 day
    }

    public function test_duplicate_word_is_flagged(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        StudyVocabulary::factory()->create(['word' => 'dup', 'meaning' => 'a']);
        Livewire::actingAs($admin)->test(VocabularyForm::class)
            ->set('word', 'dup')->set('meaning', 'b')
            ->call('save')->assertHasErrors('word');
    }

    public function test_non_admin_cannot_save(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Livewire::actingAs($user)->test(VocabularyForm::class)
            ->set('word','x')->set('meaning','y')->call('save')->assertForbidden();
    }
}
```

- [ ] **Step 6: Run to verify it fails**

Run: `cd backend && php artisan test --filter=VocabularyFormTest`
Expected: FAIL.

- [ ] **Step 7: Implement VocabularyForm**

Public props: `word, meaning, part_of_speech, phonetic, audio_url, example, example_zh, source, note`; `bool $auto_filled=false`. `lookup(VocabularyLookup $svc)`:
```php
public function lookup(VocabularyLookup $svc): void
{
    $this->authorize('create', StudyVocabulary::class);
    $this->validate(['word' => 'required|string|max:100']);
    $r = $svc->lookup($this->word);
    $this->fill(array_filter([
        'meaning' => $r['meaning'], 'part_of_speech' => $r['part_of_speech'],
        'phonetic' => $r['phonetic'], 'audio_url' => $r['audio_url'], 'example' => $r['example'],
    ], fn ($v) => $v !== null));
    $this->auto_filled = true;
}
```
`save()`: `$this->authorize('create', StudyVocabulary::class)`, validate with `'word' => 'required|string|max:100|unique:study_vocabulary,word'` (this produces the duplicate error), other fields per `VocabularyController::store`; set `next_review_at = now()->addDay()`; create; dispatch `vocabulary-saved` + toast; reset fields. Blade: word input with a "查詢" button (`wire:click="lookup"`, `wire:loading` spinner), editable pre-filled fields, duplicate warning surfaced via the `word` error bag.

- [ ] **Step 8: Run to verify it passes**

Run: `cd backend && php artisan test --filter=VocabularyFormTest`
Expected: PASS (4).

- [ ] **Step 9: Commit**

```bash
git add -A
git commit -m "feat: vocabulary browse/add with lookup, duplicate detection, filter fix

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

### Task 11: Flashcard review component (SM-2 wired, progress)

**Files:**
- Create: `backend/app/Livewire/Flashcard.php`, `backend/resources/views/livewire/flashcard.blade.php`
- Test: `backend/tests/Feature/FlashcardTest.php`

**Interfaces:**
- Consumes: `SpacedRepetition` (Task 6), `StudyVocabulary`, `StudyVocabularyPolicy`.
- Produces: review session over the due queue (`next_review_at <= now` OR null, ordered by `next_review_at`). `rate(string $result)` (admin only) applies the service, advances to the next card, tracks today's progress.

- [ ] **Step 1: Write the failing tests**

```php
<?php // backend/tests/Feature/FlashcardTest.php
namespace Tests\Feature;

use App\Livewire\Flashcard;
use App\Models\{StudyVocabulary, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class FlashcardTest extends TestCase
{
    use RefreshDatabase;

    public function test_loads_due_queue_only(): void
    {
        StudyVocabulary::factory()->create(['word' => 'due', 'next_review_at' => now()->subDay()]);
        StudyVocabulary::factory()->create(['word' => 'later', 'next_review_at' => now()->addWeek()]);
        Livewire::test(Flashcard::class)->assertSet('queue.0.word', 'due')->assertCount('queue', 1);
    }

    public function test_admin_rating_updates_schedule_and_advances(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $w = StudyVocabulary::factory()->create(['word' => 'x', 'familiarity' => 1, 'next_review_at' => now()->subDay()]);

        Livewire::actingAs($admin)->test(Flashcard::class)
            ->call('rate', 'remembered')
            ->assertSet('reviewedToday', 1);

        $w->refresh();
        $this->assertSame(2, $w->familiarity);
        $this->assertTrue($w->next_review_at->greaterThan(Carbon::now()));
        $this->assertSame(1, $w->review_count);
        $this->assertSame(1, $w->correct_count);
    }

    public function test_non_admin_cannot_rate(): void
    {
        StudyVocabulary::factory()->create(['next_review_at' => now()->subDay()]);
        Livewire::test(Flashcard::class)->call('rate', 'remembered')->assertForbidden();
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `cd backend && php artisan test --filter=FlashcardTest`
Expected: FAIL.

- [ ] **Step 3: Implement the component**

```php
<?php // backend/app/Livewire/Flashcard.php (essentials)
namespace App\Livewire;

use App\Models\StudyVocabulary;
use App\Services\SpacedRepetition;
use Livewire\Component;

class Flashcard extends Component
{
    public array $queue = [];
    public int $index = 0;
    public bool $flipped = false;
    public int $reviewedToday = 0;

    public function mount(): void { $this->loadQueue(); }

    public function loadQueue(): void
    {
        $this->queue = StudyVocabulary::query()
            ->where(fn ($q) => $q->where('next_review_at', '<=', now())->orWhereNull('next_review_at'))
            ->orderBy('next_review_at')
            ->get()->toArray();
        $this->index = 0; $this->flipped = false;
    }

    public function flip(): void { $this->flipped = ! $this->flipped; }

    public function rate(string $result, SpacedRepetition $svc): void
    {
        $this->authorize('update', StudyVocabulary::class);
        abort_if(! in_array($result, ['forgot','vague','remembered','mastered'], true), 422);
        $card = StudyVocabulary::findOrFail($this->queue[$this->index]['id']);
        [$next, $fam] = $svc->calculate($card, $result);
        $card->update([
            'next_review_at' => $next, 'last_reviewed_at' => now(), 'familiarity' => $fam,
            'review_count' => $card->review_count + 1,
            'correct_count' => in_array($result, ['remembered','mastered'], true)
                ? $card->correct_count + 1 : $card->correct_count,
        ]);
        $this->reviewedToday++;
        $this->index++; $this->flipped = false;
    }

    public function render() { return view('livewire.flashcard'); }
}
```
Note: `authorize('update', StudyVocabulary::class)` uses the class-level policy check (guests/users → 403). Blade: front (word + pronounce), flip button (subtle 3D via Alpine + CSS `transform`), back (meaning + example), four rating buttons (`wire:click="rate('forgot')"` …) admin-only, today's progress + "done" empty-state when `index >= count(queue)`.

- [ ] **Step 4: Run to verify it passes**

Run: `cd backend && php artisan test --filter=FlashcardTest`
Expected: PASS (3).

- [ ] **Step 5: Wire flashcard into the vocabulary page + commit**

Render `<livewire:flashcard />` when `VocabularyIndex` mode is `flashcard`. Manually verify flip + rating as admin. Commit:
```bash
git add -A
git commit -m "feat: flashcard review wired to SpacedRepetition with progress

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Phase 6 — Static pages

### Task 12: Home and Résumé pages (placeholder content, print CSS)

**Files:**
- Modify: `backend/resources/views/home.blade.php`, `backend/resources/views/resume.blade.php`
- Test: `backend/tests/Feature/PageTest.php` (extend)

**Interfaces:**
- Consumes: app layout + UI components (Task 4).
- Produces: full Home (hero, positioning line, 3 nav cards, social links) and Résumé (timeline education/experience, project cards, color-coded skill tags, certifications, Download-CV button, print-optimized CSS). All personal data uses clearly-marked placeholders (`[Your Name]`, `you@example.com`, sample history).

- [ ] **Step 1: Extend the failing test**

```php
    public function test_home_has_nav_cards_and_socials(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        foreach (['個人簡歷','收藏','單字庫','GitHub','Email'] as $t) {
            $this->assertStringContainsString($t, $html);
        }
    }
    public function test_resume_renders_sections(): void
    {
        $html = $this->get('/resume')->assertOk()->getContent();
        foreach (['Education','Experience','Skills'] as $t) {
            $this->assertStringContainsString($t, $html);
        }
    }
```

- [ ] **Step 2: Run to verify it fails**

Run: `cd backend && php artisan test --filter=PageTest`
Expected: FAIL.

- [ ] **Step 3: Build Home**

Rebuild `home.blade.php` extending the layout: hero (name placeholder, positioning line "資安研究 × 機器學習 × 軟體開發", subtle entrance fade), three `<x-card>` nav cards linking to resume/collection/vocabulary, social links (GitHub/Email/LinkedIn placeholders). Reuse tokens/components; no animated background.

- [ ] **Step 4: Build Résumé**

Rebuild `resume.blade.php`: header with placeholder contact; timeline for Education & Experience; project `<x-card>`s; skill `<x-tag>`s color-coded by group (security/ML/dev); certifications; "Download CV" button (links to a `/cv.pdf` placeholder or triggers `window.print()`); an `@media print` stylesheet block for a clean printable document.

- [ ] **Step 5: Run to verify it passes**

Run: `cd backend && php artisan test --filter=PageTest`
Expected: PASS. Build assets (`npm run build`) and eyeball both pages in light + dark.

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "feat: home and resume pages with placeholder content and print css

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Phase 7 — Cleanup, docs, final verification

### Task 13: Remove the REST API surface and stale layers

**Files:**
- Delete: `backend/routes/api.php`, `backend/app/Http/Controllers/Api/*`, `backend/tests/Feature/VocabularyTest.php`, old `backend/tests/Feature/CollectionTest.php`, `frontend/` (whole tree), root `index.html`/`resume.html`/`collection.html`/`vocabulary.html`/`component-demo.html`
- Modify: `backend/bootstrap/app.php` (drop `api:` routing + api-only JSON exception), `backend/app/Models/User.php` (remove `HasApiTokens`), `backend/composer.json` (remove `laravel/sanctum` if unreferenced), `backend/config/` (remove `sanctum.php` if unused)

**Interfaces:**
- Produces: a single web app with no REST surface. All previously-green feature/unit tests still pass.

- [ ] **Step 1: Confirm nothing references the API**

Run: `cd backend && grep -rn "createToken\|HasApiTokens\|Sanctum\|/api/" app routes tests config | grep -v vendor`
Expected: only hits are the files slated for deletion/edit. If a Livewire/test references `/api/`, fix it first.

- [ ] **Step 2: Delete API + stale layers**

```bash
cd "D:/Personal web"
git rm backend/routes/api.php
git rm -r backend/app/Http/Controllers/Api
git rm backend/tests/Feature/VocabularyTest.php backend/tests/Feature/CollectionTest.php
git rm -r frontend
git rm index.html resume.html collection.html vocabulary.html component-demo.html
```

- [ ] **Step 3: Update bootstrap + User model + Sanctum**

Edit `backend/bootstrap/app.php`: remove the `api:` argument from `withRouting` and remove the `withExceptions` api-json rule (keep `web:`, `commands:`, `health:`, and the `admin` alias — the alias may now be unused; leave it only if a route uses it, else remove). Remove `use Laravel\Sanctum\HasApiTokens;` and the trait usage from `User.php`. If `grep` shows no other Sanctum use, run `composer remove laravel/sanctum` and delete `config/sanctum.php` + the sanctum migration `2026_06_02_182232_create_personal_access_tokens_table.php` (only if the app never issues tokens).

- [ ] **Step 4: Run the full suite**

Run: `cd backend && php artisan migrate:fresh --seed && php artisan test`
Expected: ALL green (Unit + Feature). No references to deleted classes.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "refactor: remove REST API, Nuxt frontend, and stale HTML prototypes

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

### Task 14: Rewrite docs for the PHP-only workflow

**Files:**
- Modify: `README.md`, `啟動紀錄.md`, `backend/.env.example`

**Interfaces:**
- Produces: accurate local-run instructions (SQLite, no MySQL/Node-server) and a portability note for future public hosting.

- [ ] **Step 1: Rewrite `.env.example`**

Set `DB_CONNECTION=sqlite`; comment MySQL fields with a note "uncomment for MySQL/production"; remove `FRONTEND_URL`; keep `DICTIONARY_API_URL`/`MYMEMORY_API_URL`; `ADMIN_EMAIL=admin@example.com`, `ADMIN_PASSWORD=password123`.

- [ ] **Step 2: Rewrite `README.md`**

Document: prerequisites (PHP 8.3, Composer, Node 20); setup (`cd backend && composer install && npm install && cp .env.example .env && php artisan key:generate && touch database/database.sqlite && php artisan migrate --seed`); run (`composer run dev` or `php artisan serve` + `npm run dev`); admin login (`admin@example.com` / `password123`); test (`php artisan test`); and a "Deploy publicly later" section (point `.env` at MySQL, `npm run build`, host on any PHP 8.3+ platform).

- [ ] **Step 3: Rewrite `啟動紀錄.md`**

Replace the obsolete Node/Express/MySQL log with the Laravel + Livewire + SQLite startup steps and a short troubleshooting table (e.g., "vite manifest not found → run `npm run build`", "no application key → `php artisan key:generate`").

- [ ] **Step 4: Verify a clean bootstrap from the docs**

Run the README steps verbatim in a scratch clone (or `migrate:fresh --seed` + `npm run build` + `php artisan serve`) and confirm the site loads and admin can log in.

- [ ] **Step 5: Commit**

```bash
git add README.md 啟動紀錄.md backend/.env.example
git commit -m "docs: rewrite setup for PHP-only local (sqlite) workflow

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

### Task 15: Final full verification & branch wrap-up

**Files:** none (verification only)

- [ ] **Step 1: Full green suite**

Run: `cd backend && php artisan test`
Expected: all Unit + Feature pass. Record counts.

- [ ] **Step 2: Production asset build**

Run: `cd backend && npm run build`
Expected: builds with no errors; `public/build/manifest.json` present.

- [ ] **Step 3: End-to-end manual smoke (evidence)**

`php artisan migrate:fresh --seed && php artisan serve`, then verify in the browser: Home, Résumé (and print preview), Collection (filter/search/tabs; as admin: create/edit/delete + toast; as guest: read-only), Vocabulary (browse filter combine; add with lookup + duplicate warning; flashcard flip + rating updates schedule), theme toggle persists, no console errors, light + dark both AA-legible, mobile width usable.

- [ ] **Step 4: Push the branch and open a PR (only if the user asks)**

Do not push automatically. When the user requests it: `git push -u origin feat/php-livewire-rebuild` and open a PR summarizing the consolidation.

---

## Self-Review (author checklist — completed)

- **Spec coverage:** architecture (Tasks 1–3), design system/tokens/fonts/theme/a11y (Task 4), auth + roles (Tasks 3, 5), services incl. both bug fixes (Tasks 6–7), Collection (Tasks 8–9), Vocabulary + lookup + duplicate detection (Task 10), Flashcard/SM-2 (Task 11), Home + Résumé + print (Task 12), deletions incl. `api.php` (Task 13), docs/SQLite/portability (Task 14), final verification + adversary gate (throughout, Task 15). The `where/orWhere` filter bug is covered by `VocabularyIndexTest::test_search_and_familiarity_filters_combine_correctly`; the SM-2 overdue edge case by `SpacedRepetitionTest::test_overdue_word_does_not_produce_negative_interval`.
- **Placeholder scan:** no "TBD/handle edge cases/similar to Task N"; every code step shows real code; résumé "placeholder content" is an explicit product requirement, not a plan gap.
- **Type consistency:** service signatures (`SpacedRepetition::calculate → [Carbon,int]`, `VocabularyLookup::lookup → array`), event names (`open-collection-form`, `collection-saved`, `collection-deleted`, `vocabulary-saved`, `toast`), and prop names are used identically across producing and consuming tasks.
- **Known risk flagged:** Livewire 3 / Breeze compatibility with the very new Laravel 13.8 — Tasks 2–3 include explicit STOP-and-report and a hand-rolled-auth fallback.
