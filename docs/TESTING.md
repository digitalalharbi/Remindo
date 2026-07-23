# Testing

Backend:

```bash
cd backend
php artisan test
```

The suite covers registration, login, logout, disabled accounts, protected routes, plan
limits, reminder creation, timezone conversion, ownership/IDOR, snooze, renewal, due-schedule
claiming, duplicate prevention, email delivery, in-app persistence, and retry recording.

Frontend:

```bash
cd frontend
npm run typecheck
npm run lint
npm test
```

The frontend tests currently verify SSR output and the interaction contract. Browser E2E is
not implemented, so the project does not claim complete end-to-end coverage.
