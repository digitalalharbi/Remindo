import type { MetadataRoute } from "next";
import { locales } from "@/i18n/routing";

const SITE = process.env.NEXT_PUBLIC_SITE_URL ?? "https://remindo.me";

// Public, indexable marketing pages (never the app or account pages).
const PUBLIC_PATHS = [
  "",
  "/how-it-works",
  "/features",
  "/pricing",
  "/personal",
  "/business",
  "/integrations",
  "/security",
  "/faq",
  "/help",
  "/blog",
  "/blog/never-miss-a-renewal",
  "/blog/how-early-should-you-be-reminded",
  "/contact",
  "/use-cases/contracts",
  "/use-cases/licenses",
  "/use-cases/insurance",
  "/use-cases/subscriptions",
  "/use-cases/documents",
  "/use-cases/maintenance",
  "/terms",
  "/privacy",
  "/cookies",
];

export default function sitemap(): MetadataRoute.Sitemap {
  const entries: MetadataRoute.Sitemap = [];

  for (const path of PUBLIC_PATHS) {
    for (const locale of locales) {
      entries.push({
        url: `${SITE}/${locale}${path}`,
        lastModified: new Date(),
        changeFrequency: path === "" ? "weekly" : "monthly",
        priority: path === "" ? 1 : 0.7,
        alternates: {
          languages: Object.fromEntries(
            locales.map((l) => [l, `${SITE}/${l}${path}`]),
          ),
        },
      });
    }
  }

  return entries;
}
