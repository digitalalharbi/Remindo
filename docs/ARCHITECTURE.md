# Remindo — Architecture

## Overview

Remindo is a two-app monorepo:

- **backend/** — Laravel 12, API-only. Owns data, business logic, auth, scheduling, billing,
  notifications, and the AI/document extraction layer.
- **frontend/** — Next.js (App Router), TypeScript. Serves the marketing site, the web app, and
  the localized public pages. Talks to the backend over a versioned JSON API.

```
Browser ──▶ Next.js (SSR/SSG + client)
                │  Axios + TanStack Query
                ▼
        Laravel API (api.remindo.me)
        ├─ Sanctum (HttpOnly cookie auth)
        ├─ PostgreSQL (UUID PKs, FKs, indexes)
        ├─ Redis (cache, queues, sessions)
        ├─ Horizon (queued notifications)
        └─ Scheduler (dispatch due reminders every minute)
```

## Backend design principles

- **Thin controllers.** Controllers validate (Form Requests) and delegate to **Services**.
  Business logic lives in `app/Services`, not controllers or models.
- **Unified JSON envelope.** All responses use `App\Support\ApiResponse` →
  `{ "data": ..., "message": ..., "meta": ... }` on success and
  `{ "message": ..., "errors": ... }` on failure.
- **UUID primary keys** everywhere, foreign keys and indexes on every relation.
- **Tenancy.** Every user belongs to one or more **organizations**. All tenant-scoped data
  (reminders, documents, categories) carries an `organization_id`, and a global scope +
  policies enforce isolation. A personal account is just an organization of one.
- **Policies** gate every resource; nothing is authorized by controller conditionals alone.
- **Replaceable adapters.** Payments, SMS, WhatsApp, AI, and calendar sync are behind
  interfaces (`app/Contracts`) with sandbox/mock implementations selected by config. Swapping a
  provider is a config + binding change, never a rewrite.

### Key modules

| Module         | Responsibility                                                            |
| -------------- | ------------------------------------------------------------------------- |
| Auth           | Register, login, email verification, password reset, 2FA, OAuth (Google/MS) |
| Organizations  | Teams, membership, roles, tenant isolation                                |
| Reminders      | CRUD, categories, tags, recurrence, snooze, complete, renew               |
| Notifications  | Multi-channel dispatch (email, in-app, web push, SMS, WhatsApp, webhooks) |
| Scheduling     | Compute due notifications; Scheduler enqueues them each minute            |
| Documents      | Upload, virus/MIME checks, signed URLs, AI extraction                     |
| Billing        | Plans, subscriptions, usage limits, credits (SMS/WhatsApp), invoices      |
| AI             | Provider-agnostic extraction + natural-language reminder parsing          |
| Admin          | Super-admin: users, plans, pricing, content, translations, flags          |

## Frontend design principles

- **App Router**, Server Components for public pages (SSR/SSG for SEO), Client Components for
  the interactive app.
- **i18n is data-driven.** Translation keys only — no hard-coded copy in components. Locale in
  the URL (`/ar`, `/en`, `/es`, `/tr`); `dir` is derived from the locale.
- **Design system first.** All styling flows from design tokens (CSS variables) →
  Tailwind theme → shadcn/ui primitives. Light + dark mode.
- **State.** Server state via TanStack Query; small UI state via Zustand; forms via React Hook
  Form + Zod.

## Scheduling model

A reminder has one or more **reminder offsets** (e.g. 30 days, 7 days, 1 day before expiry).
The Scheduler runs `reminders:dispatch-due` every minute, finds notification rows whose
`send_at <= now` and `status = pending`, and pushes a queued job per channel. Horizon processes
them. This keeps the hot path (creating reminders) instant and moves fan-out to the queue.

## Security

Sanctum cookie-based auth (HttpOnly, Secure, SameSite), CSRF for stateful requests, CORS locked
to Remindo domains, rate limiting, per-tenant policies, encrypted sensitive columns, signed
URLs for documents, MIME validation, webhook signatures, idempotency keys, and security
headers. Tokens are never stored in localStorage. See `docs/decisions/` for specifics.

## Why not…

- **No microservices / DDD-heavy layering** — a single well-organized Laravel app with Services
  is simpler to run and evolve for this product's size.
- **No admin template** — the UI is an original design system, not an off-the-shelf dashboard.
