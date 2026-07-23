"use client";

import { useTranslations } from "next-intl";
import { useAdminList } from "@/lib/hooks";
import { AdminTable } from "@/components/admin/admin-table";
import { Badge } from "@/components/ui/badge";

interface AdminOrg extends Record<string, unknown> {
  name: string;
  type: string;
  currency: string;
  plan?: string;
  members: number;
  created_at: string;
}

export default function AdminOrganizationsPage() {
  const t = useTranslations("admin");
  const { data, isLoading } = useAdminList<AdminOrg>("organizations");

  return (
    <AdminTable<AdminOrg>
      title={t("nav.organizations")}
      isLoading={isLoading}
      empty={t("empty")}
      rows={data?.data ?? []}
      columns={[
        { key: "name", header: t("fields.name") },
        { key: "type", header: t("fields.type") },
        {
          key: "plan",
          header: t("fields.plan"),
          render: (o) => <Badge variant="neutral">{o.plan ?? "free"}</Badge>,
        },
        { key: "members", header: t("fields.members") },
        { key: "currency", header: t("fields.currency") },
        { key: "created_at", header: t("fields.created") },
      ]}
    />
  );
}
