import type { MetadataRoute } from "next";
import { locales } from "@/i18n/routing";

const SITE = process.env.NEXT_PUBLIC_SITE_URL ?? "https://remindo.me";

// Public, indexable marketing pages (never the app or account pages).
const PUBLIC_PATHS = [
  "",
  "/how-it-works",
  "/features",
  "/pricing",
  "/integrations",
  "/security",
  "/faq",
  "/contact",
  "/terms",
  "/privacy",
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
