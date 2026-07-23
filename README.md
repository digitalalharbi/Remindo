<div align="center">

# Remindo

**Never miss an expiration date.**
Track contracts, licenses, insurance, subscriptions, documents, and renewals in one simple place.

**تذكير بانتهاء الصلاحية · ذكّرني**
كل مواعيد الانتهاء والتجديد في مكان واحد.

[remindo.me](https://remindo.me) · [app.remindo.me](https://app.remindo.me) · [api.remindo.me](https://api.remindo.me)

</div>

---

## What is Remindo?

Remindo is a simple SaaS that reminds individuals and businesses **before** important things
expire or need renewal — contracts, licenses, insurance, subscriptions, rentals, warranties,
certificates, official documents, and periodic maintenance.

The whole product is built around one idea:

> Add an expiry date, choose when to be reminded, and let Remindo warn you before it's too late.

The core of any reminder is: **name + expiry date + reminder time + notification channel**.
A user should be able to create their first reminder in **under 30 seconds**.

Remindo is **not** a general calendar and **not** a complex company-management system.

## Monorepo layout

```
/
├── backend/          Laravel 12 API (API-only, Sanctum, PostgreSQL, Redis, Horizon)
├── frontend/         Next.js App Router + TypeScript + Tailwind + shadcn/ui
├── docs/             Architecture, decisions (ADRs), API notes, design system
├── infrastructure/   Docker assets, local data volumes (gitignored)
├── docker-compose.yml
├── .env.example
└── README.md
```

### Three separate experiences

Remindo is deliberately split into three distinct products that share only the
design system (colors, fonts, primitives) — never a layout:

| Surface | Domain (prod) | Local path | Layout |
| ------- | ------------- | ---------- | ------ |
| Marketing site | `remindo.me` | `/{locale}` (marketing group) | `MarketingLayout` |
| Auth | `app.remindo.me/login` | `/{locale}/login`, `/register` | `AuthLayout` |
| User dashboard | `app.remindo.me` | `/{locale}/dashboard`, `/reminders`, … | `DashboardLayout` |
| Super Admin | `admin.remindo.me` / `/admin` | `/{locale}/admin` | `AdminLayout` |
| API | `api.remindo.me` | `:8000/api` | — |

The marketing site never renders the dashboard; the dashboard never shows
marketing content. Access to `/dashboard` requires a session; `/admin` requires
a super-admin account.

### Languages

Arabic (RTL), English, Spanish, Turkish — served under `/ar`, `/en`, `/es`, `/tr`.
Adding a language is data-driven and does not require code changes.

---

## Quick start (Docker)

Requires Docker + Docker Compose.

```bash
git clone https://github.com/digitalalharbi/Remindo.git
cd Remindo

# 1. Root env (Postgres/Redis/ports)
cp .env.example .env

# 2. Backend env
cp backend/.env.example backend/.env

# 3. Frontend env
cp frontend/.env.example frontend/.env.local

# 4. Bring everything up
docker compose up -d --build

# 5. Generate app key + run migrations & seeders (first run only)
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate --seed
```

Then open:

- Marketing site → http://localhost:3000/en (or `/ar`, `/es`, `/tr`)
- User dashboard → http://localhost:3000/en/dashboard
- Super Admin → http://localhost:3000/en/admin
- API → http://localhost:8000/api

**Seeded accounts** (local/dev/preview — passwords are env-driven via
`DEMO_SEED_PASSWORD` / `ADMIN_SEED_PASSWORD`; defaults shown):

| Account | Email | Password | Access |
| ------- | ----- | -------- | ------ |
| Demo user | `demo@remindo.me` | `DemoPass123!` | Dashboard |
| Super admin | `admin@remindo.me` | `AdminPass123!` | Dashboard + `/admin` |

> **Public preview / live demo:** see [`docs/LIVE_PREVIEW.md`](docs/LIVE_PREVIEW.md)
> for the reproducible deploy recipe, required env vars, preview accounts, demo
> seed data, and the honest status of what runs live vs. sandbox. Preview mode
> (`PREVIEW_MODE=true`) adds a "Preview Mode" badge, a "Try demo account" button,
> and an online mailbox at **Admin → Mail log**.

## Quick start (local, no Docker)

You need PHP 8.4+, Composer, Node 20+, PostgreSQL 16, Redis.

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# point DB_* in .env at your local PostgreSQL, then:
php artisan migrate --seed
php artisan serve            # http://localhost:8000
php artisan horizon          # queue worker (separate terminal)
php artisan schedule:work    # due-reminder dispatch (separate terminal)
```

### Frontend

```bash
cd frontend
npm install
cp .env.example .env.local   # set NEXT_PUBLIC_API_URL=http://localhost:8000/api
npm run dev                  # http://localhost:3000
```

---

## Testing

```bash
# Backend (Pest / PHPUnit)
cd backend && php artisan test

# Frontend (unit)
cd frontend && npm run test

# Frontend (E2E — Playwright)
cd frontend && npm run test:e2e
```

## External integrations & secrets

Remindo ships with **replaceable adapters** for every external service (payments, SMS,
WhatsApp, AI extraction, calendar sync, OAuth). Where real credentials are not present, a
**sandbox / mock adapter** is used so the whole system runs end-to-end locally. Every required
key is documented in the relevant `.env.example`. No real secrets are committed.

To go to production you must supply real credentials for:
payment gateways (Moyasar/Tap, Stripe), SMS/WhatsApp providers, an AI provider, and OAuth apps
(Google, Microsoft). Until then those integrations run in sandbox mode and are **not** claimed
to be production-ready.

## Documentation

- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — system overview
- [`docs/API.md`](docs/API.md) — API reference (~108 routes)
- [`docs/SECURITY.md`](docs/SECURITY.md) — auth, tenancy, encryption, webhooks
- [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) — domains, services, env, enabling integrations
- [`docs/LIVE_PREVIEW.md`](docs/LIVE_PREVIEW.md) — public preview deploy recipe, env vars, demo accounts
- [`docs/TESTING.md`](docs/TESTING.md) — backend/unit/E2E and static checks
- [`docs/DESIGN_SYSTEM.md`](docs/DESIGN_SYSTEM.md) — tokens, colors, typography
- [`docs/decisions/`](docs/decisions/) — architecture decision records (ADRs)
- [`docs/ROADMAP.md`](docs/ROADMAP.md) — status + honest integration matrix
- [`docs/screenshots/`](docs/screenshots/) — marketing, dashboard, create-reminder, billing, admin

## Status

Three separate surfaces (marketing / dashboard / admin) share one design system.
**67 backend tests + 7 Playwright E2E + frontend unit all green**; lint,
typecheck, build, and Pint clean. Production-ready without external credentials:
email/in-app reminders, scheduling, ICS export, 2FA, sessions, signed webhooks,
the admin console, and the marketing site. Payments, OAuth, calendar sync,
SMS/WhatsApp, web push, and AI extraction ship as **replaceable sandbox/mock
adapters** and become live once their credentials are supplied — see
[`docs/ROADMAP.md`](docs/ROADMAP.md) for the full matrix.

## License

Proprietary — © Remindo. All rights reserved.
