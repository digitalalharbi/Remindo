"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { Check } from "lucide-react";
import { usePlans } from "@/lib/hooks";
import { formatPrice, cn } from "@/lib/utils";
import { buttonVariants } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { Link } from "@/i18n/navigation";
import type { Plan } from "@/lib/types";

function planName(plan: Plan, locale: string): string {
  if (typeof plan.name === "string") return plan.name;
  return plan.name[locale] ?? plan.name.en ?? plan.key;
}

export function PricingTable({ locale }: { locale: string }) {
  const t = useTranslations("pricing");
  const { data: plans, isLoading } = usePlans();
  const [yearly, setYearly] = useState(false);

  return (
    <div>
      <div className="mx-auto max-w-2xl text-center">
        <h1 className="text-4xl font-semibold tracking-tight sm:text-5xl">
          {t("title")}
        </h1>
        <p className="mt-4 text-lg text-muted-foreground">{t("subtitle")}</p>

        <div className="mt-8 inline-flex items-center gap-1 rounded-lg border border-border bg-card p-1">
          <button
            onClick={() => setYearly(false)}
            className={cn(
              "rounded-md px-4 py-1.5 text-sm font-medium transition-colors",
              !yearly ? "bg-primary text-primary-foreground" : "text-muted-foreground",
            )}
          >
            {t("monthly")}
          </button>
          <button
            onClick={() => setYearly(true)}
            className={cn(
              "rounded-md px-4 py-1.5 text-sm font-medium transition-colors",
              yearly ? "bg-primary text-primary-foreground" : "text-muted-foreground",
            )}
          >
            {t("yearly")}
          </button>
        </div>
      </div>

      <div className="mt-14 grid gap-6 lg:grid-cols-4">
        {isLoading &&
          Array.from({ length: 4 }).map((_, i) => (
            <Skeleton key={i} className="h-96 rounded-2xl" />
          ))}

        {plans?.map((plan) => {
          const popular = plan.key === "professional";
          const price = yearly ? plan.price_yearly : plan.price_monthly;
          const free = plan.price_monthly === 0;
          return (
            <div
              key={plan.id}
              className={cn(
                "relative flex flex-col rounded-2xl border bg-card p-6 shadow-sm",
                popular ? "border-primary ring-1 ring-primary" : "border-border",
              )}
            >
              {popular && (
                <span className="absolute -top-3 start-6 rounded-full bg-primary px-3 py-1 text-xs font-medium text-primary-foreground">
                  {t("mostPopular")}
                </span>
              )}
              <h3 className="text-lg font-semibold">{planName(plan, locale)}</h3>
              <div className="mt-4 flex items-baseline gap-1">
                <span className="text-4xl font-semibold tracking-tight">
                  {free ? formatPrice(0, plan.currency, locale) : formatPrice(price, plan.currency, locale)}
                </span>
                {!free && (
                  <span className="text-sm text-muted-foreground">
                    {yearly ? t("perYear") : t("perMonth")}
                  </span>
                )}
              </div>

              <div className="mt-4 text-sm text-muted-foreground">
                {plan.reminder_limit === -1
                  ? t("unlimited")
                  : t("remindersLabel", { count: plan.reminder_limit })}
              </div>

              <ul className="mt-6 flex-1 space-y-2.5 text-sm">
                {plan.features.slice(0, 7).map((f) => (
                  <li key={f} className="flex items-center gap-2">
                    <Check className="size-4 shrink-0 text-success" />
                    <span className="capitalize">{f.replace(/_/g, " ")}</span>
                  </li>
                ))}
              </ul>

              <Link
                href="/register"
                className={cn(
                  buttonVariants({ variant: popular ? "primary" : "secondary" }),
                  "mt-6 w-full",
                )}
              >
                {free ? t("ctaFree") : t("cta")}
              </Link>
            </div>
          );
        })}
      </div>
    </div>
  );
}
