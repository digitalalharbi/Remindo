# Remindo — Design System

An original system for Remindo. Calm, trustworthy, precise. Simplicity over feature density,
generous white space, one clear accent, quiet motion. Not a clone of any product; only the
*level* of craft is the reference.

## Principles

1. Simplicity before features.
2. Comfortable white space.
3. Clear visual hierarchy, short text.
4. Few elements per screen; one primary action.
5. Soft, non-distracting motion.
6. Full consistency across pages; understandable without explanation.
7. WCAG AA, keyboard support, mobile-first, RTL + LTR.

## Color

Defined as CSS variables (HSL) in `frontend/src/styles/tokens.css`, exposed to Tailwind. Two
themes: light + dark.

**Brand** — a calm, trustworthy teal-leaning blue is the single accent.

| Token            | Light            | Dark             | Use                        |
| ---------------- | ---------------- | ---------------- | -------------------------- |
| `--background`   | near-white       | near-black       | page background            |
| `--foreground`   | slate-900        | slate-100        | body text                  |
| `--muted`        | slate-100        | slate-800        | subtle surfaces            |
| `--muted-foreground` | slate-500    | slate-400        | secondary text             |
| `--card`         | white            | slate-900        | cards, popovers            |
| `--border`       | slate-200        | slate-800        | hairlines                  |
| `--primary`      | brand-600        | brand-500        | **the** accent, CTAs       |
| `--primary-foreground` | white      | slate-950        | text on primary            |
| `--ring`         | brand-500        | brand-500        | focus ring                 |

**State colors** — used sparingly, only for status.

| Token       | Meaning  |
| ----------- | -------- |
| `--success` | renewed / completed / safe (green) |
| `--warning` | due soon (amber) |
| `--danger`  | overdue / destructive (red) |

Rules: no rainbow of colors, no heavy gradients, no heavy glassmorphism, no needless cards.

## Typography

- **Latin (en/es/tr):** Inter.
- **Arabic (ar):** IBM Plex Sans Arabic (excellent RTL, pairs well with Inter's proportions).

Type scale (rem): 0.75, 0.875, 1, 1.125, 1.25, 1.5, 1.875, 2.25, 3. Line-height loosens as size
grows. Weights: 400 body, 500 UI, 600 headings.

## Spacing & radius

- Spacing scale (px): 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80 — everything snaps to it.
- Radius: `--radius` = 12px base; sm 8, lg 16, full 9999. Balanced, not bubbly.
- Shadows: very light, layered; used to lift, never to decorate.

## Motion

- Durations: 120ms (micro), 200ms (default), 320ms (enter/leave).
- Easing: `cubic-bezier(0.2, 0, 0, 1)` for enter, standard ease for exit.
- Respect `prefers-reduced-motion`.

## Components (shadcn/ui base, restyled)

Button, Input, Textarea, Select, Date picker, Dialog/Sheet, Popover, Toast, Badge, Card, Tabs,
Dropdown, Skeleton, Empty state, Calendar. All theme-aware and RTL-aware.

## States every screen must handle

- Empty state (useful, with a primary action).
- Skeleton loading.
- Clear success and error messages.
- Precise micro-interactions.
