# Remindo — Security

## Authentication & sessions
- **Sanctum cookie auth** (HttpOnly, Secure, SameSite) — tokens are never stored
  in localStorage.
- **CSRF** protection on all stateful requests; the SPA fetches the CSRF cookie
  before mutations.
- **Two-factor auth** (TOTP, RFC 6238) with encrypted secrets and single-use
  recovery codes; login is a two-step challenge when enabled.
- **Active-session management** — users can view and revoke individual or all
  other sessions; a new-login notification is sent on every completed sign-in.
- **OAuth** (Google/Microsoft) links to an existing email account instead of
  creating duplicates; disabled safely until credentials are configured.

## Multi-tenancy & authorization
- Every tenant-scoped model carries `organization_id` and a **global scope** that
  makes cross-tenant rows unreachable (cross-org access returns 404, not 403).
- **Policies** gate reminder access; **EnsureSuperAdmin** gates the admin API;
  **EnsureNotSuspended** blocks suspended users/organizations.
- Suspended users are logged out and their tokens revoked.

## Data protection
- **Encrypted at rest**: 2FA secrets/recovery codes, calendar provider tokens,
  webhook signing secrets (Laravel `encrypted` casts).
- **Documents**: private disk, MIME allow-list, 10 MB cap, short-lived signed
  download URLs, tenant-scoped ownership.
- **AI privacy**: documents are only processed on explicit request and are never
  used to train models (`config/ai.php`).

## Transport & headers
- **CORS** locked to Remindo domains (no wildcard), credentials enabled.
- **Security headers** on every response: `X-Content-Type-Options`,
  `X-Frame-Options: DENY`, `Referrer-Policy`, `Permissions-Policy`; server
  implementation headers stripped.
- **Rate limiting** on auth and contact endpoints.

## Webhooks & idempotency
- Outgoing webhooks are **HMAC-SHA256 signed** (`X-Remindo-Signature`) with
  retries and a delivery ledger.
- Inbound payment webhooks are **signature-verified and idempotent**
  (`processed_webhooks`) — a replayed event is a no-op, and subscriptions are
  only activated by a trusted webhook, never the checkout return page.

## Secrets
- No secrets, `.env`, or key files are committed. Every external credential is
  documented as a placeholder in `.env.example`.
