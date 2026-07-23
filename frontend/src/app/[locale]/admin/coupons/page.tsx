"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { Plus, Trash2 } from "lucide-react";
import { useAdminList, useAdminAction } from "@/lib/hooks";
import { AdminTable } from "@/components/admin/admin-table";
import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Dialog } from "@/components/ui/dialog";
import { ConfirmDialog } from "@/components/ui/confirm-dialog";

interface Coupon extends Record<string, unknown> {
  id: string;
  code: string;
  type: "percent" | "fixed";
  value: number;
  duration: string;
  times_redeemed: number;
  is_active: boolean;
}

export default function AdminCouponsPage() {
  const t = useTranslations("admin");
  const tc = useTranslations("common");
  const { data, isLoading } = useAdminList<Coupon>("coupons");
  const action = useAdminAction(["coupons"]);

  const [createOpen, setCreateOpen] = useState(false);
  const [deleteId, setDeleteId] = useState<string | null>(null);
  const [form, setForm] = useState({ code: "", type: "percent", value: 10, duration: "once" });

  const create = async () => {
    await action.mutateAsync({ method: "post", path: "coupons", body: form });
    setCreateOpen(false);
    setForm({ code: "", type: "percent", value: 10, duration: "once" });
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold tracking-tight">{t("nav.coupons")}</h1>
        <Button size="sm" onClick={() => setCreateOpen(true)}>
          <Plus className="size-4" />
          {t("actions.newCoupon")}
        </Button>
      </div>

      <AdminTable<Coupon>
        title=""
        isLoading={isLoading}
        empty={t("empty")}
        rows={data?.data ?? []}
        columns={[
          { key: "code", header: t("actions.code"), render: (c) => <span className="font-mono font-medium">{c.code}</span> },
          { key: "value", header: t("actions.value"), render: (c) => (c.type === "percent" ? `${c.value}%` : c.value) },
          { key: "duration", header: "Duration" },
          { key: "times_redeemed", header: "Redeemed" },
          {
            key: "is_active",
            header: t("fields.status"),
            render: (c) => <Badge variant={c.is_active ? "success" : "neutral"}>{c.is_active ? t("actions.active") : t("actions.inactive")}</Badge>,
          },
          {
            key: "actions",
            header: "",
            render: (c) => (
              <button onClick={() => setDeleteId(c.id)} className="text-danger hover:opacity-70" aria-label="Delete">
                <Trash2 className="size-4" />
              </button>
            ),
          },
        ]}
      />

      <Dialog open={createOpen} onClose={() => setCreateOpen(false)} title={t("actions.newCoupon")}>
        <div className="space-y-4">
          <div className="space-y-1.5">
            <Label>{t("actions.code")}</Label>
            <Input value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value.toUpperCase() })} placeholder="LAUNCH50" />
          </div>
          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-1.5">
              <Label>{t("actions.discountType")}</Label>
              <select
                className="h-11 w-full rounded-lg border border-input bg-background px-3 text-sm"
                value={form.type}
                onChange={(e) => setForm({ ...form, type: e.target.value })}
              >
                <option value="percent">{t("actions.percent")}</option>
                <option value="fixed">{t("actions.fixed")}</option>
              </select>
            </div>
            <div className="space-y-1.5">
              <Label>{t("actions.value")}</Label>
              <Input type="number" value={form.value} onChange={(e) => setForm({ ...form, value: Number(e.target.value) })} />
            </div>
          </div>
          <div className="flex justify-end gap-2">
            <Button variant="ghost" onClick={() => setCreateOpen(false)}>{tc("cancel")}</Button>
            <Button onClick={create} disabled={!form.code || action.isPending}>{t("actions.create")}</Button>
          </div>
        </div>
      </Dialog>

      <ConfirmDialog
        open={!!deleteId}
        onClose={() => setDeleteId(null)}
        onConfirm={async () => {
          if (deleteId) await action.mutateAsync({ method: "delete", path: `coupons/${deleteId}` });
          setDeleteId(null);
        }}
        title={t("actions.delete")}
        message={t("actions.confirmDeleteCoupon")}
        loading={action.isPending}
      />
    </div>
  );
}
