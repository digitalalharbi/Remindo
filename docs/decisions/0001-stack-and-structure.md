# ADR 0001 — Stack and repository structure

- Status: Accepted
- Date: 2026-07

## Context

Remindo needs a production SaaS: localized marketing site + web app + JSON API, billing,
notifications, and an AI document-extraction feature. The spec fixes much of the stack.

## Decision

- **Monorepo** with `backend/` (Laravel 12, API-only) and `frontend/` (Next.js App Router).
  One codebase, clear boundary at the HTTP API.
- **PostgreSQL** with **UUID** primary keys; **Redis** for cache/queue/session; **Horizon** for
  queues; **Scheduler** for due-reminder dispatch.
- **Sanctum** cookie-based auth (HttpOnly) — no tokens in localStorage.
- **Thin controllers + Services**, unified JSON response envelope, Form Requests, Policies.
- **Frontend:** TypeScript strict, Tailwind, shadcn/ui, TanStack Query, Zustand, RHF + Zod.
- **Adapters** for all external providers (payments, SMS, WhatsApp, AI, calendar, OAuth) with
  sandbox/mock defaults selected by config.

## Consequences

- Simple to run locally (`docker compose up`) and to reason about.
- External integrations run end-to-end in sandbox mode without real credentials; production
  requires supplying real keys, documented in `.env.example`.
- No microservices, no DDD-heavy layering, no admin template — deliberately, for this product's
  size and simplicity goals.
