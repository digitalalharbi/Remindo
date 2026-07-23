import type { MetadataRoute } from "next";

const SITE = process.env.NEXT_PUBLIC_SITE_URL ?? "https://remindo.me";

export default function robots(): MetadataRoute.Robots {
  return {
    rules: {
      userAgent: "*",
      allow: "/",
      // Never index the app, account, or payment surfaces.
      disallow: ["/*/app", "/*/app/", "/*/login", "/*/register", "/api/"],
    },
    sitemap: `${SITE}/sitemap.xml`,
    host: SITE,
  };
}
