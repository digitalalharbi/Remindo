import { getTranslations } from "next-intl/server";
import { BellRing, Search } from "lucide-react";

/** A calm, static preview of the real app UI — shown in the marketing hero. */
export async function AppPreview({ locale }: { locale: string }) {
  const t = await getTranslations("categories");
  const td = await getTranslations("app.dashboard");

  const rows = [
    { name: t("insurance"), item: locale === "ar" ? "تأمين السيارة" : "Car insurance", days: 20, tone: "warning" as const },
    { name: t("licenses"), item: locale === "ar" ? "السجل التجاري" : "Commercial registration", days: 45, tone: "neutral" as const },
    { name: t("rentals"), item: locale === "ar" ? "عقد المكتب" : "Office lease", days: 5, tone: "danger" as const },
    { name: t("subscriptions"), item: locale === "ar" ? "تجديد الدومين" : "Domain renewal", days: 60, tone: "neutral" as const },
  ];

  const toneClass = {
    danger: "bg-danger/12 text-danger",
    warning: "bg-warning/15 text-warning-foreground",
    neutral: "bg-muted text-muted-foreground",
  };

  return (
    <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-lg">
      {/* window chrome */}
      <div className="flex items-center gap-2 border-b border-border bg-muted/40 px-4 py-3">
        <span className="size-3 rounded-full bg-danger/60" />
        <span className="size-3 rounded-full bg-warning/60" />
        <span className="size-3 rounded-full bg-success/60" />
        <div className="ms-3 flex items-center gap-2 rounded-md border border-border bg-background px-2.5 py-1 text-xs text-muted-foreground">
          <Search className="size-3" />
          app.remindo.me
        </div>
      </div>

      <div className="grid gap-0 sm:grid-cols-[180px_1fr]">
        {/* sidebar */}
        <aside className="hidden border-e border-border p-4 sm:block">
          <div className="flex items-center gap-2 font-semibold">
            <span className="grid size-7 place-items-center rounded-lg bg-primary text-primary-foreground">
              <BellRing className="size-4" />
            </span>
            Remindo
          </div>
          <nav className="mt-5 space-y-1 text-sm">
            <div className="rounded-md bg-primary/10 px-3 py-2 font-medium text-primary">
              {td("overdue") /* using dashboard keys for calm labels */}
            </div>
            <div className="px-3 py-2 text-muted-foreground">{td("thisWeek")}</div>
            <div className="px-3 py-2 text-muted-foreground">{td("thisMonth")}</div>
          </nav>
        </aside>

        {/* main */}
        <div className="p-5">
          <div className="grid grid-cols-3 gap-3">
            {[
              { label: td("overdue"), value: "1", tone: "danger" },
              { label: td("thisWeek"), value: "2", tone: "warning" },
              { label: td("thisMonth"), value: "4", tone: "neutral" },
            ].map((s) => (
              <div key={s.label} className="rounded-xl border border-border p-3">
                <div className="text-2xl font-semibold">{s.value}</div>
                <div className="mt-0.5 text-xs text-muted-foreground">{s.label}</div>
              </div>
            ))}
          </div>

          <div className="mt-4 space-y-2">
            {rows.map((r, i) => (
              <div
                key={i}
                className="flex items-center justify-between rounded-xl border border-border px-3.5 py-3"
              >
                <div className="min-w-0">
                  <div className="truncate text-sm font-medium">{r.item}</div>
                  <div className="truncate text-xs text-muted-foreground">{r.name}</div>
                </div>
                <span
                  className={`shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium ${toneClass[r.tone]}`}
                >
                  {locale === "ar" ? `باقي ${r.days} يوم` : `${r.days} days`}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
