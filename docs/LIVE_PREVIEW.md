# Remindo — Live Preview

This document is the honest, reproducible recipe for the **public preview**: a
real, DB-connected instance of all four surfaces (marketing / dashboard / admin /
API) — not screenshots, not a static page. It also states plainly what is live,
what is sandboxed, and what still needs your credentials.

---

## Status (honest)

| Piece | State |
| ----- | ----- |
| Marketing, dashboard, admin, API code | ✅ built, tested, preview-ready |
| Preview mode (badge, demo button, mail log) | ✅ implemented + verified |
| Demo + admin accounts and rich seed data | ✅ implemented + verified |
| Full deploy path (migrate/caches/queue/scheduler/build/E2E) | ✅ verified in-container against a real Postgres + Redis |
| **Public, clickable URLs for the four surfaces** | ⛔ **blocked — needs an app host you own** (see below) |

### Why there are no live URLs in this file yet

The build environment is an outbound-only sandbox: it can reach the internet
through a proxy but **cannot accept public inbound traffic**, and it has **no
tool that hosts a PHP (Laravel) or Node SSR (Next.js) app at a public URL**.
Publishing the four real surfaces therefore requires an external host **you own**
— exactly the kind of "un-bypassable need (hosting account / DNS)" the brief said
to stop and document for. Everything that does **not** require that host is done,
committed, and verified, so the moment you connect a host the steps below take it
live without further code changes.

Do **not** treat any `localhost` URL as the live preview — those are in-container
verification only.

---

## What "live" looks like once a host is connected

Four services + a preview database:

| Surface | Suggested preview host | Serves |
| ------- | ---------------------- | ------ |
| Marketing | `app-preview.remindo.me` (or platform URL) | `/{locale}` |
| Dashboard | same host | `/{locale}/dashboard`, `/reminders`, … |
| Super Admin | same host | `/{locale}/admin` |
| API | `api-preview.remindo.me` (or platform URL) | `/api/*` |
| Preview DB | managed Postgres (separate from production) | — |

The frontend is one Next.js app that renders all three web surfaces via route
groups; you deploy it once. The API is the Laravel app. Both are cheap to host on
Railway / Render / Fly.io with a managed Postgres + Redis add-on.

---

## Redeploy recipe

### 0. Provision (once)
- A managed **Postgres** (the *Preview DB* — never the production DB).
- A managed **Redis** (queue + cache).
- One service for the **Laravel API**, one for the **Next.js** app.

### 1. Backend (Laravel API)

```bash
cd backend
cp .env.example .env
# fill DB_*, REDIS_*, and the preview/cross-domain block (see env table below)
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force            # seeds plans, platform data, admin + demo (demo is auto-skipped in production)
php artisan optimize                   # config + route + view cache
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Run these as **persistent services** (not one-off terminals):

```bash
php artisan queue:work redis --sleep=1 --tries=3 --backoff=60,300,900
php artisan schedule:work
```

Health check: `GET /api/health` → `{"data":{"status":"ok","preview":true}}`.

### 2. Frontend (Next.js)

```bash
cd frontend
cp .env.example .env.local
# set NEXT_PUBLIC_* (see env table)
npm ci
npm run build
npm run start        # or `node .next/standalone/server.js` for the standalone output
```

### 3. Smoke test the deployed URLs
- Marketing loads in ar/en/es/tr, RTL correct for Arabic.
- Login page shows the **Preview Mode** badge and **Try demo account** button.
- Demo login → dashboard shows the seeded reminders (overdue / upcoming / renewed).
- Admin login → `/admin` stats + **Mail log** populated.
- Cross-domain cookie auth: CSRF cookie set, login persists, logout clears it.

---

## Required environment variables

### Backend (`backend/.env`)

| Var | Preview value (example) | Purpose |
| --- | --- | --- |
| `APP_ENV` | `production` | disables debug, blocks demo seeder in prod DB |
| `APP_DEBUG` | `false` | no stack traces to clients |
| `APP_URL` | `https://api-preview.remindo.me` | API base |
| `FRONTEND_URL` | `https://app-preview.remindo.me` | SPA base |
| `DB_*` | (managed Postgres) | **separate Preview DB** |
| `REDIS_*` | (managed Redis) | queue + cache |
| `SESSION_DOMAIN` | `.remindo.me` | shared parent for cookie auth |
| `SANCTUM_STATEFUL_DOMAINS` | `app-preview.remindo.me` | SPA hosts |
| `CORS_ALLOWED_ORIGINS` | `https://app-preview.remindo.me` | credentialed CORS |
| `SESSION_SECURE_COOKIE` | `true` | HTTPS-only cookie |
| `SESSION_SAME_SITE` | `lax` (`none` if API+SPA are cross-site) | cross-domain cookies |
| `PREVIEW_MODE` | `true` | badge + demo button + `/preview` flag |
| `PREVIEW_CAPTURE_MAIL` | `true` | capture outgoing mail to Admin → Mail log |
| `MAIL_MAILER` | `log` | no real mail sent in preview |
| `DEMO_SEED_PASSWORD` | `DemoPass123!` | demo account password |
| `ADMIN_SEED_PASSWORD` | `AdminPass123!` | admin account password |

> If you deploy the preview with `APP_ENV=production` (recommended, for debug-off
> and stack-trace safety), run the demo seeder explicitly since it is skipped in
> production: `php artisan db:seed --class=DemoSeeder --force`. Never do this on
> the real production database.

### Frontend (`frontend/.env.local`)

| Var | Preview value | Purpose |
| --- | --- | --- |
| `NEXT_PUBLIC_API_URL` | `https://api-preview.remindo.me/api` | API base |
| `NEXT_PUBLIC_SITE_URL` | `https://app-preview.remindo.me` | canonical/SEO |
| `NEXT_PUBLIC_PREVIEW_MODE` | `true` | badge + demo button |
| `NEXT_PUBLIC_DEMO_EMAIL` | `demo@remindo.me` | demo button prefill |
| `NEXT_PUBLIC_DEMO_PASSWORD` | `DemoPass123!` | demo button prefill |

---

## Preview accounts

| Account | Email | Password | Access |
| ------- | ----- | -------- | ------ |
| Demo user | `demo@remindo.me` | `DemoPass123!` | Dashboard |
| Super admin | `admin@remindo.me` | `AdminPass123!` | Dashboard + `/admin` |

Passwords are env-driven; the values above are the documented preview defaults.

### Seeded demo data (bilingual, every lifecycle state)
Car insurance, supplier contract, commercial registration (CR), software
subscription, professional (engineering) license, employee (first-aid)
certificate, office lease, domain renewal, passport, and a **renewed** vehicle
registration — spanning **overdue**, **due-soon**, **upcoming**, and **renewed**.

### Resetting preview data (never touches production)
```bash
cd backend
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan db:seed --class=DemoSeeder --force   # only if APP_ENV=production
```
Point this at the **Preview DB only**. The demo seeder is idempotent (it no-ops
if reminders already exist) and is gated out of `production` in `DatabaseSeeder`.

---

## Preview mode behaviour

- **Preview Mode badge** — a dismissible amber pill on every surface when
  `NEXT_PUBLIC_PREVIEW_MODE=true`, signalling sandbox data + mocked integrations.
- **Try demo account** — one-click login on the login screen (preview only).
- **Online mailbox** — with `PREVIEW_CAPTURE_MAIL=true`, every outgoing email is
  captured to `mail_previews` and viewable at **Admin → Mail log** (list + full
  HTML body), so reviewers can see reminder/verification emails without a real
  inbox. `GET /api/preview` reports the preview flag + which integrations are
  sandboxed.

## What is live vs. sandboxed in preview

**Live (no external keys):** account creation, cross-domain cookie auth, 2FA,
sessions, reminders CRUD + lifecycle, the 30-second create flow, scheduling +
queued dispatch, in-app + (captured) email notifications, ICS export, the admin
console, and the marketing site.

**Sandbox / mock (until you supply keys):** payments (Moyasar/Tap/Stripe),
Google/Microsoft OAuth, Google/Outlook calendar sync, SMS/WhatsApp delivery, web
push (VAPID), and AI document extraction. All are labelled by the Preview Mode
badge and documented in `backend/.env.example`.

---

## Pre-deploy security checklist

- [ ] `APP_DEBUG=false`, `APP_ENV=production` — no stack traces to clients.
- [ ] No secrets committed; every credential set via the host's env (see secret scan in CI).
- [ ] HTTPS only; `SESSION_SECURE_COOKIE=true`.
- [ ] `CORS_ALLOWED_ORIGINS` limited to the preview hosts (no wildcard).
- [ ] Security headers present (`X-Frame-Options: DENY`, `X-Content-Type-Options`, etc.).
- [ ] Rate limiting on auth + contact endpoints (6/min) enabled.
- [ ] Tenant isolation + super-admin gate verified (cross-org → 404; non-admin → 403).
- [ ] Document uploads: MIME allow-list, size cap, short-lived signed URLs.
- [ ] **Preview DB is separate from production**; demo seeder never run on prod.
- [ ] `ADMIN_SEED_PASSWORD` set to a strong value in any real production deploy.
