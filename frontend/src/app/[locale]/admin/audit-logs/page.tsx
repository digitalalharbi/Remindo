"use client";

import { useTranslations, useLocale } from "next-intl";
import { useAdminList } from "@/lib/hooks";
import { AdminTable } from "@/components/admin/admin-table";

interface AuditLog extends Record<string, unknown> {
  action: string;
  user?: string;
  subject_type: string;
  created_at: string;
}

export default function AdminAuditLogsPage() {
  const t = useTranslations("admin");
  const locale = useLocale();
  const { data, isLoading } = useAdminList<AuditLog>("audit-logs");
  const dateFmt = new Intl.DateTimeFormat(locale, { dateStyle: "medium", timeStyle: "short" });

  return (
    <AdminTable<AuditLog>
      title={t("nav.auditLogs")}
      isLoading={isLoading}
      empty={t("empty")}
      rows={data?.data ?? []}
      columns={[
        { key: "action", header: t("fields.action"), render: (l) => <span className="font-medium">{l.action}</span> },
        { key: "user", header: t("fields.user") },
        { key: "subject_type", header: t("fields.subject") },
        { key: "created_at", header: t("fields.date"), render: (l) => dateFmt.format(new Date(l.created_at)) },
      ]}
    />
  );
}
