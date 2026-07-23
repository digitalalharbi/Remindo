"use client";

import { Globe } from "lucide-react";
import { useLocale } from "next-intl";
import { useState, useRef, useEffect } from "react";
import { usePathname, useRouter } from "@/i18n/navigation";
import { locales, type Locale } from "@/i18n/routing";
import { cn } from "@/lib/utils";

const LABELS: Record<Locale, string> = {
  ar: "العربية",
  en: "English",
  es: "Español",
  tr: "Türkçe",
};

export function LocaleSwitcher() {
  const locale = useLocale();
  const pathname = usePathname();
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", onClick);
    return () => document.removeEventListener("mousedown", onClick);
  }, []);

  return (
    <div ref={ref} className="relative">
      <button
        type="button"
        aria-label="Change language"
        aria-expanded={open}
        onClick={() => setOpen((o) => !o)}
        className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-border bg-card px-3 text-sm transition-colors hover:bg-muted"
      >
        <Globe className="size-4" />
        <span className="hidden sm:inline">{LABELS[locale as Locale]}</span>
      </button>
      {open && (
        <div className="absolute end-0 z-50 mt-2 w-40 animate-fade-up overflow-hidden rounded-xl border border-border bg-card p-1 shadow-lg">
          {locales.map((l) => (
            <button
              key={l}
              onClick={() => {
                router.replace(pathname, { locale: l });
                setOpen(false);
              }}
              className={cn(
                "flex w-full items-center rounded-md px-3 py-2 text-sm transition-colors hover:bg-muted",
                l === locale && "font-medium text-primary",
              )}
            >
              {LABELS[l]}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
