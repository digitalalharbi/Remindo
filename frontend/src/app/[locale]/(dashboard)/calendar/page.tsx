"use client";

import { useState } from "react";
import { useTranslations, useLocale } from "next-intl";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { useReminders } from "@/lib/hooks";
import { Skeleton } from "@/components/ui/skeleton";
import { cn } from "@/lib/utils";
import type { Reminder } from "@/lib/types";

export default function CalendarPage() {
  const tn = useTranslations("app.nav");
  const locale = useLocale();
  const [cursor, setCursor] = useState(() => {
    const now = new Date();
    return { year: now.getFullYear(), month: now.getMonth() };
  });

  const { data, isLoading } = useReminders({ per_page: 250 });
  const reminders: Reminder[] = data?.data ?? [];

  // Group reminders by ISO date.
  const byDate = new Map<string, Reminder[]>();
  for (const r of reminders) {
    const list = byDate.get(r.expiry_date) ?? [];
    list.push(r);
    byDate.set(r.expiry_date, list);
  }

  const first = new Date(cursor.year, cursor.month, 1);
  const startWeekday = first.getDay();
  const daysInMonth = new Date(cursor.year, cursor.month + 1, 0).getDate();
  const monthName = new Intl.DateTimeFormat(locale, { month: "long", year: "numeric" }).format(first);
  const weekdayFmt = new Intl.DateTimeFormat(locale, { weekday: "short" });
  const weekdays = Array.from({ length: 7 }, (_, i) => weekdayFmt.format(new Date(2024, 0, 7 + i)));

  const cells: (number | null)[] = [
    ...Array.from({ length: startWeekday }, () => null),
    ...Array.from({ length: daysInMonth }, (_, i) => i + 1),
  ];

  const iso = (day: number) =>
    `${cursor.year}-${String(cursor.month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
  const todayIso = new Date().toISOString().slice(0, 10);

  const move = (delta: number) => {
    const m = cursor.month + delta;
    setCursor({
      year: cursor.year + Math.floor(m / 12),
      month: ((m % 12) + 12) % 12,
    });
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold tracking-tight">{tn("calendar")}</h1>
        <div className="flex items-center gap-2">
          <button
            onClick={() => move(-1)}
            className="grid size-9 place-items-center rounded-lg border border-border hover:bg-muted"
            aria-label="Previous month"
          >
            <ChevronLeft className="size-4 rtl:rotate-180" />
          </button>
          <span className="min-w-40 text-center text-sm font-medium">{monthName}</span>
          <button
            onClick={() => move(1)}
            className="grid size-9 place-items-center rounded-lg border border-border hover:bg-muted"
            aria-label="Next month"
          >
            <ChevronRight className="size-4 rtl:rotate-180" />
          </button>
        </div>
      </div>

      {isLoading ? (
        <Skeleton className="h-96 rounded-xl" />
      ) : (
        <div className="overflow-hidden rounded-xl border border-border bg-card">
          <div className="grid grid-cols-7 border-b border-border bg-muted/40 text-center text-xs font-medium text-muted-foreground">
            {weekdays.map((w) => (
              <div key={w} className="py-2">
                {w}
              </div>
            ))}
          </div>
          <div className="grid grid-cols-7">
            {cells.map((day, i) => {
              const dayIso = day ? iso(day) : null;
              const items = dayIso ? byDate.get(dayIso) ?? [] : [];
              const isToday = dayIso === todayIso;
              return (
                <div
                  key={i}
                  className={cn(
                    "min-h-20 border-b border-e border-border p-1.5 last:border-e-0 [&:nth-child(7n)]:border-e-0",
                    !day && "bg-muted/20",
                  )}
                >
                  {day && (
                    <>
                      <div
                        className={cn(
                          "mb-1 inline-grid size-6 place-items-center rounded-full text-xs",
                          isToday && "bg-primary text-primary-foreground",
                        )}
                      >
                        {day}
                      </div>
                      <div className="space-y-1">
                        {items.slice(0, 3).map((r) => (
                          <div
                            key={r.id}
                            title={r.title}
                            className="truncate rounded px-1 py-0.5 text-[11px]"
                            style={{
                              backgroundColor: (r.category?.color ?? "#4F6BED") + "22",
                              color: r.category?.color ?? "#4F6BED",
                            }}
                          >
                            {r.title}
                          </div>
                        ))}
                        {items.length > 3 && (
                          <div className="px-1 text-[10px] text-muted-foreground">
                            +{items.length - 3}
                          </div>
                        )}
                      </div>
                    </>
                  )}
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
}
