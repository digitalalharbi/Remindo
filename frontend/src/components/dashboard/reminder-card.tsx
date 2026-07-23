"use client";

import { useState } from "react";
import { useTranslations, useLocale } from "next-intl";
import { CheckCircle2, RefreshCw, MoreHorizontal, Trash2, Archive } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import {
  useCompleteReminder,
  useDeleteReminder,
  useArchiveReminder,
} from "@/lib/hooks";
import { useToast } from "@/components/ui/toast";
import { cn } from "@/lib/utils";
import type { Reminder } from "@/lib/types";
import { RenewReminderDialog } from "./renew-reminder-dialog";

export function ReminderCard({ reminder }: { reminder: Reminder }) {
  const t = useTranslations("app.reminders");
  const tc = useTranslations("common");
  const locale = useLocale();
  const complete = useCompleteReminder();
  const del = useDeleteReminder();
  const archive = useArchiveReminder();
  const { toast } = useToast();
  const [menuOpen, setMenuOpen] = useState(false);
  const [renewOpen, setRenewOpen] = useState(false);

  const days = reminder.days_until_expiry;
  const status =
    reminder.status === "completed"
      ? { variant: "success" as const, label: t("complete") }
      : days < 0
        ? { variant: "danger" as const, label: t("overdueBy", { days: Math.abs(days) }) }
        : days === 0
          ? { variant: "warning" as const, label: t("dueToday") }
          : { variant: days <= 7 ? ("warning" as const) : ("neutral" as const), label: t("daysLeft", { days }) };

  const catName = reminder.category
    ? typeof reminder.category.name === "string"
      ? reminder.category.name
      : reminder.category.name[locale] ?? reminder.category.name.en
    : null;

  const dateFmt = new Intl.DateTimeFormat(locale, { dateStyle: "medium" });

  return (
    <>
      <div className="group flex items-center gap-3 rounded-xl border border-border bg-card px-4 py-3.5 transition-shadow hover:shadow-sm">
        {reminder.category && (
          <span
            className="size-2.5 shrink-0 rounded-full"
            style={{ backgroundColor: reminder.category.color }}
            aria-hidden
          />
        )}
        <div className="min-w-0 flex-1">
          <div className="flex items-center gap-2">
            <p className="truncate font-medium">{reminder.title}</p>
            {reminder.recurrence !== "none" && (
              <RefreshCw className="size-3 text-muted-foreground" />
            )}
          </div>
          <p className="truncate text-xs text-muted-foreground">
            {[catName, dateFmt.format(new Date(reminder.expiry_date))]
              .filter(Boolean)
              .join(" · ")}
          </p>
        </div>

        <Badge variant={status.variant}>{status.label}</Badge>

        <div className="relative">
          <button
            aria-label="Actions"
            onClick={() => setMenuOpen((o) => !o)}
            className="grid size-8 place-items-center rounded-md text-muted-foreground transition-colors hover:bg-muted"
          >
            <MoreHorizontal className="size-4" />
          </button>
          {menuOpen && (
            <>
              <div className="fixed inset-0 z-10" onClick={() => setMenuOpen(false)} />
              <div className="absolute end-0 z-20 mt-1 w-44 overflow-hidden rounded-lg border border-border bg-card p-1 shadow-lg">
                {reminder.status === "active" && (
                  <>
                    <MenuItem
                      icon={CheckCircle2}
                      label={t("complete")}
                      onClick={async () => {
                        setMenuOpen(false);
                        await complete.mutateAsync({ id: reminder.id });
                        toast(t("completedToast"));
                      }}
                    />
                    <MenuItem
                      icon={RefreshCw}
                      label={t("renew")}
                      onClick={() => {
                        setMenuOpen(false);
                        setRenewOpen(true);
                      }}
                    />
                    <MenuItem
                      icon={Archive}
                      label={t("archive")}
                      onClick={async () => {
                        setMenuOpen(false);
                        await archive.mutateAsync({ id: reminder.id });
                      }}
                    />
                  </>
                )}
                <MenuItem
                  icon={Trash2}
                  label={tc("delete")}
                  danger
                  onClick={async () => {
                    setMenuOpen(false);
                    await del.mutateAsync(reminder.id);
                    toast(t("deletedToast"));
                  }}
                />
              </div>
            </>
          )}
        </div>
      </div>

      <RenewReminderDialog
        reminder={reminder}
        open={renewOpen}
        onClose={() => setRenewOpen(false)}
      />
    </>
  );
}

function MenuItem({
  icon: Icon,
  label,
  onClick,
  danger,
}: {
  icon: typeof CheckCircle2;
  label: string;
  onClick: () => void;
  danger?: boolean;
}) {
  return (
    <button
      onClick={onClick}
      className={cn(
        "flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-muted",
        danger && "text-danger",
      )}
    >
      <Icon className="size-4" />
      {label}
    </button>
  );
}
