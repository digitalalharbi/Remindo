"use client";

import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";
import { api, ensureCsrf, type ApiEnvelope } from "@/lib/api";
import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { useToast } from "@/components/ui/toast";
import { locales } from "@/i18n/routing";

// Editable SEO defaults per locale (title suffix + default description).
type SeoRow = { group: string; key: string; value: Record<string, string> };

export default function AdminContentPage() {
  const t = useTranslations("admin");
  const { toast } = useToast();
  const [values, setValues] = useState<Record<string, Record<string, string>>>({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      const { data } = await api.get<ApiEnvelope<SeoRow[]>>("/admin/settings", {
        params: { group: "seo" },
      });
      const map: Record<string, Record<string, string>> = {};
      for (const row of data.data) map[row.key] = row.value ?? {};
      setValues(map);
      setLoading(false);
    })().catch(() => setLoading(false));
  }, []);

  const setVal = (key: string, locale: string, v: string) =>
    setValues((prev) => ({ ...prev, [key]: { ...(prev[key] ?? {}), [locale]: v } }));

  const save = async (key: string) => {
    await ensureCsrf();
    await api.post("/admin/settings", { group: "seo", key, value: values[key] ?? {} });
    toast(t("actions.saved"), "success");
  };

  const fields = [
    { key: "default_title", label: "Default title" },
    { key: "default_description", label: "Default description" },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold tracking-tight">{t("nav.content")}</h1>
        <p className="mt-1 text-sm text-muted-foreground">SEO defaults per language.</p>
      </div>

      {loading ? (
        <div className="h-40 animate-pulse rounded-xl bg-muted" />
      ) : (
        fields.map((f) => (
          <div key={f.key} className="space-y-3 rounded-xl border border-border bg-card p-5">
            <h2 className="text-sm font-semibold">{f.label}</h2>
            <div className="grid gap-3 sm:grid-cols-2">
              {locales.map((l) => (
                <div key={l} className="space-y-1.5">
                  <Label className="uppercase">{l}</Label>
                  <Input value={values[f.key]?.[l] ?? ""} onChange={(e) => setVal(f.key, l, e.target.value)} />
                </div>
              ))}
            </div>
            <div className="flex justify-end">
              <Button size="sm" onClick={() => save(f.key)}>{t("actions.save")}</Button>
            </div>
          </div>
        ))
      )}
    </div>
  );
}
