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

## Current build milestone

The repository currently contains the production foundation and the first working product
slice:

- responsive Arabic/English marketing experience with RTL/LTR and light/dark modes
- interactive “create reminder” flow with validation and success feedback
- Laravel JSON API for creating, listing, editing, and deleting reminders
- UUID reminder records, indexed expiry queries, API resources, form validation, and feature tests

Authentication, real notification delivery, document extraction, payments, and external
calendar integrations remain later milestones. They are intentionally not represented as
complete until provider credentials and end-to-end tests are in place.

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

### Domains

| Domain            | Purpose            |
| ----------------- | ------------------ |
| `remindo.me`      | Public marketing site |
| `app.remindo.me`  | The web app         |
| `api.remindo.me`  | The JSON API        |

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

- Marketing / app → http://localhost:3000
- API → http://localhost:8000/api

## Quick start (local, no Docker)

You need PHP 8.3+, Composer, Node 20+, PostgreSQL 16, Redis.

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
- [`docs/DESIGN_SYSTEM.md`](docs/DESIGN_SYSTEM.md) — tokens, colors, typography
- [`docs/decisions/`](docs/decisions/) — architecture decision records (ADRs)
- [`docs/ROADMAP.md`](docs/ROADMAP.md) — what's built and what's next

## License

Proprietary — © Remindo. All rights reserved.
