"use client";

import { useTranslations } from "next-intl";
import { Plus, Inbox } from "lucide-react";
import { useDashboard, useMe } from "@/lib/hooks";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { ReminderCard } from "@/components/dashboard/reminder-card";
import { useUiStore } from "@/stores/ui";
import { cn } from "@/lib/utils";

export default function DashboardPage() {
  const t = useTranslations("app.dashboard");
  const { data: user } = useMe();
  const { data, isLoading } = useDashboard();
  const openCreate = useUiStore((s) => s.openCreateReminder);

  const stats = [
    { key: "overdue", value: data?.counts.overdue ?? 0, tone: "text-danger" },
    { key: "thisWeek", value: data?.counts.this_week ?? 0, tone: "text-warning-foreground" },
    { key: "thisMonth", value: data?.counts.this_month ?? 0, tone: "text-foreground" },
    { key: "active", value: data?.counts.active ?? 0, tone: "text-primary" },
  ] as const;

  const upcoming = data?.upcoming ?? [];

  return (
    <div className="space-y-8">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">
            {t("greeting", { name: user?.name?.split(" ")[0] ?? "" })}
          </h1>
          <p className="mt-1 text-muted-foreground">{t("subtitle")}</p>
        </div>
        <Button onClick={openCreate}>
          <Plus className="size-4" />
          {t("addReminder")}
        </Button>
      </div>

      {/* Stat tiles */}
      <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
        {stats.map((s) => (
          <div key={s.key} className="rounded-xl border border-border bg-card p-4">
            {isLoading ? (
              <Skeleton className="h-8 w-10" />
            ) : (
              <div className={cn("text-3xl font-semibold tracking-tight", s.tone)}>
                {s.value}
              </div>
            )}
            <div className="mt-1 text-sm text-muted-foreground">{t(s.key)}</div>
          </div>
        ))}
      </div>

      {/* Upcoming list */}
      <div>
        <h2 className="mb-3 text-sm font-semibold text-muted-foreground">
          {t("upcoming")}
        </h2>
        {isLoading ? (
          <div className="space-y-2">
            {Array.from({ length: 4 }).map((_, i) => (
              <Skeleton key={i} className="h-16 rounded-xl" />
            ))}
          </div>
        ) : upcoming.length === 0 ? (
          <div className="rounded-xl border border-dashed border-border bg-card/50 px-6 py-14 text-center">
            <div className="mx-auto grid size-12 place-items-center rounded-full bg-muted">
              <Inbox className="size-6 text-muted-foreground" />
            </div>
            <h3 className="mt-4 font-medium">{t("emptyTitle")}</h3>
            <p className="mx-auto mt-1 max-w-sm text-sm text-muted-foreground">
              {t("emptyBody")}
            </p>
            <Button className="mt-5" onClick={openCreate}>
              <Plus className="size-4" />
              {t("addReminder")}
            </Button>
          </div>
        ) : (
          <div className="space-y-2">
            {upcoming.map((r) => (
              <ReminderCard key={r.id} reminder={r} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
