import type { Metadata } from "next";
import { locales } from "@/i18n/routing";

const SITE = process.env.NEXT_PUBLIC_SITE_URL ?? "https://remindo.me";

/**
 * Build per-page, per-locale metadata: unique title + description, canonical URL,
 * hreflang alternates, and Open Graph. `path` is the locale-less path ("/features").
 */
export function pageMetadata({
  locale,
  path,
  title,
  description,
}: {
  locale: string;
  path: string;
  title: string;
  description: string;
}): Metadata {
  const clean = path === "/" ? "" : path;
  return {
    title,
    description,
    alternates: {
      canonical: `${SITE}/${locale}${clean}`,
      languages: Object.fromEntries(locales.map((l) => [l, `${SITE}/${l}${clean}`])),
    },
    openGraph: {
      title,
      description,
      url: `${SITE}/${locale}${clean}`,
      siteName: "Remindo",
      locale,
      type: "website",
    },
    twitter: { card: "summary_large_image", title, description },
  };
}

/** JSON-LD helper — renders a <script type="application/ld+json"> payload. */
export function jsonLd(data: Record<string, unknown>): string {
  return JSON.stringify(data);
}

export const organizationSchema = {
  "@context": "https://schema.org",
  "@type": "Organization",
  name: "Remindo",
  url: SITE,
  logo: `${SITE}/icon.png`,
  sameAs: [] as string[],
};

export const websiteSchema = {
  "@context": "https://schema.org",
  "@type": "WebSite",
  name: "Remindo",
  url: SITE,
};

export const softwareApplicationSchema = {
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  name: "Remindo",
  applicationCategory: "BusinessApplication",
  operatingSystem: "Web",
  offers: {
    "@type": "Offer",
    price: "0",
    priceCurrency: "USD",
  },
};
