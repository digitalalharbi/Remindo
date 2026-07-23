# API

Base path: `/api/v1`. JSON requests must send `Accept: application/json`.

## Public

- `GET /health`
- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/forgot-password`
- `POST /auth/reset-password`

## Authenticated with Sanctum

- `GET /auth/me`
- `POST /auth/logout`
- `GET /auth/sessions`
- `DELETE /auth/sessions/{token}`
- `PUT /auth/password`
- email verification send and signed verification endpoints
- reminder CRUD plus archive, snooze, renew, and complete actions
- notification list and mark-all-read

Reminder creation accepts one to ten schedules. Each schedule contains `scheduled_at` and one
of `email`, `in_app`, `sms`, or `whatsapp`. User-local timestamps are converted to UTC.

SMS and WhatsApp are logged by the Mock provider unless a production provider is implemented.
