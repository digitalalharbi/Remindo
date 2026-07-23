"use client";

import { useTranslations, useLocale } from "next-intl";
import { useReminders, useDashboard } from "@/lib/hooks";
import { Skeleton } from "@/components/ui/skeleton";
import type { Reminder } from "@/lib/types";

export default function ReportsPage() {
  const t = useTranslations("app.reports");
  const tn = useTranslations("app.nav");
  const locale = useLocale();
  const { data: dashboard, isLoading: dashLoading } = useDashboard();
  const { data: remindersRes, isLoading: remLoading } = useReminders({ per_page: 250 });

  const reminders: Reminder[] = remindersRes?.data ?? [];

  // Operational breakdown by category (client-side, from the reminders list).
  const byCategory = new Map<string, number>();
  for (const r of reminders) {
    const name = r.category
      ? typeof r.category.name === "string"
        ? r.category.name
        : r.category.name[locale] ?? r.category.name.en
      : t("uncategorized");
    byCategory.set(name, (byCategory.get(name) ?? 0) + 1);
  }
  const categoryRows = [...byCategory.entries()].sort((a, b) => b[1] - a[1]);
  const maxCat = Math.max(1, ...categoryRows.map(([, c]) => c));

  const counts = dashboard?.counts;

  return (
    <div className="space-y-8">
      <h1 className="text-2xl font-semibold tracking-tight">{tn("reports")}</h1>

      {/* Status overview */}
      <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
        {(["active", "overdue", "thisWeek", "thisMonth"] as const).map((k) => (
          <div key={k} className="rounded-xl border border-border bg-card p-4">
            {dashLoading ? (
              <Skeleton className="h-8 w-10" />
            ) : (
              <div className="text-3xl font-semibold tracking-tight">
                {k === "active"
                  ? counts?.active
                  : k === "overdue"
                    ? counts?.overdue
                    : k === "thisWeek"
                      ? counts?.this_week
                      : counts?.this_month}
              </div>
            )}
            <div className="mt-1 text-sm text-muted-foreground">{t(k)}</div>
          </div>
        ))}
      </div>

      {/* By category */}
      <div>
        <h2 className="mb-3 text-sm font-semibold text-muted-foreground">{t("byCategory")}</h2>
        {remLoading ? (
          <Skeleton className="h-40 rounded-xl" />
        ) : categoryRows.length === 0 ? (
          <div className="rounded-xl border border-dashed border-border bg-card/50 px-6 py-10 text-center text-sm text-muted-foreground">
            {t("empty")}
          </div>
        ) : (
          <div className="space-y-3 rounded-xl border border-border bg-card p-5">
            {categoryRows.map(([name, count]) => (
              <div key={name} className="flex items-center gap-3">
                <span className="w-32 shrink-0 truncate text-sm">{name}</span>
                <div className="h-2.5 flex-1 overflow-hidden rounded-full bg-muted">
                  <div
                    className="h-full rounded-full bg-primary"
                    style={{ width: `${(count / maxCat) * 100}%` }}
                  />
                </div>
                <span className="w-8 shrink-0 text-end text-sm tabular-nums text-muted-foreground">
                  {count}
                </span>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
