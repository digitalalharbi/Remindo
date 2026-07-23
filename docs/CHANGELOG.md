# Changelog

## 2026-07-23

- Added Sanctum registration, login, logout, password reset/change, verification delivery,
  device-token listing, and revocation.
- Enforced reminder ownership and active-plan limits.
- Added multi-schedule reminders with user-timezone to UTC conversion.
- Added scheduler dispatch, queued sending, retries, stale-claim recovery, idempotency,
  email, in-app, SMS Mock, and WhatsApp Mock delivery records.
- Added snooze, complete, archive, and renewal actions.
- Added a real API-backed authenticated frontend route at `/app`.
- Added security headers, strict CORS configuration, Mailpit, seed data, and expanded tests.
