# Remindo — Testing

## Backend (PHPUnit, PostgreSQL)

```bash
cd backend && php artisan test
```

67 feature/unit tests covering: authentication, reminders lifecycle, notification
scheduling, tenant isolation, AI extraction, subscriptions, billing lifecycle
(trials/proration/dunning/idempotent webhooks/refunds), admin management
(plans/coupons/suspension/flags/languages), public site, 2FA, OAuth, calendar,
notification channels (credits/quiet-hours/webhooks), and document ownership.

Tests run against a real PostgreSQL database (`remindo_test`) so PostgreSQL-only
behavior (`ilike`, JSON, UUID) is exercised exactly as in production.

## Frontend unit (Vitest)

```bash
cd frontend && npm run test
```

## Frontend E2E (Playwright)

```bash
cd frontend && npm run test:e2e
```

Requires the backend (`:8000`) and frontend (`:3000`) running with a seeded
database. Covers: marketing render + RTL (Arabic) + pricing, register → dashboard
→ create reminder, unauthenticated redirect, and billing. The pre-installed
Chromium is used via `executablePath` in `playwright.config.ts` (override with
`PLAYWRIGHT_CHROMIUM_PATH`).

## Static checks

```bash
cd frontend && npm run lint && npm run typecheck && npm run build
cd backend && ./vendor/bin/pint --test
```

## CI

`.github/workflows/ci.yml` runs the backend suite (Postgres + Redis services)
and the frontend lint/typecheck/unit/build on every push and PR.
