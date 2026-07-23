"use client";

import { useTranslations, useLocale } from "next-intl";
import { useAdminList } from "@/lib/hooks";
import { AdminTable } from "@/components/admin/admin-table";
import { Badge } from "@/components/ui/badge";
import { formatPrice } from "@/lib/utils";

interface AdminInvoice extends Record<string, unknown> {
  number: string;
  organization?: string;
  total: number;
  currency: string;
  status: string;
  provider: string;
  issued_at: string;
}

export default function AdminInvoicesPage() {
  const t = useTranslations("admin");
  const locale = useLocale();
  const { data, isLoading } = useAdminList<AdminInvoice>("invoices");

  return (
    <AdminTable<AdminInvoice>
      title={t("nav.invoices")}
      isLoading={isLoading}
      empty={t("empty")}
      rows={data?.data ?? []}
      columns={[
        { key: "number", header: t("fields.number"), render: (i) => <span className="font-medium">{i.number}</span> },
        { key: "organization", header: t("fields.organization") },
        { key: "total", header: t("fields.amount"), render: (i) => formatPrice(i.total, i.currency, locale) },
        { key: "provider", header: t("fields.provider") },
        {
          key: "status",
          header: t("fields.status"),
          render: (i) => <Badge variant={i.status === "paid" ? "success" : "neutral"}>{i.status}</Badge>,
        },
        { key: "issued_at", header: t("fields.date") },
      ]}
    />
  );
}
