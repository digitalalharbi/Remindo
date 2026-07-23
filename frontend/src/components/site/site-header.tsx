"use client";

import { useTranslations } from "next-intl";
import { Menu, X } from "lucide-react";
import { useState } from "react";
import { Link } from "@/i18n/navigation";
import { Logo } from "./logo";
import { ThemeToggle } from "./theme-toggle";
import { LocaleSwitcher } from "./locale-switcher";
import { buttonVariants } from "@/components/ui/button";
import { useMe } from "@/lib/hooks";
import { cn } from "@/lib/utils";

export function SiteHeader() {
  const t = useTranslations("nav");
  const tc = useTranslations("common");
  const [open, setOpen] = useState(false);
  // Cookie-based session check; when signed in we show "Open dashboard".
  const { data: user } = useMe();

  const links = [
    { href: "/how-it-works", label: t("howItWorks") },
    { href: "/features", label: t("features") },
    { href: "/pricing", label: t("pricing") },
    { href: "/security", label: t("security") },
  ] as const;

  return (
    <header className="sticky top-0 z-40 border-b border-border/80 bg-background/80 backdrop-blur-md">
      <div className="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
        <div className="flex items-center gap-8">
          <Logo />
          <nav className="hidden items-center gap-1 md:flex">
            {links.map((l) => (
              <Link
                key={l.href}
                href={l.href}
                className="rounded-md px-3 py-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
              >
                {l.label}
              </Link>
            ))}
          </nav>
        </div>

        <div className="flex items-center gap-2">
          <LocaleSwitcher />
          <ThemeToggle />
          <div className="hidden items-center gap-2 sm:flex">
            {user ? (
              <Link href="/dashboard" className={cn(buttonVariants({ size: "sm" }))}>
                {tc("openDashboard")}
              </Link>
            ) : (
              <>
                <Link
                  href="/login"
                  className="rounded-md px-3 py-2 text-sm font-medium text-foreground transition-colors hover:text-primary"
                >
                  {tc("signIn")}
                </Link>
                <Link
                  href="/register"
                  className={cn(buttonVariants({ size: "sm" }))}
                >
                  {tc("getStarted")}
                </Link>
              </>
            )}
          </div>
          <button
            className="grid size-9 place-items-center rounded-lg border border-border md:hidden"
            aria-label="Menu"
            onClick={() => setOpen((o) => !o)}
          >
            {open ? <X className="size-4" /> : <Menu className="size-4" />}
          </button>
        </div>
      </div>

      {open && (
        <div className="border-t border-border bg-background px-4 py-3 md:hidden">
          <nav className="flex flex-col gap-1">
            {links.map((l) => (
              <Link
                key={l.href}
                href={l.href}
                onClick={() => setOpen(false)}
                className="rounded-md px-3 py-2.5 text-sm text-foreground hover:bg-muted"
              >
                {l.label}
              </Link>
            ))}
            <div className="mt-2 flex gap-2">
              <Link
                href="/login"
                onClick={() => setOpen(false)}
                className={cn(buttonVariants({ variant: "secondary", size: "sm" }), "flex-1")}
              >
                {tc("signIn")}
              </Link>
              <Link
                href="/register"
                onClick={() => setOpen(false)}
                className={cn(buttonVariants({ size: "sm" }), "flex-1")}
              >
                {tc("getStarted")}
              </Link>
            </div>
          </nav>
        </div>
      )}
    </header>
  );
}
