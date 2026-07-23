"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { Ban, RotateCcw } from "lucide-react";
import { useAdminList, useAdminAction } from "@/lib/hooks";
import { AdminTable } from "@/components/admin/admin-table";
import { Badge } from "@/components/ui/badge";
import { ConfirmDialog } from "@/components/ui/confirm-dialog";

interface AdminOrg extends Record<string, unknown> {
  id: string;
  name: string;
  type: string;
  currency: string;
  plan?: string;
  members: number;
  suspended: boolean;
  created_at: string;
}

export default function AdminOrganizationsPage() {
  const t = useTranslations("admin");
  const { data, isLoading } = useAdminList<AdminOrg>("organizations");
  const action = useAdminAction(["organizations"]);
  const [target, setTarget] = useState<AdminOrg | null>(null);

  return (
    <>
      <AdminTable<AdminOrg>
        title={t("nav.organizations")}
        isLoading={isLoading}
        empty={t("empty")}
        rows={data?.data ?? []}
        columns={[
          { key: "name", header: t("fields.name") },
          {
            key: "plan",
            header: t("fields.plan"),
            render: (o) => <Badge variant="neutral">{o.plan ?? "free"}</Badge>,
          },
          { key: "members", header: t("fields.members") },
          { key: "currency", header: t("fields.currency") },
          {
            key: "status",
            header: t("fields.status"),
            render: (o) =>
              o.suspended ? <Badge variant="danger">{t("actions.suspend")}</Badge> : <Badge variant="success">✓</Badge>,
          },
          {
            key: "actions",
            header: "",
            render: (o) =>
              o.suspended ? (
                <button
                  onClick={() => action.mutate({ method: "post", path: `organizations/${o.id}/reactivate` })}
                  className="inline-flex items-center gap-1 text-sm text-primary hover:opacity-70"
                >
                  <RotateCcw className="size-3.5" />
                  {t("actions.reactivate")}
                </button>
              ) : (
                <button
                  onClick={() => setTarget(o)}
                  className="inline-flex items-center gap-1 text-sm text-danger hover:opacity-70"
                >
                  <Ban className="size-3.5" />
                  {t("actions.suspend")}
                </button>
              ),
          },
        ]}
      />

      <ConfirmDialog
        open={!!target}
        onClose={() => setTarget(null)}
        onConfirm={async () => {
          if (target) await action.mutateAsync({ method: "post", path: `organizations/${target.id}/suspend` });
          setTarget(null);
        }}
        title={t("actions.suspend")}
        message={t("actions.confirmSuspendOrg")}
        confirmLabel={t("actions.suspend")}
        loading={action.isPending}
      />
    </>
  );
}
