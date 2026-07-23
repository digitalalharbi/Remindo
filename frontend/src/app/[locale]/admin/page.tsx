"use client";

import { useTranslations, useLocale } from "next-intl";
import { Users, Building2, CreditCard, BellRing, Receipt, Sparkles } from "lucide-react";
import { useAdminStats } from "@/lib/hooks";
import { Skeleton } from "@/components/ui/skeleton";
import { formatPrice } from "@/lib/utils";

export default function AdminOverviewPage() {
  const t = useTranslations("admin.stats");
  const tn = useTranslations("admin.nav");
  const locale = useLocale();
  const { data, isLoading } = useAdminStats();

  const revenue = data
    ? formatPrice(Number(data.revenue_total ?? 0), String(data.revenue_currency ?? "SAR"), locale)
    : "—";

  const tiles = [
    { key: "users", icon: Users, value: data?.users },
    { key: "organizations", icon: Building2, value: data?.organizations },
    { key: "subscriptions", icon: CreditCard, value: data?.active_subscriptions },
    { key: "reminders", icon: BellRing, value: data?.reminders },
    { key: "revenue", icon: Receipt, value: revenue, raw: true },
    { key: "ai", icon: Sparkles, value: data?.ai_operations },
  ] as const;

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold tracking-tight">{tn("overview")}</h1>
      <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
        {tiles.map((tile) => (
          <div key={tile.key} className="rounded-xl border border-border bg-card p-5">
            <tile.icon className="size-5 text-muted-foreground" />
            {isLoading ? (
              <Skeleton className="mt-3 h-8 w-16" />
            ) : (
              <div className="mt-3 text-3xl font-semibold tracking-tight">
                {tile.value ?? 0}
              </div>
            )}
            <div className="mt-1 text-sm text-muted-foreground">{t(tile.key)}</div>
          </div>
        ))}
      </div>
    </div>
  );
}
