import { en } from "./en";
import { ar } from "./ar";
import { es } from "./es";
import { tr } from "./tr";
import type { MarketingContent } from "./types";

const byLocale: Record<string, MarketingContent> = { en, ar, es, tr };

/** Resolve marketing content for a locale, falling back to English. */
export function marketing(locale: string): MarketingContent {
  return byLocale[locale] ?? en;
}

export type { MarketingContent } from "./types";
