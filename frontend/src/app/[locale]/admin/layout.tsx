"use client";

import { useEffect, useState } from "react";
import { Menu } from "lucide-react";
import { useMe } from "@/lib/hooks";
import { useRouter } from "@/i18n/navigation";
import { AdminSidebar } from "@/components/admin/admin-sidebar";
import { ThemeToggle } from "@/components/site/theme-toggle";
import { LocaleSwitcher } from "@/components/site/locale-switcher";
import { Skeleton } from "@/components/ui/skeleton";

/**
 * AdminLayout — Super Admin shell, entirely separate from the user dashboard and
 * marketing site. Only Remindo staff (is_super_admin) may enter; everyone else
 * is redirected to their dashboard.
 */
export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const { data: user, isLoading } = useMe();
  const router = useRouter();
  const [mobileOpen, setMobileOpen] = useState(false);

  useEffect(() => {
    if (!isLoading) {
      if (!user) router.replace("/login");
      else if (!user.is_super_admin) router.replace("/dashboard");
    }
  }, [isLoading, user, router]);

  if (isLoading || !user?.is_super_admin) {
    return (
      <div className="flex min-h-dvh items-center justify-center">
        <Skeleton className="size-10 rounded-full" />
      </div>
    );
  }

  return (
    <div className="min-h-dvh bg-muted/20">
      <aside className="fixed inset-y-0 hidden w-60 border-e border-border bg-card lg:block">
        <AdminSidebar />
      </aside>

      {mobileOpen && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="absolute inset-0 bg-foreground/25" onClick={() => setMobileOpen(false)} />
          <aside className="absolute inset-y-0 start-0 w-60 border-e border-border bg-card">
            <AdminSidebar onNavigate={() => setMobileOpen(false)} />
          </aside>
        </div>
      )}

      <div className="flex min-h-dvh flex-col lg:ps-60">
        <header className="sticky top-0 z-30 flex items-center justify-between border-b border-border bg-card/70 px-4 py-2.5 backdrop-blur lg:px-6">
          <button
            className="grid size-9 place-items-center rounded-lg border border-border lg:hidden"
            aria-label="Menu"
            onClick={() => setMobileOpen(true)}
          >
            <Menu className="size-4" />
          </button>
          <div className="ms-auto flex items-center gap-1.5">
            <LocaleSwitcher />
            <ThemeToggle />
          </div>
        </header>
        <main className="flex-1 p-4 sm:p-6 lg:p-8">
          <div className="mx-auto max-w-6xl">{children}</div>
        </main>
      </div>
    </div>
  );
}
