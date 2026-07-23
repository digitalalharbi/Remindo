import type { MarketingContent } from "./types";

export const en: MarketingContent = {
  features: {
    seoTitle: "Features — Expiration Reminder Software",
    seoDescription:
      "Multiple reminders, renewals in one tap, document AI, every notification channel, categories, and team sharing — everything Remindo does to keep you ahead of expiry dates.",
    heroTitle: "Everything you need to never miss a date",
    heroSubtitle:
      "Remindo focuses on one job — reminding you before things expire — and does it exceptionally well.",
    sections: [
      { title: "Multiple reminders", body: "Get warned 30 days, 7 days, and 1 day before — on the channels you choose." },
      { title: "Renewals in one tap", body: "Mark something renewed, add the new date, and Remindo picks up where it left off." },
      { title: "Document AI", body: "Drop in a contract and Remindo reads the expiry date for you to confirm — never saved without your review." },
      { title: "Every channel", body: "Email, in-app, web push, SMS, WhatsApp, and calendar sync — with your own quiet hours." },
      { title: "Categories & search", body: "Contracts, licenses, insurance and more, each color-coded and instantly searchable." },
      { title: "Personal or team", body: "Keep reminders to yourself or share them, assign owners, and work together." },
    ],
  },
  howItWorks: {
    seoTitle: "How it works — Remindo in three steps",
    seoDescription:
      "Add what expires, set the date and when to be reminded, and Remindo warns you before it lapses. Your first reminder takes under 30 seconds.",
    heroTitle: "Three steps. That's the whole product.",
    heroSubtitle: "Your first reminder is 30 seconds away — no manual required.",
    sections: [
      { title: "1 · Add what expires", body: "Name the thing — a contract, a license, an insurance policy — or drop in the document and let AI read it." },
      { title: "2 · Set the date", body: "Choose the expiry date and how far ahead you want to be reminded." },
      { title: "3 · Get reminded", body: "Remindo notifies you across your chosen channels, then helps you renew in one tap." },
    ],
  },
  personal: {
    seoTitle: "Remindo for individuals — personal expiry reminders",
    seoDescription:
      "Track your passport, car insurance, subscriptions, warranties, and documents in one calm place. Free to start, no credit card.",
    heroTitle: "Your important dates, quietly handled",
    heroSubtitle:
      "Passports, insurance, subscriptions, warranties — Remindo remembers so you don't have to.",
    sections: [
      { title: "Start free", body: "Five active reminders, email and in-app alerts, no credit card — enough for most people." },
      { title: "One simple place", body: "No spreadsheets, no scattered calendar events. Everything that expires, in one view." },
      { title: "Renew with confidence", body: "Get warned early, renew in a tap, and add the new date without losing history." },
    ],
  },
  business: {
    seoTitle: "Remindo for business — team renewal management",
    seoDescription:
      "Give your team one place for contracts, licenses, insurance, and compliance dates. Roles, sharing, activity logs, API and webhooks.",
    heroTitle: "One place for every renewal your business tracks",
    heroSubtitle:
      "Contracts, licenses, insurance, and compliance dates — shared with your team, never missed.",
    sections: [
      { title: "Shared workspaces", body: "Invite your team, assign owners, and see who's responsible for each renewal." },
      { title: "Roles & activity", body: "Owner, admin, and member roles, with an activity log of every change." },
      { title: "API & webhooks", body: "Connect Remindo to your own systems and get notified programmatically." },
    ],
  },
  integrations: {
    seoTitle: "Integrations — calendars, channels, and webhooks",
    seoDescription:
      "Connect Google Calendar and Outlook, deliver reminders by email, web push, SMS, and WhatsApp, and wire up outgoing webhooks.",
    heroTitle: "Works with the tools you already use",
    heroSubtitle: "Reminders where you'll actually see them, and calendars kept in sync.",
    sections: [
      { title: "Calendars", body: "Connect Google Calendar or Outlook and export your renewal dates automatically." },
      { title: "Notification channels", body: "Email, web push, SMS, and WhatsApp — choose per reminder, with quiet hours." },
      { title: "Webhooks", body: "Send reminder events to your own endpoints with signed, retried delivery." },
    ],
  },
  security: {
    seoTitle: "Security & privacy at Remindo",
    seoDescription:
      "Encrypted sensitive data, tenant isolation, signed document links, 2FA, and a strict privacy stance — your documents are never used to train AI.",
    heroTitle: "Built to be trusted with your dates",
    heroSubtitle: "Simple on the surface, careful underneath.",
    sections: [
      { title: "Your data, isolated", body: "Every workspace is strictly separated. Reminders and documents never leak across tenants." },
      { title: "Encryption & access", body: "Sensitive fields are encrypted, documents use short-lived signed links, and 2FA protects your account." },
      { title: "Privacy first", body: "Your documents are never used to train AI, and extraction only runs when you ask for it." },
    ],
  },
  help: {
    seoTitle: "Help center — Remindo",
    seoDescription: "Guides and answers for getting the most out of Remindo, from your first reminder to team management.",
    heroTitle: "How can we help?",
    heroSubtitle: "Short guides for every part of Remindo.",
    sections: [
      { title: "Getting started", body: "Create your account, choose your language, and add your first reminder in under a minute." },
      { title: "Reminders & renewals", body: "Set multiple alerts, snooze, complete, renew, and archive — all from one screen." },
      { title: "Billing & team", body: "Upgrade your plan, manage seats, and invite teammates to your workspace." },
    ],
  },
  contact: {
    seoTitle: "Contact Remindo",
    seoDescription: "Questions, feedback, or help getting set up? Send us a message and we'll get back to you.",
    heroTitle: "Talk to us",
    heroSubtitle: "We read every message.",
    sections: [],
    nameLabel: "Your name",
    emailLabel: "Email",
    messageLabel: "Message",
    send: "Send message",
    sent: "Thanks — we'll be in touch soon.",
  },
  useCases: {
    contracts: {
      seoTitle: "Contract expiry reminders — never miss a renewal",
      seoDescription: "Track contract end and renewal dates, get warned early, and renew on time. Remindo keeps every agreement in view.",
      heroTitle: "Contract renewal reminders",
      heroSubtitle: "Know before a contract ends — not after.",
      tracks: ["Service agreements", "Vendor contracts", "Leases", "Employment contracts", "NDAs", "SLAs"],
      sections: [
        { title: "See renewals coming", body: "Add each contract's end date and get warned weeks ahead — with time to negotiate." },
        { title: "Attach the document", body: "Keep the signed PDF with the reminder, and let AI pull out the expiry date." },
        { title: "Renew and repeat", body: "Renewed? Add the new term and Remindo watches the next date automatically." },
      ],
    },
    licenses: {
      seoTitle: "License expiration reminders — stay compliant",
      seoDescription: "Track business, professional, and software license expiry dates and renew before they lapse.",
      heroTitle: "License expiration reminders",
      heroSubtitle: "Stay licensed, stay compliant, stay open.",
      tracks: ["Business licenses", "Professional licenses", "Software licenses", "Permits", "Certifications", "Registrations"],
      sections: [
        { title: "Never lapse", body: "Get early warnings before any license expires, so renewals are routine, not emergencies." },
        { title: "One list", body: "Every license across your business in a single, searchable place." },
        { title: "Share the load", body: "Assign each license to the right person on your team." },
      ],
    },
    insurance: {
      seoTitle: "Insurance renewal reminders — never lose coverage",
      seoDescription: "Track policy renewal dates for car, health, property, and business insurance and renew before coverage ends.",
      heroTitle: "Insurance renewal reminders",
      heroSubtitle: "Never drive, live, or operate uninsured.",
      tracks: ["Car insurance", "Health insurance", "Property insurance", "Business insurance", "Travel insurance", "Life insurance"],
      sections: [
        { title: "Renew on time", body: "Get reminded before every policy lapses, with plenty of time to compare and renew." },
        { title: "All policies together", body: "Personal and business coverage, side by side, with the documents attached." },
        { title: "Add the new date", body: "After renewing, add the new period and Remindo keeps watching." },
      ],
    },
    subscriptions: {
      seoTitle: "Subscription renewal reminders — control recurring costs",
      seoDescription: "Track subscription and membership renewal dates so you renew what you use and cancel what you don't — before you're billed.",
      heroTitle: "Subscription reminders",
      heroSubtitle: "Renew what you use. Cancel what you don't. Before the charge.",
      tracks: ["Software (SaaS)", "Domains & hosting", "Memberships", "Streaming", "Cloud services", "Warranties"],
      sections: [
        { title: "No surprise charges", body: "Get warned before a subscription renews, with time to decide." },
        { title: "See everything", body: "All your recurring commitments in one place, not scattered across inboxes." },
        { title: "Cancel with time to spare", body: "A reminder a week before means you never miss a cancellation window." },
      ],
    },
    documents: {
      seoTitle: "Document expiry reminders — passports, IDs, and more",
      seoDescription: "Track expiry dates for passports, IDs, visas, and official documents, and renew before they expire.",
      heroTitle: "Document expiry reminders",
      heroSubtitle: "Passports, IDs, visas — renewed before they expire.",
      tracks: ["Passports", "National IDs", "Visas", "Driver's licenses", "Certificates", "Official documents"],
      sections: [
        { title: "Beat the queue", body: "Renewals take time. Get reminded early enough to avoid last-minute rushes." },
        { title: "Extract automatically", body: "Upload a document and let AI read the number, issuer, and expiry date for you to confirm." },
        { title: "Keep the family covered", body: "Track everyone's documents in one place." },
      ],
    },
    maintenance: {
      seoTitle: "Maintenance reminders — servicing and inspections",
      seoDescription: "Track periodic maintenance, servicing, and inspection dates for vehicles, equipment, and property.",
      heroTitle: "Maintenance reminders",
      heroSubtitle: "Service on schedule, not after something breaks.",
      tracks: ["Vehicle servicing", "Equipment maintenance", "Property inspections", "Warranty servicing", "Safety checks", "Filter changes"],
      sections: [
        { title: "Stay ahead of wear", body: "Set recurring reminders for every service interval and never fall behind." },
        { title: "Recurring by default", body: "Monthly, yearly, or custom cycles — Remindo re-schedules automatically." },
        { title: "Log the history", body: "Mark each service done and keep a clear record over time." },
      ],
    },
  },
  legal: {
    terms: {
      seoTitle: "Terms of Service — Remindo",
      seoDescription: "The terms that govern your use of Remindo.",
      title: "Terms of Service",
      updated: "Last updated: July 2026",
      sections: [
        { title: "Using Remindo", body: "Remindo helps you track expiry and renewal dates. You're responsible for the accuracy of the dates you enter and for acting on the reminders you receive." },
        { title: "Your account", body: "Keep your credentials secure. You're responsible for activity under your account. Notify us of any unauthorized use." },
        { title: "Plans & billing", body: "Paid plans renew automatically until cancelled. Prices, limits, and taxes are shown at checkout in your currency." },
        { title: "Reminders are best-effort", body: "We work hard to deliver every reminder, but you shouldn't rely on Remindo as the sole safeguard for critical or legal deadlines." },
        { title: "Changes", body: "We may update these terms; we'll note the date above and, for material changes, let you know in the app." },
      ],
    },
    privacy: {
      seoTitle: "Privacy Policy — Remindo",
      seoDescription: "How Remindo collects, uses, and protects your data.",
      title: "Privacy Policy",
      updated: "Last updated: July 2026",
      sections: [
        { title: "What we collect", body: "Your account details, the reminders and documents you create, and basic usage data needed to run the service." },
        { title: "How we use it", body: "To deliver reminders, provide the service, and improve Remindo. We never sell your data." },
        { title: "AI and your documents", body: "Documents are only processed when you request extraction, and are never used to train AI models." },
        { title: "Your rights", body: "You can access, export, or delete your data at any time from your account or by contacting us." },
        { title: "Security", body: "We encrypt sensitive data, isolate every workspace, and use signed links for documents." },
      ],
    },
    cookies: {
      seoTitle: "Cookie Policy — Remindo",
      seoDescription: "How Remindo uses cookies and similar technologies.",
      title: "Cookie Policy",
      updated: "Last updated: July 2026",
      sections: [
        { title: "Essential cookies", body: "We use a small number of cookies to keep you signed in and to protect against cross-site request forgery." },
        { title: "Preferences", body: "We remember your language and light/dark theme so the site feels consistent." },
        { title: "No ad tracking", body: "We don't use advertising or cross-site tracking cookies." },
      ],
    },
  },
  blog: {
    seoTitle: "Blog — Remindo",
    seoDescription: "Practical tips on staying ahead of renewals, expiry dates, and the paperwork of everyday life and business.",
    title: "The Remindo blog",
    subtitle: "Short, practical notes on never missing a date.",
    posts: [
      {
        slug: "never-miss-a-renewal",
        title: "How to never miss a renewal again",
        excerpt: "A simple system for staying ahead of every expiry date — without spreadsheets.",
        author: "The Remindo Team",
        date: "2026-07-01",
        body: [
          "Most missed renewals aren't a memory problem — they're a system problem. The date lives in an email, a drawer, or someone's head, and nobody's watching it.",
          "The fix is boring and reliable: put every expiry date in one place, attach a reminder that fires early, and choose a channel you actually check.",
          "That's the whole idea behind Remindo. Add the date once, and let the reminder do the remembering.",
        ],
      },
      {
        slug: "how-early-should-you-be-reminded",
        title: "How early should a reminder fire?",
        excerpt: "A quick rule of thumb for setting reminder lead times that actually help.",
        author: "The Remindo Team",
        date: "2026-07-10",
        body: [
          "Too late and you're rushing; too early and you forget again. The sweet spot depends on how long the renewal takes.",
          "For quick renewals, 7 days is plenty. For anything involving paperwork or approvals — passports, licenses, contracts — give yourself 30 days or more.",
          "Remindo lets you stack several reminders on one date, so you can have an early heads-up and a final nudge.",
        ],
      },
    ],
  },
};
