# Remindo frontend

The Remindo marketing and product-preview experience, built with Next.js-compatible
App Router components, React, TypeScript, Tailwind CSS, and the Vinext deployment runtime.

## Run locally

```bash
npm ci
npm run dev
```

Open `http://localhost:3000`.

## Validation

```bash
npm test
npm run lint
```

The current milestone includes:

- Arabic and English UI with RTL/LTR switching
- responsive marketing hero and real product preview
- light and dark themes
- accessible, keyboard-friendly create-reminder modal
- server-rendered metadata, canonical URL, and language alternates

The form currently demonstrates the interaction locally. API wiring and authenticated app
routes are tracked as the next product milestone.
