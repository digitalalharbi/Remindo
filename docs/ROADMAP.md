# Remindo — Build Roadmap & Status

This is a large production SaaS. It is being built in coherent, shippable milestones. This file
is the honest source of truth for **what actually works** vs. what is scaffolded or planned.
No integration is claimed as production-ready before its real credentials exist.

Legend: ✅ done · 🟡 partial / scaffolded · ⬜ planned

## Milestone 1 — Foundation
- ✅ Monorepo structure, `.gitignore`, `.env.example`, `docker-compose.yml`, README
- ✅ Docs: architecture, design system, ADRs, roadmap
- ✅ GitHub Actions CI (backend + frontend)

## Milestone 2 — Backend core ✅
- ✅ Laravel 12 API-only, unified JSON envelope, JSON exception handlers
- ✅ Auth: register (+ personal org), login, logout, me, email verification,
  password reset (Sanctum cookie, throttled)
- ✅ Organizations / tenancy: global scope + Tenancy holder + ResolveTenant
  middleware + ReminderPolicy (isolation verified by tests)
- ✅ Reminders: CRUD, search/filters/sort/paginate, categories, tags,
  recurrence, snooze, complete, renew, archive
- ✅ Notifications model + scheduler (`reminders:dispatch-due`) + queued
  `SendReminderNotification` job + `ReminderDueNotification` (mail + database)
- ✅ SetLocale middleware + per-user preferredLocale (localized responses/emails)
- ✅ 16 feature tests pass (auth, reminders, scheduling, tenant isolation)

## Milestone 3 — Frontend core ✅
- ✅ Design tokens + Tailwind v4 theme + light/dark + RTL-aware
- ✅ i18n (ar/en/es/tr) + RTL via next-intl, full key-parity catalogs
- ✅ Marketing homepage (hero, 3 steps, live app preview, features, CTA) + pricing
- ✅ Auth pages (login, register) wired to the API (cookie auth, CSRF)
- ✅ App shell + guarded routes + dashboard + reminders list + 30s create flow
  (complete / renew / snooze / archive / delete)
- ✅ SEO: per-locale metadata + hreflang, sitemap, robots (app noindex), 404
- ✅ Vitest unit + Playwright E2E; frontend build green
- ⬜ Remaining public pages (features, security, blog, help, legal, use-cases)
- ⬜ Structured data (Organization/WebSite/SoftwareApplication/FAQ) — partial

## Milestone 4 — Documents & AI
- ⬜ Upload + drag/drop, MIME/virus checks, signed URLs
- ⬜ AI extraction (provider-agnostic, sandbox adapter), review-before-save
- ⬜ Natural-language reminder creation

## Milestone 5 — Billing
- ⬜ Plans, subscriptions, usage limits, credits (SMS/WhatsApp)
- ⬜ Payment gateway layer (Moyasar/Tap, Stripe) — sandbox adapters
- ⬜ Invoices, coupons, proration, dunning

## Milestone 6 — Channels & integrations
- ⬜ Email, in-app, web push, SMS, WhatsApp, webhooks
- ⬜ Google / Outlook calendar sync
- ⬜ OAuth login (Google, Microsoft)

## Milestone 7 — Admin & hardening
- ⬜ Super-admin (users, plans, content, translations, flags)
- ⬜ 2FA, audit logs, security headers, backups
- ⬜ E2E, accessibility, RTL/LTR, responsive tests

Each milestone is committed and pushed; tests for a milestone pass before the next begins.

## Current state (honest summary)

The **core product loop works end-to-end and is tested**: create an account →
choose language/country → create a reminder in under 30 seconds → a notification
schedule is generated → the scheduler dispatches due notifications → snooze /
complete / renew. The marketing site, design system, and full ar/en/es/tr + RTL
localization are in place, with light/dark mode. Everything runs from the README.

Milestones 4–7 (document upload + AI extraction, payments/billing, the remaining
notification channels, calendar/OAuth integrations, and the Super Admin panel)
are **not yet built**. Per the project's own rule — quality and simplicity over
breadth — these are staged as clearly-scoped next milestones rather than shipped
as non-functional placeholders. External integrations will use replaceable
sandbox/mock adapters until real credentials are supplied (documented in
`.env.example`), and none is claimed as production-ready before then.
