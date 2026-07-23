"use client";

import { useEffect, useState } from "react";
import { Menu } from "lucide-react";
import { useMe } from "@/lib/hooks";
import { useRouter } from "@/i18n/navigation";
import { AppSidebar } from "@/components/app/app-sidebar";
import { LocaleSwitcher } from "@/components/site/locale-switcher";
import { ThemeToggle } from "@/components/site/theme-toggle";
import { Logo } from "@/components/site/logo";
import { Skeleton } from "@/components/ui/skeleton";

export default function AppLayout({ children }: { children: React.ReactNode }) {
  const { data: user, isLoading, isError } = useMe();
  const router = useRouter();
  const [mobileOpen, setMobileOpen] = useState(false);

  useEffect(() => {
    if (!isLoading && (isError || !user)) {
      router.replace("/login");
    }
  }, [isLoading, isError, user, router]);

  if (isLoading) {
    return (
      <div className="flex min-h-dvh items-center justify-center">
        <Skeleton className="size-10 rounded-full" />
      </div>
    );
  }

  if (!user) return null;

  return (
    <div className="flex min-h-dvh bg-muted/20">
      {/* Desktop sidebar */}
      <aside className="fixed inset-y-0 hidden w-64 border-e border-border bg-card lg:block">
        <AppSidebar />
      </aside>

      {/* Mobile drawer */}
      {mobileOpen && (
        <div className="fixed inset-0 z-40 lg:hidden">
          <div
            className="absolute inset-0 bg-foreground/25"
            onClick={() => setMobileOpen(false)}
          />
          <aside className="absolute inset-y-0 start-0 w-64 border-e border-border bg-card">
            <AppSidebar onNavigate={() => setMobileOpen(false)} />
          </aside>
        </div>
      )}

      <div className="flex flex-1 flex-col lg:ps-64">
        {/* Mobile top bar */}
        <header className="sticky top-0 z-30 flex items-center justify-between border-b border-border bg-card/80 px-4 py-3 backdrop-blur lg:hidden">
          <button
            className="grid size-9 place-items-center rounded-lg border border-border"
            aria-label="Menu"
            onClick={() => setMobileOpen(true)}
          >
            <Menu className="size-4" />
          </button>
          <Logo />
          <div className="flex items-center gap-1.5">
            <LocaleSwitcher />
            <ThemeToggle />
          </div>
        </header>

        {/* Desktop top bar */}
        <header className="sticky top-0 z-20 hidden items-center justify-end gap-2 border-b border-border bg-card/60 px-6 py-3 backdrop-blur lg:flex">
          <LocaleSwitcher />
          <ThemeToggle />
        </header>

        <main className="flex-1 p-4 sm:p-6 lg:p-8">
          <div className="mx-auto max-w-5xl">{children}</div>
        </main>
      </div>
    </div>
  );
}
