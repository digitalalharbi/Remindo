# Remindo — API Reference

Base URL: `https://api.remindo.me/api` (dev: `http://localhost:8000/api`).
All responses use the unified envelope: `{ "data", "message"?, "meta"? }` on
success and `{ "message", "errors"? }` on error. Auth is cookie-based (Sanctum);
fetch `/sanctum/csrf-cookie` before mutations. ~108 routes total.

## Public
- `GET /health`
- `GET /plans` · `GET /faqs` · `POST /contact`
- `POST /webhooks/payments/{provider}` — idempotent, signature-verified

## Auth
- `POST /auth/register` · `POST /auth/login` · `POST /auth/logout` · `GET /auth/me`
- `POST /auth/forgot-password` · `POST /auth/reset-password`
- `GET /auth/email/verify/{id}/{hash}` · `POST /auth/email/verification-notification`
- `POST /auth/two-factor-challenge`
- 2FA: `POST /auth/two-factor/enable|confirm`, `DELETE /auth/two-factor`, `GET /auth/two-factor/recovery-codes`
- Sessions: `GET /auth/sessions`, `DELETE /auth/sessions/{id}`, `DELETE /auth/sessions/others`
- OAuth: `GET /auth/oauth/status`, `GET /auth/oauth/{provider}/redirect|callback`

## Reminders & taxonomy
- `GET|POST /reminders`, `GET|PUT|DELETE /reminders/{id}`
- `POST /reminders/{id}/complete|renew|snooze|archive`
- `GET|POST /categories`, `DELETE /categories/{id}` · `GET|POST /tags`, `DELETE /tags/{id}`
- `GET /dashboard`

## Documents & AI
- `GET|POST /documents`, `DELETE /documents/{id}`, `POST /documents/{id}/replace`
- `POST /documents/{id}/extract` (review-before-save) · `POST /ai/parse`
- `GET /documents/{id}/download` → signed URL · `GET /documents/{id}/file` (signed)

## Calendar
- `GET /calendar/feed.ics` · `GET /calendar/reminders/{id}.ics`
- `GET /calendar/connections` · `POST|DELETE /calendar/connect/{provider}` · `POST /calendar/sync/{id}`

## Channels
- `GET|PATCH /channels/preferences` · `GET /channels/health`
- `GET /channels/credits` · `POST /channels/credits/buy`
- `GET|POST /channels/webhooks`, `DELETE /channels/webhooks/{id}`, `GET /channels/webhooks/{id}/deliveries`
- `POST /channels/push/subscribe`

## Billing
- `GET /subscription` · `POST /subscription` (plan_key, interval, coupon_code?) · `DELETE /subscription`

## Profile & team
- `PATCH /profile` · `GET /team` · `GET /notifications`, `POST /notifications/{id}/read`, `POST /notifications/read-all`

## Super Admin (`is_super_admin` only)
- `GET /admin/stats|audit-logs|invoices`
- Users: `GET /admin/users`, `GET /admin/users/{id}`, `POST /admin/users/{id}/suspend|reactivate`
- Orgs: `GET /admin/organizations`, `.../{id}`, `.../{id}/suspend|reactivate`
- Plans: `GET|POST /admin/plans`, `PATCH /admin/plans/{id}`, `POST /admin/plans/{id}/toggle`, `POST /admin/plans/reorder`, `DELETE /admin/plans/{id}`
- Coupons: `GET|POST /admin/coupons`, `PATCH|DELETE /admin/coupons/{id}`
- Content: `GET|POST /admin/flags`, `GET /admin/languages`, `POST /admin/languages/{id}/toggle`, FAQ CRUD, `GET|POST /admin/settings`
- Credit packs: `GET|POST /admin/credit-packs`, `DELETE /admin/credit-packs/{id}`
- `POST /admin/invoices/{id}/refund`

## Webhook signatures
Outgoing: `X-Remindo-Signature: sha256=<hmac>` over the raw body using the
endpoint secret. Inbound payment webhooks: verify `X-Signature` against your
gateway webhook secret.
