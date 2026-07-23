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

## Milestone 4 — Documents & AI ✅
- ✅ Upload + drag/drop, MIME allow-list, size cap, signed-URL file streaming
- ✅ Provider-agnostic AI extraction (DocumentExtractor contract) with a
  replaceable MockDocumentExtractor; review-before-save enforced
- ✅ Natural-language reminder parsing (ar + en); usage limits + cost tracking +
  privacy controls (no training on customer data). 4 feature tests
- ⬜ Real provider adapters (OpenAI/Anthropic) — wired once keys exist
- ⬜ Virus scanning hook (currently a no-op marker)

## Milestone 5 — Billing 🟡
- ✅ Subscriptions + invoices, plan change (monthly/yearly), usage limits
- ✅ Payment gateway layer (PaymentGateway contract) + SandboxGateway;
  Moyasar/Tap/Stripe slots stubbed for real credentials
- ✅ Invoice issuance in org currency with market VAT; cancel → free. 5 tests
- ⬜ Coupons, proration, dunning, refunds, real gateway webhooks
- ⬜ SMS/WhatsApp credit packs + AI add-on purchases

## Milestone 6 — Channels & integrations
- ⬜ Email, in-app, web push, SMS, WhatsApp, webhooks
- ⬜ Google / Outlook calendar sync
- ⬜ OAuth login (Google, Microsoft)

## Milestone 7 — Interface separation & Admin 🟡
- ✅ Three separate experiences: marketing / dashboard / admin, each with its own
  layout (MarketingLayout, AuthLayout, DashboardLayout, AdminLayout). Route
  groups `(marketing)`, `(auth)`, `(dashboard)` + `admin/`.
- ✅ Real dashboard: sidebar + top bar (search, add, notifications, lang, theme,
  account), pages: overview, reminders, calendar, documents, team, reports,
  billing, settings.
- ✅ Super Admin (guarded `/admin`, super-admin only): overview stats, users,
  organizations, plans, invoices, audit logs. Admin API + 3 tests (28 total).
- ✅ Security headers; audit logs surfaced in admin.
- ⬜ Admin write actions (edit plans/pricing/flags, suspend accounts), content &
  translation management, 2FA, backups, broader E2E/a11y coverage.

Each milestone is committed and pushed; tests for a milestone pass before the next begins.

## Completion criteria (from the brief)

| # | Criterion | Status |
| - | --------- | ------ |
| 1 | Create an account | ✅ |
| 2 | Choose language & country | ✅ |
| 3 | Create a reminder in under 30 seconds | ✅ |
| 4 | Upload a document and extract the expiry date | ✅ (mock adapter) |
| 5 | Review the data before saving | ✅ |
| 6 | Receive an actual alert | ✅ (scheduler + queued channels; email/in-app live) |
| 7 | Snooze / complete / renew | ✅ |
| 8 | Subscribe & pay in your language and currency | ✅ (sandbox gateway) |
| 9 | Use fully on phone, tablet, desktop | ✅ (responsive, mobile-first) |
| 10 | Switch language with no missing text | ✅ (4 locales, key-parity enforced) |
| 11 | Light and Dark mode | ✅ |
| 12 | Pass all tests | ✅ (25 backend + frontend unit + Playwright E2E) |
| 13 | Run from the README with no missing steps | ✅ |
| 14 | Pushed to the specified GitHub repo | ✅ |

Live SMS/WhatsApp/web-push delivery, calendar sync, OAuth login, and the Super
Admin panel are staged (Milestones 6–7) and run through sandbox/mock adapters or
are not yet built — never claimed as production-ready before real credentials.

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
