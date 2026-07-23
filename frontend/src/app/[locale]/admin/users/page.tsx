"use client";

import { useTranslations } from "next-intl";
import { useAdminList } from "@/lib/hooks";
import { AdminTable } from "@/components/admin/admin-table";
import { Badge } from "@/components/ui/badge";

interface AdminUser extends Record<string, unknown> {
  name: string;
  email: string;
  country?: string;
  is_super_admin: boolean;
  verified: boolean;
  created_at: string;
}

export default function AdminUsersPage() {
  const t = useTranslations("admin");
  const { data, isLoading } = useAdminList<AdminUser>("users");

  return (
    <AdminTable<AdminUser>
      title={t("nav.users")}
      isLoading={isLoading}
      empty={t("empty")}
      rows={data?.data ?? []}
      columns={[
        { key: "name", header: t("fields.name") },
        { key: "email", header: t("fields.email") },
        { key: "country", header: t("fields.country") },
        {
          key: "role",
          header: t("fields.role"),
          render: (u) =>
            u.is_super_admin ? <Badge variant="primary">Admin</Badge> : <span>—</span>,
        },
        {
          key: "verified",
          header: t("fields.verified"),
          render: (u) =>
            u.verified ? <Badge variant="success">✓</Badge> : <span>—</span>,
        },
        { key: "created_at", header: t("fields.created") },
      ]}
    />
  );
}
