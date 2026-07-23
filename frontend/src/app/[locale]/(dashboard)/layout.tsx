"use client";

import { useEffect, useState } from "react";
import { useMe } from "@/lib/hooks";
import { useRouter } from "@/i18n/navigation";
import { DashboardSidebar } from "@/components/dashboard/dashboard-sidebar";
import { DashboardTopbar } from "@/components/dashboard/dashboard-topbar";
import { CreateReminderDialog } from "@/components/dashboard/create-reminder-dialog";
import { Skeleton } from "@/components/ui/skeleton";
import { useUiStore } from "@/stores/ui";

/**
 * DashboardLayout — the authenticated SaaS shell. Completely separate from the
 * marketing site: no hero, no marketing header/footer, no pricing. Guards access
 * (redirects to /login) and hosts the shared create-reminder dialog.
 */
export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  const { data: user, isLoading, isError } = useMe();
  const router = useRouter();
  const [mobileOpen, setMobileOpen] = useState(false);
  const createOpen = useUiStore((s) => s.createReminderOpen);
  const closeCreate = useUiStore((s) => s.closeCreateReminder);

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
    <div className="min-h-dvh bg-muted/20">
      {/* Desktop sidebar */}
      <aside className="fixed inset-y-0 hidden w-64 border-e border-border bg-card lg:block">
        <DashboardSidebar />
      </aside>

      {/* Mobile drawer */}
      {mobileOpen && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="absolute inset-0 bg-foreground/25" onClick={() => setMobileOpen(false)} />
          <aside className="absolute inset-y-0 start-0 w-64 border-e border-border bg-card">
            <DashboardSidebar onNavigate={() => setMobileOpen(false)} />
          </aside>
        </div>
      )}

      <div className="flex min-h-dvh flex-col lg:ps-64">
        <DashboardTopbar onOpenMenu={() => setMobileOpen(true)} />
        <main className="flex-1 p-4 sm:p-6 lg:p-8">
          <div className="mx-auto max-w-5xl">{children}</div>
        </main>
      </div>

      <CreateReminderDialog open={createOpen} onClose={closeCreate} />
    </div>
  );
}
