# Design Spec — PHP-First Rebuild (Laravel 13 + Livewire 3)

> Created: 2026-07-04
> Status: Approved, pending implementation plan
> Supersedes the frontend approach in `2026-06-03-laravel-nuxt3-migration-design.md`

---

## 1. Background & Goal

The repository is a **personal portal website** with four sections: Home, Résumé, an
Anime/Manga Collection tracker, and a paper-Vocabulary study tool. It currently contains
**three overlapping layers**:

1. **Root HTML prototypes** (`index.html`, `resume.html`, `collection.html`,
   `vocabulary.html`, `component-demo.html`) — the original vanilla build. **Broken
   standalone**: they reference `css/*.css` and `js/*.js` that do not exist at the repo root.
2. **`backend/`** — a Laravel 13 (PHP 8.3) **API-only** app (Sanctum token auth).
3. **`frontend/`** — a Nuxt3 SSG app (Vue 3 + TypeScript + Pinia).

**Goal:** Collapse these into **one PHP-first full-stack Laravel application** using
**Blade + Livewire 3**, with a sleek, minimal-futuristic UI. PHP becomes the primary tool
for the entire stack. The app must **always run cleanly locally**; it is also built to be
**portable to any PHP 8.3+ host** so it can be made publicly accessible later (GitHub Pages
cannot host it because Livewire requires a live PHP server).

### Decisions (locked)

| Topic | Decision |
|---|---|
| Architecture | Full PHP/Laravel, **Blade + Livewire 3 + Alpine**. Retire Nuxt3 and root HTML. |
| Strategy | **Evolve the existing `backend/` app in place** (reuse models/migrations/logic). Keep the `backend/` folder. |
| Scope | **All four sections** rebuilt in the new UI. |
| Personal content | **Realistic placeholders**, swapped for real content later. |
| Aesthetic | **Sleek minimal-futuristic** — near-monochrome, one accent, thin rules, refined type. |
| Motion | **Subtle only**; full `prefers-reduced-motion` support. No animated backgrounds. |
| Theme | **Light-first**, with a polished dark toggle. |
| Accent | **Cyan / teal** (`#06B6D4 → #14B8A6`). |
| Auth | **Session auth via Breeze (Livewire stack)**, **multi-user-ready** with `admin`/`user` roles. |
| Vocab lookup | **Keep auto-lookup** via Free Dictionary + MyMemory (both APIs), with caching + fallback. |
| Local DB | **SQLite** (zero-config). MySQL-compatible for production. |
| Deployment | Local guaranteed now; portable to any PHP 8.3+ host for future public access. |
| REST API | **Dropped** as a public surface. Read/write happens through Livewire. Lookup remains a server-side service. |

---

## 2. Technology Stack

| Layer | Technology |
|---|---|
| Language / framework | PHP 8.3, Laravel 13.8 (existing) |
| Interactivity | Livewire 3 + Alpine.js (Alpine ships with Livewire 3) |
| Styling | Tailwind CSS + design tokens (CSS custom properties), built via Vite (already configured) |
| Auth | Laravel Breeze — **Livewire stack**, session-based |
| Authorization | `role` column on `users` + Policies/Gates (`admin` vs `user`) |
| Database (local) | SQLite (`database/database.sqlite`) |
| Database (portable) | MySQL 8.x (same migrations) |
| External APIs | Free Dictionary API, MyMemory Translation API (server-side proxy) |
| Testing | PHPUnit 12 (existing) + Livewire test helpers; factories reused |
| Fonts | Space Grotesk (display), Inter (body), JetBrains Mono (data/labels) |

---

## 3. Target Structure (single app under `backend/`)

```
backend/
├── app/
│   ├── Livewire/
│   │   ├── CollectionIndex.php        # tabs, stats, filters, search, sort, view toggle
│   │   ├── CollectionForm.php         # add/edit modal (admin)
│   │   ├── VocabularyIndex.php        # browse + filter
│   │   ├── VocabularyForm.php         # add/edit with auto-lookup (admin)
│   │   └── Flashcard.php              # SM-2 review session
│   ├── Models/                        # REUSED: User, CollectionWork, CollectionCategory, StudyVocabulary
│   ├── Services/
│   │   ├── VocabularyLookup.php       # lifted from VocabularyController::lookup + caching/fallback
│   │   └── SpacedRepetition.php       # lifted from calculateNextReview (bug-fixed)
│   ├── Policies/
│   │   ├── CollectionWorkPolicy.php
│   │   └── StudyVocabularyPolicy.php
│   └── Http/
│       ├── Requests/                  # CollectionWorkRequest, VocabularyRequest
│       └── Middleware/                # role middleware (evolve existing AdminOnly)
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php      # navbar, theme toggle, footer, meta/OG
│   │   ├── home.blade.php
│   │   ├── resume.blade.php
│   │   └── livewire/                  # component templates
│   ├── css/app.css                   # Tailwind + tokens
│   └── js/app.js                     # Alpine bootstrap, theme persistence
├── routes/web.php                    # primary routes (replaces api.php)
├── database/
│   ├── migrations/                   # REUSED + roles adjustment
│   ├── factories/                    # REUSED
│   └── seeders/                      # AdminSeeder (role=admin), CategorySeeder
└── tests/                            # Feature (Livewire) + Unit (services)
```

**Removed:** `frontend/` (Nuxt3); root `*.html` prototypes; `backend/routes/api.php` and
`app/Http/Controllers/Api/*` (logic migrated to Services/Livewire first); Sanctum wiring if
unused after auth switch. `啟動紀錄.md` rewritten for the PHP-only setup.

---

## 4. Data Model (reused, unchanged schema)

Existing tables are kept as designed in `架構設計.md`:

- `collection_works` — anime/manga unified table (type, title, status, rating, favorite,
  release_year, release_season, media_type, source_type, episodes/volumes, author, studio, note).
- `collection_categories` — genre/source/media-type dictionary.
- `collection_work_categories` — many-to-many pivot.
- `study_vocabulary` — word, meaning, part_of_speech, phonetic, audio_url, example, example_zh,
  source, note, familiarity, review_count, correct_count, next_review_at, last_reviewed_at, auto_filled.
- `users` — with `role ENUM('admin','user') DEFAULT 'user'` (admin seeded).

**Relationships:** `CollectionWork belongsToMany CollectionCategory` (eager-loaded to avoid N+1).

---

## 5. Authentication & Authorization

- **Breeze (Livewire stack)** provides login, session handling, password reset scaffolding.
- **Public registration disabled by default** (single seeded admin). Structure supports enabling
  it later for multi-user/public use.
- `users.role` drives authorization. **Guests: read-only.** **Admin: full CRUD.**
- Writes protected by **Policies** (`create/update/delete` gated to `role === 'admin'`), enforced
  in Livewire components (`$this->authorize(...)`) — not just hidden in the UI.
- Seeder creates one admin; credentials configured via `.env` / prompt (an open item to confirm at seed time).

---

## 6. Section Specifications

### 6.1 Home (`home.blade.php`, static)
Hero (name, positioning line, avatar), 2–3 line intro, three nav cards (Résumé / Collection /
Vocabulary), social links (GitHub, Email, LinkedIn), footer. Theme toggle in navbar.

### 6.2 Résumé (`resume.blade.php`, static)
Header with contact info; **timeline** education & experience; **project cards**; color-coded
**skill tags** (e.g., security / ML / dev); certifications; **Download CV** button.
Print-optimized CSS so "print to PDF" yields a clean document. Placeholder content throughout.

### 6.3 Collection (`Livewire/CollectionIndex` + `CollectionForm`)
- **Header:** type tabs (All / Anime / Manga) + live stats (total, completed, watching, favorites).
- **Toolbar:** add (admin), debounced search, filters, sort, card/table view toggle.
- **Filters:** status, release year range, season (winter/spring/summer/autumn), genre categories
  (multi-select with **AND/OR** toggle), rating range, favorite-only.
- **List:** cards show cover, title, year + season tag, category tags, star rating, favorite icon.
- **Add/Edit modal:** fields switch by type (anime vs manga); server-side validation via FormRequest.
- **States:** empty-state CTA, loading skeletons, delete confirmation, success toasts.

### 6.4 Vocabulary (`Livewire/VocabularyIndex` + `VocabularyForm` + `Flashcard`)
- **Header:** stats (total, added this week, pending review, average familiarity).
- **Modes:** Browse / Add / Flashcard.
- **Browse:** search + familiarity filter + list (fixes the current `where/orWhere` grouping bug).
- **Add (admin):** type English word → **debounced auto-lookup** → pre-fills meaning (zh),
  part_of_speech, phonetic, audio_url, example. All fields editable. **Duplicate detection**
  (existing word → warn: overwrite / cancel). `auto_filled` flag recorded.
- **Flashcard:** front = word + pronounce button; flip (3D, subtle) → meaning + example; self-rate
  **forgot / vague / remembered / mastered** → updates familiarity + `next_review_at` via SM-2;
  shows today's progress. Uses `SpacedRepetition` service.

---

## 7. Services (logic lifted from controllers)

### 7.1 `VocabularyLookup`
Server-side proxy: Free Dictionary (part_of_speech, phonetic, audio_url, example) + MyMemory
(zh meaning). Adds **response caching** (keyed by word, TTL) to avoid repeat external calls,
**per-API graceful fallback** (one failing does not fail the whole lookup), and timeouts.
Returns a normalized array for form pre-fill.

### 7.2 `SpacedRepetition`
Simplified SM-2 / Leitner. **Bug fix over current code:** compute the current interval robustly
even when `next_review_at` is in the past or `last_reviewed_at` is null; never produce negative
or zero intervals. Rules: new → +1 day; `forgot` → reset +1 day, familiarity −1 (floor 0);
`vague` → keep interval, familiarity unchanged; `remembered` → interval ×2, familiarity +1
(cap 5); `mastered` → interval ×2.5, familiarity +1 (cap 5). Fully unit-tested at boundaries.

---

## 8. Design System

- **Tokens** (CSS custom properties + Tailwind theme):
  - Light: bg `#F8FAFC`, surface `#FFFFFF`, ink `#0A0A0A`, hairline `slate-200`.
  - Dark: bg `slate-950`, surface `slate-900`, ink `slate-100`, hairline `slate-800`.
  - Accent: cyan→teal `#06B6D4 → #14B8A6`; accessible solids `cyan-600` (light) / `cyan-400` (dark)
    for text/controls to hold **WCAG AA** contrast.
- **Type:** Space Grotesk (headings), Inter (body), JetBrains Mono (data/labels/stats).
- **Motion:** 150–220ms fades, small hover lifts, focus rings, one 3D flashcard flip. Honors
  `prefers-reduced-motion`. No animated/particle backgrounds.
- **Shared components:** card, button, input, tag, modal, toast, skeleton, empty-state — all
  tokenized so every page reads as one system.
- **Accessibility:** keyboard navigable, visible focus, ARIA on interactive controls, AA contrast.
- **Meta/SEO:** per-page `<title>`, Open Graph tags, favicon.

---

## 9. Routing (`routes/web.php`)

| Method | Path | View / Component | Access |
|---|---|---|---|
| GET | `/` | `home` | public |
| GET | `/resume` | `resume` | public |
| GET | `/collection` | `CollectionIndex` | public (read) |
| GET | `/vocabulary` | `VocabularyIndex` | public (read) |
| — | `/login`, `/logout` | Breeze | — |
| (Livewire actions) | create/update/delete, review, lookup | components + policies | admin for writes |

Rate limiting retained on the lookup action (external-API abuse protection).

---

## 10. Testing Strategy & Adversarial Agent Workflow

Implementation proceeds as a **dynamic build-and-adversary loop**, one vertical slice at a time
(e.g., design system → auth → Collection → Vocabulary → Flashcard → Home/Résumé → docs):

1. **Builder** implements the slice **TDD-first** (test → code → refactor).
2. **Adversary agents** attack in parallel:
   - **code-review** (correctness, simplification, reuse),
   - **security-review** (authorization, input handling, the lookup proxy, session),
   - **edge-case hunter** (empty states, huge/CJK/unicode input, concurrent edits, SM-2 boundaries,
     external-API failure/timeout, duplicate words, invalid filters).
3. Findings loop back to the builder. A slice is **done only when tests pass AND adversary findings
   are resolved**, verified by actually running the app and the test suite (evidence, not assertion).
4. **Gate** at each slice before proceeding.

Test types: Livewire feature tests (rendering, filtering, CRUD authorization, flashcard flow),
unit tests (`SpacedRepetition`, `VocabularyLookup` with HTTP faked), policy tests.

---

## 11. Local Run & Portability

- **Local (guaranteed):** SQLite; `composer install`, `npm install && npm run build` (or `dev`),
  `php artisan migrate --seed`, `php artisan serve` → `http://localhost:8000`. No MySQL required.
- **Portable/public (future):** point `.env` at MySQL; same migrations; deploy to any PHP 8.3+ host
  (Railway / Render / Fly.io / VPS / Laravel Cloud). Documented but not executed now.
- `.env.example` and a rewritten `啟動紀錄.md` / README describe the PHP-only workflow accurately.

---

## 12. Out of Scope (YAGNI for now)

Public multi-user registration flow, external deployment execution, AniList auto-import, analytics,
RSS, i18n, blog. Structure leaves room for them but they are not built in this pass.

---

## 13. Resolved Items

- [x] **Admin seed credentials:** `admin@example.com` / `password123` (change after first login).
- [x] **`backend/routes/api.php`:** delete it (plus `app/Http/Controllers/Api/*` after logic migration).
- [x] **Résumé content:** clearly-marked realistic placeholders (`[Your Name]`, sample history), replaced later.
