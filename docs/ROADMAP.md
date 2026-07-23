# Remindo — Build Roadmap & Status

This is a large production SaaS. It is being built in coherent, shippable milestones. This file
is the honest source of truth for **what actually works** vs. what is scaffolded or planned.
No integration is claimed as production-ready before its real credentials exist.

Legend: ✅ done · 🟡 partial / scaffolded · ⬜ planned

## Milestone 1 — Foundation
- ✅ Monorepo structure, `.gitignore`, `.env.example`, `docker-compose.yml`, README
- ✅ Docs: architecture, design system, ADRs, roadmap
- ✅ GitHub Actions CI (backend + frontend)

## Milestone 2 — Backend core
- 🟡 Laravel 12 API scaffold, unified JSON responses, config
- ⬜ Auth: register, login, email verification, password reset (Sanctum, cookie)
- ⬜ Organizations / tenancy + policies + isolation
- ⬜ Reminders: CRUD, categories, tags, recurrence, snooze, complete, renew
- ⬜ Notifications model + scheduler (due dispatch) + queued channels
- ⬜ Feature tests

## Milestone 3 — Frontend core
- ⬜ Design tokens + Tailwind theme + light/dark
- ⬜ i18n (ar/en/es/tr) + RTL
- ⬜ Marketing homepage + pricing
- ⬜ Auth pages
- ⬜ App shell + dashboard + create-reminder flow
- ⬜ SEO (metadata, hreflang, sitemap, structured data)

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
