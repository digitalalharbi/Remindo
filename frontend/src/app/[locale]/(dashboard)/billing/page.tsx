"use client";

import { useState } from "react";
import { useTranslations, useLocale } from "next-intl";
import { Check, Loader2 } from "lucide-react";
import {
  useSubscription,
  useSubscribe,
  usePlans,
} from "@/lib/hooks";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { useToast } from "@/components/ui/toast";
import { formatPrice, cn } from "@/lib/utils";
import type { Plan } from "@/lib/types";

function planName(plan: Plan, locale: string) {
  return typeof plan.name === "string" ? plan.name : plan.name[locale] ?? plan.name.en;
}

export default function SubscriptionPage() {
  const t = useTranslations("app.subscription");
  const tp = useTranslations("pricing");
  const locale = useLocale();
  const { toast } = useToast();

  const { data: sub, isLoading } = useSubscription();
  const { data: plans } = usePlans();
  const subscribe = useSubscribe();
  const [interval, setInterval] = useState<"monthly" | "yearly">("monthly");

  const currentKey = sub?.plan?.key;

  const onSubscribe = async (planKey: string) => {
    try {
      await subscribe.mutateAsync({ plan_key: planKey, interval });
      toast(t("activated"), "success");
    } catch {
      toast(tp("cta"), "error");
    }
  };

  return (
    <div className="space-y-8">
      <h1 className="text-2xl font-semibold tracking-tight">{t("title")}</h1>

      {/* Current plan */}
      <div className="rounded-xl border border-border bg-card p-5">
        <div className="text-sm text-muted-foreground">{t("currentPlan")}</div>
        {isLoading ? (
          <Skeleton className="mt-2 h-7 w-32" />
        ) : (
          <div className="mt-1 flex items-center gap-2">
            <span className="text-xl font-semibold">
              {sub?.plan ? planName(sub.plan, locale) : "—"}
            </span>
            {sub?.subscription?.current_period_end && (
              <span className="text-sm text-muted-foreground">
                {t("renewsOn", { date: sub.subscription.current_period_end })}
              </span>
            )}
          </div>
        )}
      </div>

      {/* Interval toggle */}
      <div className="inline-flex items-center gap-1 rounded-lg border border-border bg-card p-1">
        {(["monthly", "yearly"] as const).map((iv) => (
          <button
            key={iv}
            onClick={() => setInterval(iv)}
            className={cn(
              "rounded-md px-4 py-1.5 text-sm font-medium transition-colors",
              interval === iv ? "bg-primary text-primary-foreground" : "text-muted-foreground",
            )}
          >
            {tp(iv)}
          </button>
        ))}
      </div>

      {/* Plans */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {plans?.map((plan) => {
          const isCurrent = plan.key === currentKey;
          const price = interval === "yearly" ? plan.price_yearly : plan.price_monthly;
          return (
            <div
              key={plan.id}
              className={cn(
                "flex flex-col rounded-2xl border bg-card p-5",
                isCurrent ? "border-primary ring-1 ring-primary" : "border-border",
              )}
            >
              <div className="flex items-center justify-between">
                <h3 className="font-semibold">{planName(plan, locale)}</h3>
                {isCurrent && <Badge variant="primary">{t("current")}</Badge>}
              </div>
              <div className="mt-3 text-2xl font-semibold tracking-tight">
                {formatPrice(price, sub?.currency ?? plan.currency, locale)}
                {price > 0 && (
                  <span className="text-sm font-normal text-muted-foreground">
                    {interval === "yearly" ? tp("perYear") : tp("perMonth")}
                  </span>
                )}
              </div>
              <div className="mt-2 text-xs text-muted-foreground">
                {plan.reminder_limit === -1
                  ? tp("unlimited")
                  : tp("remindersLabel", { count: plan.reminder_limit })}
              </div>
              <Button
                className="mt-5 w-full"
                variant={isCurrent ? "secondary" : "primary"}
                disabled={isCurrent || subscribe.isPending}
                onClick={() => onSubscribe(plan.key)}
              >
                {subscribe.isPending && subscribe.variables?.plan_key === plan.key ? (
                  <Loader2 className="size-4 animate-spin" />
                ) : isCurrent ? (
                  <Check className="size-4" />
                ) : (
                  t("subscribe")
                )}
              </Button>
            </div>
          );
        })}
      </div>

      {/* Invoices */}
      <div>
        <h2 className="mb-3 text-sm font-semibold text-muted-foreground">
          {t("invoices")}
        </h2>
        {!sub || sub.invoices.length === 0 ? (
          <div className="rounded-xl border border-dashed border-border bg-card/50 px-6 py-10 text-center text-sm text-muted-foreground">
            {t("noInvoices")}
          </div>
        ) : (
          <div className="overflow-hidden rounded-xl border border-border">
            <table className="w-full text-sm">
              <thead className="bg-muted/50 text-muted-foreground">
                <tr>
                  <th className="px-4 py-2.5 text-start font-medium">{t("number")}</th>
                  <th className="px-4 py-2.5 text-start font-medium">{t("date")}</th>
                  <th className="px-4 py-2.5 text-start font-medium">{t("amount")}</th>
                  <th className="px-4 py-2.5 text-start font-medium">{t("status")}</th>
                </tr>
              </thead>
              <tbody>
                {sub.invoices.map((inv) => (
                  <tr key={inv.id} className="border-t border-border">
                    <td className="px-4 py-2.5 font-medium">{inv.number}</td>
                    <td className="px-4 py-2.5 text-muted-foreground">{inv.issued_at}</td>
                    <td className="px-4 py-2.5">
                      {formatPrice(inv.total, inv.currency, locale)}
                    </td>
                    <td className="px-4 py-2.5">
                      <Badge variant="success">{inv.status}</Badge>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
