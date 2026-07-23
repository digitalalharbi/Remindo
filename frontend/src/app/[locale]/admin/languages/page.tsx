"use client";

import { useTranslations } from "next-intl";
import { useAdminList, useAdminAction } from "@/lib/hooks";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";

interface Lang extends Record<string, unknown> {
  id: string;
  code: string;
  name: string;
  english_name: string;
  rtl: boolean;
  enabled: boolean;
  is_default: boolean;
}

export default function AdminLanguagesPage() {
  const t = useTranslations("admin");
  const { data, isLoading } = useAdminList<Lang>("languages");
  const action = useAdminAction(["languages"]);

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-semibold tracking-tight">{t("nav.languages")}</h1>
      {isLoading ? (
        <Skeleton className="h-40 rounded-xl" />
      ) : (
        <div className="overflow-hidden rounded-xl border border-border">
          {(data?.data ?? []).map((lang, i) => (
            <div
              key={lang.id}
              className={`flex items-center justify-between gap-4 px-4 py-3.5 ${i > 0 ? "border-t border-border" : ""}`}
            >
              <div className="flex items-center gap-3">
                <span className="font-medium">{lang.name}</span>
                <span className="text-xs text-muted-foreground">{lang.english_name}</span>
                {lang.rtl && <Badge variant="neutral">RTL</Badge>}
                {lang.is_default && <Badge variant="primary">default</Badge>}
              </div>
              <button
                role="switch"
                aria-checked={lang.enabled}
                onClick={() =>
                  !lang.is_default &&
                  action.mutate({ method: "post", path: `languages/${lang.id}/toggle` })
                }
                disabled={lang.is_default || action.isPending}
                className={`relative h-6 w-11 shrink-0 rounded-full transition-colors disabled:opacity-50 ${lang.enabled ? "bg-primary" : "bg-muted"}`}
              >
                <span
                  className={`absolute top-0.5 size-5 rounded-full bg-white shadow transition-all ${lang.enabled ? "start-5.5 rtl:start-0.5" : "start-0.5 rtl:start-5.5"}`}
                />
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
