# Deployment

## Required services

- PHP 8.3+, PostgreSQL 16, Redis 7
- a persistent queue worker and one scheduler process
- SMTP provider or Mailpit for development
- Node.js 22+ for the frontend build

Set production environment values, then run:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
npm ci
npm run build
```

Queue workers should be restarted after deployment. Configure a process manager to keep
`php artisan queue:work redis --sleep=1 --tries=3 --backoff=60,300,900` alive. Run one
`php artisan schedule:run` every minute or keep `schedule:work` alive.

Back up PostgreSQL and user storage independently. Test restoration regularly.
