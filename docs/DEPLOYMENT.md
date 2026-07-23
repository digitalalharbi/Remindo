# Remindo — Deployment

## Domains
| Surface | Domain |
| ------- | ------ |
| Marketing | `remindo.me` |
| App (dashboard + admin) | `app.remindo.me` (admin at `/admin`) |
| API | `api.remindo.me` |

## Services
- PostgreSQL 16, Redis 7, PHP 8.3+ (Laravel Horizon worker + scheduler), Node 20
  (Next.js).

## Environment
Copy and fill:
- `backend/.env` from `backend/.env.example`
- `frontend/.env.local` from `frontend/.env.example`

Set at minimum: `APP_KEY` (`php artisan key:generate`), `DB_*`, `REDIS_*`,
`SESSION_DOMAIN` (the shared parent domain, e.g. `.remindo.me`),
`SANCTUM_STATEFUL_DOMAINS`, `CORS_ALLOWED_ORIGINS`, `NEXT_PUBLIC_API_URL`,
`NEXT_PUBLIC_SITE_URL`.

## Runtime processes (backend)
```bash
php artisan migrate --force
php artisan horizon        # queued notifications, webhooks, calendar sync
php artisan schedule:work  # due reminders (every minute), grace-period downgrades
```

## Docker
```bash
cp .env.example .env
docker compose up -d --build
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate --seed
```
`docker compose config` validates the stack (postgres, redis, backend, worker,
scheduler, frontend).

## Enabling external integrations in production
Each integration ships disabled/sandbox and is documented in `backend/.env.example`:
- **Payments** — `PAYMENTS_PROVIDER` + gateway secret + `STRIPE_WEBHOOK_SECRET`.
  Point the gateway's webhook at `POST /api/webhooks/payments/{provider}`.
- **OAuth** — `GOOGLE_*` / `MICROSOFT_*` client id + secret, then enable the
  `oauth_google` / `oauth_microsoft` feature flags in Admin.
- **Calendar** — `CALENDAR_PROVIDER=google|outlook` (reuses the OAuth apps).
- **SMS / WhatsApp** — `SMS_PROVIDER` / `WHATSAPP_PROVIDER` + keys; delivery stays
  Mock until set. Users purchase credit packs; sending deducts credits.
- **Web Push** — generate a VAPID keypair into `VAPID_*`.

Until real credentials are supplied, these run through replaceable
sandbox/mock adapters and are **not** production-live.
