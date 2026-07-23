"use client";

import { useTranslations, useLocale } from "next-intl";
import { useAdminList } from "@/lib/hooks";
import { AdminTable } from "@/components/admin/admin-table";
import { Badge } from "@/components/ui/badge";
import { formatPrice } from "@/lib/utils";

interface AdminPlan extends Record<string, unknown> {
  key: string;
  price_monthly: number;
  price_yearly: number;
  currency: string;
  reminder_limit: number;
  user_limit: number;
  ai_operations_limit: number;
  is_active: boolean;
}

export default function AdminPlansPage() {
  const t = useTranslations("admin");
  const locale = useLocale();
  const { data, isLoading } = useAdminList<AdminPlan>("plans");

  return (
    <AdminTable<AdminPlan>
      title={t("nav.plans")}
      isLoading={isLoading}
      empty={t("empty")}
      rows={data?.data ?? []}
      columns={[
        { key: "key", header: t("fields.plan"), render: (p) => <span className="font-medium capitalize">{p.key}</span> },
        { key: "price_monthly", header: t("fields.monthly"), render: (p) => formatPrice(p.price_monthly, p.currency, locale) },
        { key: "price_yearly", header: t("fields.yearly"), render: (p) => formatPrice(p.price_yearly, p.currency, locale) },
        { key: "reminder_limit", header: t("fields.reminders"), render: (p) => (p.reminder_limit === -1 ? "∞" : p.reminder_limit) },
        { key: "user_limit", header: t("fields.users") },
        { key: "ai_operations_limit", header: "AI" },
        {
          key: "is_active",
          header: t("fields.status"),
          render: (p) => (p.is_active ? <Badge variant="success">✓</Badge> : <Badge variant="neutral">—</Badge>),
        },
      ]}
    />
  );
}
