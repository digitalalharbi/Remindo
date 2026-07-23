"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { Plus, Trash2, Pencil } from "lucide-react";
import { useAdminList, useAdminAction } from "@/lib/hooks";
import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { Dialog } from "@/components/ui/dialog";
import { ConfirmDialog } from "@/components/ui/confirm-dialog";
import { locales } from "@/i18n/routing";

interface Faq extends Record<string, unknown> {
  id: string;
  question: Record<string, string>;
  answer: Record<string, string>;
  category: string;
  published: boolean;
}

const emptyLocalized = () => Object.fromEntries(locales.map((l) => [l, ""])) as Record<string, string>;

export default function AdminFaqsPage() {
  const t = useTranslations("admin");
  const tc = useTranslations("common");
  const { data, isLoading } = useAdminList<Faq>("faqs");
  const action = useAdminAction(["faqs"]);

  const [form, setForm] = useState<{ id?: string; question: Record<string, string>; answer: Record<string, string> } | null>(null);
  const [deleteId, setDeleteId] = useState<string | null>(null);

  const save = async () => {
    if (!form) return;
    const body = { question: form.question, answer: form.answer, published: true };
    if (form.id) await action.mutateAsync({ method: "patch", path: `faqs/${form.id}`, body });
    else await action.mutateAsync({ method: "post", path: "faqs", body });
    setForm(null);
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold tracking-tight">{t("nav.faqs")}</h1>
        <Button size="sm" onClick={() => setForm({ question: emptyLocalized(), answer: emptyLocalized() })}>
          <Plus className="size-4" />
          {t("actions.newFaq")}
        </Button>
      </div>

      {isLoading ? (
        <Skeleton className="h-40 rounded-xl" />
      ) : (
        <div className="space-y-2">
          {(data?.data ?? []).map((f) => (
            <div key={f.id} className="flex items-start gap-3 rounded-xl border border-border bg-card px-4 py-3">
              <div className="min-w-0 flex-1">
                <p className="font-medium">{f.question?.en}</p>
                <p className="truncate text-sm text-muted-foreground">{f.answer?.en}</p>
              </div>
              <button
                onClick={() => setForm({ id: f.id, question: { ...emptyLocalized(), ...f.question }, answer: { ...emptyLocalized(), ...f.answer } })}
                className="text-primary hover:opacity-70"
                aria-label="Edit"
              >
                <Pencil className="size-4" />
              </button>
              <button onClick={() => setDeleteId(f.id)} className="text-danger hover:opacity-70" aria-label="Delete">
                <Trash2 className="size-4" />
              </button>
            </div>
          ))}
        </div>
      )}

      <Dialog open={!!form} onClose={() => setForm(null)} title={form?.id ? t("actions.edit") : t("actions.newFaq")} className="max-w-2xl">
        {form && (
          <div className="max-h-[70vh] space-y-4 overflow-y-auto">
            {locales.map((l) => (
              <div key={l} className="space-y-2 rounded-lg border border-border p-3">
                <p className="text-xs font-semibold uppercase text-muted-foreground">{l}</p>
                <div className="space-y-1.5">
                  <Label>{t("actions.question")}</Label>
                  <Input
                    value={form.question[l] ?? ""}
                    onChange={(e) => setForm({ ...form, question: { ...form.question, [l]: e.target.value } })}
                  />
                </div>
                <div className="space-y-1.5">
                  <Label>{t("actions.answer")}</Label>
                  <textarea
                    rows={2}
                    className="w-full rounded-lg border border-input bg-background p-2.5 text-sm"
                    value={form.answer[l] ?? ""}
                    onChange={(e) => setForm({ ...form, answer: { ...form.answer, [l]: e.target.value } })}
                  />
                </div>
              </div>
            ))}
            <div className="flex justify-end gap-2">
              <Button variant="ghost" onClick={() => setForm(null)}>{tc("cancel")}</Button>
              <Button onClick={save} disabled={!form.question.en || !form.answer.en || action.isPending}>
                {t("actions.save")}
              </Button>
            </div>
          </div>
        )}
      </Dialog>

      <ConfirmDialog
        open={!!deleteId}
        onClose={() => setDeleteId(null)}
        onConfirm={async () => {
          if (deleteId) await action.mutateAsync({ method: "delete", path: `faqs/${deleteId}` });
          setDeleteId(null);
        }}
        title={t("actions.delete")}
        message={t("actions.confirmDeleteFaq")}
        loading={action.isPending}
      />
    </div>
  );
}
