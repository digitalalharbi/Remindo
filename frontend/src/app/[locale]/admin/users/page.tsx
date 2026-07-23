"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { Ban, RotateCcw } from "lucide-react";
import { useAdminList, useAdminAction } from "@/lib/hooks";
import { AdminTable } from "@/components/admin/admin-table";
import { Badge } from "@/components/ui/badge";
import { ConfirmDialog } from "@/components/ui/confirm-dialog";

interface AdminUser extends Record<string, unknown> {
  id: string;
  name: string;
  email: string;
  country?: string;
  is_super_admin: boolean;
  verified: boolean;
  suspended: boolean;
  created_at: string;
}

export default function AdminUsersPage() {
  const t = useTranslations("admin");
  const { data, isLoading } = useAdminList<AdminUser>("users");
  const action = useAdminAction(["users"]);
  const [suspendTarget, setSuspendTarget] = useState<AdminUser | null>(null);

  return (
    <>
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
            key: "status",
            header: t("fields.status"),
            render: (u) =>
              u.suspended ? (
                <Badge variant="danger">{t("actions.suspend")}</Badge>
              ) : u.is_super_admin ? (
                <Badge variant="primary">Admin</Badge>
              ) : u.verified ? (
                <Badge variant="success">✓</Badge>
              ) : (
                <span>—</span>
              ),
          },
          { key: "created_at", header: t("fields.created") },
          {
            key: "actions",
            header: "",
            render: (u) =>
              u.is_super_admin ? (
                <span className="text-muted-foreground">—</span>
              ) : u.suspended ? (
                <button
                  onClick={() => action.mutate({ method: "post", path: `users/${u.id}/reactivate` })}
                  className="inline-flex items-center gap-1 text-sm text-primary hover:opacity-70"
                >
                  <RotateCcw className="size-3.5" />
                  {t("actions.reactivate")}
                </button>
              ) : (
                <button
                  onClick={() => setSuspendTarget(u)}
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
        open={!!suspendTarget}
        onClose={() => setSuspendTarget(null)}
        onConfirm={async () => {
          if (suspendTarget)
            await action.mutateAsync({ method: "post", path: `users/${suspendTarget.id}/suspend` });
          setSuspendTarget(null);
        }}
        title={t("actions.suspend")}
        message={t("actions.confirmSuspendUser")}
        confirmLabel={t("actions.suspend")}
        loading={action.isPending}
      />
    </>
  );
}
