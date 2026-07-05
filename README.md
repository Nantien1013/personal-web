# Personal Web

A personal portal website — home, résumé, an anime/manga **collection** tracker, and a
paper-vocabulary **study** tool with spaced-repetition flashcards. Built as a single
PHP-first full-stack app: **Laravel 13 + Livewire 3 + Tailwind v4**, session auth, SQLite
for zero-config local dev (MySQL-ready for production).

The whole application lives in [`backend/`](backend/).

---

## Requirements

- PHP 8.3+
- Composer 2.x
- Node.js 20+ and npm (for building CSS/JS assets)

## Quick start (local, SQLite — no database server needed)

```bash
cd backend
composer install
npm install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
npm run build
php artisan serve
```

Open <http://localhost:8000>.

During development you can run the asset watcher instead of a one-off build:

```bash
# terminal 1
php artisan serve
# terminal 2
npm run dev
```

## Admin login

Write access (add/edit/delete collection items and vocabulary) requires logging in.
`AdminSeeder` creates one admin from `.env`:

- **Email:** `admin@example.com`
- **Password:** `password123`

Log in at <http://localhost:8000/login>. Change these via `ADMIN_EMAIL` / `ADMIN_PASSWORD`
before seeding (public registration is disabled). Guests can browse everything read-only.

## Tests

```bash
cd backend
php artisan test
```

---

## Sections

| Page | Route | What it does |
|---|---|---|
| Home | `/` | Hero, intro, navigation cards, social links |
| Résumé | `/resume` | Education/experience timeline, projects, skills, print-to-PDF |
| Collection | `/collection` | Anime/manga tracker — tabs, stats, search, filters, ratings (admin CRUD) |
| Vocabulary | `/vocabulary` | Browse/add words with auto-lookup + spaced-repetition flashcards (admin CRUD) |

**Vocabulary auto-lookup** proxies two free APIs server-side (Free Dictionary + MyMemory
translation) with caching and graceful fallback — configured via `DICTIONARY_API_URL` /
`MYMEMORY_API_URL`.

## Tech

Laravel 13 · Livewire 3 · Alpine.js · Tailwind CSS v4 (design tokens, light/dark) · Laravel
Breeze (session auth) · SQLite (local) / MySQL (production) · PHPUnit + Livewire test helpers.

## Deploy publicly (later)

The app runs on any PHP 8.3+ host. To go live:

1. Point `.env` at MySQL (`DB_CONNECTION=mysql` + the `DB_*` vars), set `APP_ENV=production`,
   `APP_DEBUG=false`, and a fresh `APP_KEY`.
2. `composer install --no-dev --optimize-autoloader && npm ci && npm run build`
3. `php artisan migrate --seed --force`
4. Serve `backend/public` via your platform (Railway, Render, Fly.io, a VPS, Laravel Cloud, …).

GitHub Pages cannot host it — Livewire needs a live PHP server.
