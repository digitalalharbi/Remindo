# Notifications

The scheduler dispatches `DispatchDueReminderSchedules` every minute. It atomically claims
due rows and queues `SendReminderNotification`. Stale queued rows are recoverable after ten
minutes. The send job has three attempts and backoffs of 60, 300, and 900 seconds.

Every attempt is stored in `notification_attempts`. A successful delivery creates one
`reminder_notifications` row, protected by the schedule's unique key. Re-running a completed
job is a no-op.

Channels:

- Email: implemented through Laravel Mail; SMTP/Mailpit is configured by environment.
- In-app: implemented and persisted.
- SMS: Mock only; it writes structured logs and records the attempt as Mock delivery.
- WhatsApp: Mock only; it writes structured logs and records the attempt as Mock delivery.
- Web Push: not implemented.

Run locally:

```bash
php artisan schedule:work
php artisan queue:work redis --tries=3 --backoff=60,300,900
```
