import { defineRouting } from "next-intl/routing";

/**
 * Supported languages. Each behaves like its own product but shares one codebase.
 * Adding a language later is data-driven: add the code here and a messages file.
 */
export const locales = ["ar", "en", "es", "tr"] as const;
export type Locale = (typeof locales)[number];

export const defaultLocale: Locale = "en";

/** Locales that render right-to-left. */
export const rtlLocales: Locale[] = ["ar"];

export function isRtl(locale: string): boolean {
  return rtlLocales.includes(locale as Locale);
}

export const routing = defineRouting({
  locales,
  defaultLocale,
  localePrefix: "always", // every route is /ar, /en, /es, /tr
});
