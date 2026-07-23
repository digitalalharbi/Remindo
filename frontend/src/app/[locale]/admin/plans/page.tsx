"use client";

import { useState } from "react";
import { useTranslations, useLocale } from "next-intl";
import { Plus, Pencil, Trash2 } from "lucide-react";
import { useAdminList, useAdminAction } from "@/lib/hooks";
import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { Dialog } from "@/components/ui/dialog";
import { ConfirmDialog } from "@/components/ui/confirm-dialog";
import { formatPrice } from "@/lib/utils";

interface AdminPlan extends Record<string, unknown> {
  id: string;
  key: string;
  name: Record<string, string>;
  price_monthly: number;
  price_yearly: number;
  currency: string;
  reminder_limit: number;
  user_limit: number;
  ai_operations_limit: number;
  is_active: boolean;
}

type PlanForm = {
  key: string;
  name: Record<string, string>;
  price_monthly: number;
  price_yearly: number;
  currency: string;
  reminder_limit: number;
  user_limit: number;
  ai_operations_limit: number;
  features: string[];
  is_active: boolean;
};

const blank: PlanForm = {
  key: "",
  name: { en: "" },
  price_monthly: 0,
  price_yearly: 0,
  currency: "SAR",
  reminder_limit: 50,
  user_limit: 1,
  ai_operations_limit: 0,
  features: [] as string[],
  is_active: true,
};

export default function AdminPlansPage() {
  const t = useTranslations("admin");
  const tc = useTranslations("common");
  const locale = useLocale();
  const { data, isLoading } = useAdminList<AdminPlan>("plans");
  const action = useAdminAction(["plans"]);

  const [editing, setEditing] = useState<AdminPlan | null>(null);
  const [form, setForm] = useState<PlanForm | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<AdminPlan | null>(null);

  const openNew = () => {
    setEditing(null);
    setForm({ ...blank });
  };
  const openEdit = (p: AdminPlan) => {
    setEditing(p);
    setForm({
      key: p.key,
      name: p.name,
      price_monthly: p.price_monthly,
      price_yearly: p.price_yearly,
      currency: p.currency,
      reminder_limit: p.reminder_limit,
      user_limit: p.user_limit,
      ai_operations_limit: p.ai_operations_limit,
      features: [],
      is_active: p.is_active,
    });
  };

  const save = async () => {
    if (!form) return;
    if (editing) {
      await action.mutateAsync({ method: "patch", path: `plans/${editing.id}`, body: form });
    } else {
      await action.mutateAsync({ method: "post", path: "plans", body: form });
    }
    setForm(null);
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold tracking-tight">{t("nav.plans")}</h1>
        <Button size="sm" onClick={openNew}>
          <Plus className="size-4" />
          {t("actions.newPlan")}
        </Button>
      </div>

      {isLoading ? (
        <Skeleton className="h-64 rounded-xl" />
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {(data?.data ?? []).map((p) => (
            <div key={p.id} className="flex flex-col rounded-xl border border-border bg-card p-4">
              <div className="flex items-center justify-between">
                <span className="font-semibold capitalize">{p.name?.[locale] ?? p.name?.en ?? p.key}</span>
                <Badge variant={p.is_active ? "success" : "neutral"}>
                  {p.is_active ? t("actions.active") : t("actions.inactive")}
                </Badge>
              </div>
              <div className="mt-2 text-lg font-semibold">
                {formatPrice(p.price_monthly, p.currency, locale)}
                <span className="text-xs font-normal text-muted-foreground">/mo</span>
              </div>
              <div className="mt-1 text-xs text-muted-foreground">
                {p.reminder_limit === -1 ? "∞" : p.reminder_limit} reminders · {p.user_limit} users
              </div>
              <div className="mt-4 flex gap-2">
                <button onClick={() => openEdit(p)} className="inline-flex items-center gap-1 text-sm text-primary hover:opacity-70">
                  <Pencil className="size-3.5" />
                  {t("actions.edit")}
                </button>
                <button
                  onClick={() => action.mutate({ method: "post", path: `plans/${p.id}/toggle` })}
                  className="text-sm text-muted-foreground hover:text-foreground"
                >
                  {p.is_active ? t("actions.disabled") : t("actions.enabled")}
                </button>
                {p.key !== "free" && (
                  <button onClick={() => setDeleteTarget(p)} className="ms-auto text-danger hover:opacity-70" aria-label="Delete">
                    <Trash2 className="size-4" />
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      <Dialog
        open={!!form}
        onClose={() => setForm(null)}
        title={editing ? t("actions.edit") : t("actions.newPlan")}
      >
        {form && (
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1.5">
                <Label>{t("actions.key")}</Label>
                <Input value={form.key} disabled={!!editing} onChange={(e) => setForm({ ...form, key: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label>{t("fields.name")} (EN)</Label>
                <Input value={form.name.en} onChange={(e) => setForm({ ...form, name: { ...form.name, en: e.target.value } })} />
              </div>
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1.5">
                <Label>{t("fields.monthly")} (minor)</Label>
                <Input type="number" value={form.price_monthly} onChange={(e) => setForm({ ...form, price_monthly: Number(e.target.value) })} />
              </div>
              <div className="space-y-1.5">
                <Label>{t("fields.yearly")} (minor)</Label>
                <Input type="number" value={form.price_yearly} onChange={(e) => setForm({ ...form, price_yearly: Number(e.target.value) })} />
              </div>
            </div>
            <div className="grid grid-cols-3 gap-3">
              <div className="space-y-1.5">
                <Label>{t("fields.currency")}</Label>
                <Input value={form.currency} maxLength={3} onChange={(e) => setForm({ ...form, currency: e.target.value.toUpperCase() })} />
              </div>
              <div className="space-y-1.5">
                <Label>{t("fields.reminders")}</Label>
                <Input type="number" value={form.reminder_limit} onChange={(e) => setForm({ ...form, reminder_limit: Number(e.target.value) })} />
              </div>
              <div className="space-y-1.5">
                <Label>{t("fields.users")}</Label>
                <Input type="number" value={form.user_limit} onChange={(e) => setForm({ ...form, user_limit: Number(e.target.value) })} />
              </div>
            </div>
            <div className="flex justify-end gap-2">
              <Button variant="ghost" onClick={() => setForm(null)}>{tc("cancel")}</Button>
              <Button onClick={save} disabled={!form.key || !form.name.en || action.isPending}>
                {t("actions.save")}
              </Button>
            </div>
          </div>
        )}
      </Dialog>

      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={async () => {
          if (deleteTarget) await action.mutateAsync({ method: "delete", path: `plans/${deleteTarget.id}` });
          setDeleteTarget(null);
        }}
        title={t("actions.delete")}
        message={t("actions.confirmDeletePlan")}
        loading={action.isPending}
      />
    </div>
  );
}
