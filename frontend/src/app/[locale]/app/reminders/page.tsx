"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { Plus, Search, BellRing } from "lucide-react";
import { useReminders } from "@/lib/hooks";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { ReminderCard } from "@/components/app/reminder-card";
import { CreateReminderDialog } from "@/components/app/create-reminder-dialog";
import { cn } from "@/lib/utils";

const FILTERS = ["all", "active", "overdue", "upcoming", "completed"] as const;

export default function RemindersPage() {
  const t = useTranslations("app.reminders");
  const [search, setSearch] = useState("");
  const [filter, setFilter] = useState<(typeof FILTERS)[number]>("all");
  const [createOpen, setCreateOpen] = useState(false);

  const params: Record<string, string> = {};
  if (search) params.search = search;
  if (filter === "overdue") params.filter = "overdue";
  else if (filter === "upcoming") params.filter = "upcoming";
  else if (filter === "active") params.status = "active";
  else if (filter === "completed") params.status = "completed";

  const { data, isLoading } = useReminders(params);
  const reminders = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-2xl font-semibold tracking-tight">{t("title")}</h1>
        <Button onClick={() => setCreateOpen(true)}>
          <Plus className="size-4" />
          {t("add")}
        </Button>
      </div>

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div className="relative flex-1">
          <Search className="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder={t("searchPlaceholder")}
            className="ps-9"
          />
        </div>
        <div className="flex gap-1 overflow-x-auto">
          {FILTERS.map((f) => (
            <button
              key={f}
              onClick={() => setFilter(f)}
              className={cn(
                "whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium transition-colors",
                filter === f
                  ? "bg-primary/10 text-primary"
                  : "text-muted-foreground hover:bg-muted",
              )}
            >
              {t(
                `filter${f.charAt(0).toUpperCase()}${f.slice(1)}` as
                  | "filterAll"
                  | "filterActive"
                  | "filterOverdue"
                  | "filterUpcoming"
                  | "filterCompleted",
              )}
            </button>
          ))}
        </div>
      </div>

      {isLoading ? (
        <div className="space-y-2">
          {Array.from({ length: 6 }).map((_, i) => (
            <Skeleton key={i} className="h-16 rounded-xl" />
          ))}
        </div>
      ) : reminders.length === 0 ? (
        <div className="rounded-xl border border-dashed border-border bg-card/50 px-6 py-16 text-center">
          <div className="mx-auto grid size-12 place-items-center rounded-full bg-muted">
            <BellRing className="size-6 text-muted-foreground" />
          </div>
          <h3 className="mt-4 font-medium">{t("emptyTitle")}</h3>
          <p className="mx-auto mt-1 max-w-sm text-sm text-muted-foreground">
            {t("emptyBody")}
          </p>
          <Button className="mt-5" onClick={() => setCreateOpen(true)}>
            <Plus className="size-4" />
            {t("add")}
          </Button>
        </div>
      ) : (
        <div className="space-y-2">
          {reminders.map((r) => (
            <ReminderCard key={r.id} reminder={r} />
          ))}
        </div>
      )}

      <CreateReminderDialog open={createOpen} onClose={() => setCreateOpen(false)} />
    </div>
  );
}
