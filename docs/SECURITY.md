# Security

## Implemented

- Sanctum bearer-token authentication and stateful middleware support
- password hashing, password reset, email verification notification, device token revocation
- per-email-and-IP authentication rate limiting
- user ownership policy on reminder read, update, delete, snooze, renew, and completion
- request validation and mass-assignment allowlists
- strict CORS allowlist from `CORS_ALLOWED_ORIGINS`
- CSP, anti-framing, MIME sniffing, referrer, and browser permission headers
- notification idempotency keys and atomic schedule claiming
- secrets excluded through `.gitignore`; example files contain placeholders only
- encrypted application sessions when the environment example is followed

## Not implemented

- two-factor authentication
- workspace roles and organization isolation
- attachment malware scanning
- payment-webhook signature verification
- Web Push subscription encryption

Production must use HTTPS, `APP_DEBUG=false`, secure cookies, unique secrets, a private
database/Redis network, and provider key rotation.
