<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Preview mode
    |--------------------------------------------------------------------------
    |
    | When enabled, the deployment is a public *preview* connected to a separate
    | preview database. Integrations that require real credentials (payments,
    | SMS/WhatsApp, OAuth, calendar sync, AI, live email delivery) run through
    | sandbox/mock adapters. The frontend shows a "Preview Mode" badge, and
    | outgoing mail is captured to an in-app mailbox the admin can view online.
    |
    | This is NEVER a substitute for the production flag — it is purely cosmetic
    | + operational. Demo seeders are gated separately in DatabaseSeeder.
    |
    */

    'enabled' => (bool) env('PREVIEW_MODE', false),

    // Capture outgoing mail into the `mail_previews` table (viewable in Admin →
    // Mail log) instead of relying on an external inbox. Defaults on in preview.
    'capture_mail' => (bool) env('PREVIEW_CAPTURE_MAIL', env('PREVIEW_MODE', false)),

    // Demo credentials surfaced on the login screen's "Try demo account" button
    // and documented for reviewers. Only used to prefill the preview login.
    'demo_email' => env('PREVIEW_DEMO_EMAIL', 'demo@remindo.me'),

];
