"use client";

import { useTranslations } from "next-intl";
import { useAdminList, useAdminAction } from "@/lib/hooks";
import { Skeleton } from "@/components/ui/skeleton";

interface Flag extends Record<string, unknown> {
  id: string;
  key: string;
  description?: string;
  enabled: boolean;
}

export default function AdminFlagsPage() {
  const t = useTranslations("admin");
  const { data, isLoading } = useAdminList<Flag>("flags");
  const action = useAdminAction(["flags"]);

  const toggle = (flag: Flag) =>
    action.mutate({ method: "post", path: "flags", body: { key: flag.key, enabled: !flag.enabled } });

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-semibold tracking-tight">{t("nav.flags")}</h1>
      {isLoading ? (
        <div className="space-y-2">
          {Array.from({ length: 5 }).map((_, i) => (
            <Skeleton key={i} className="h-14 rounded-xl" />
          ))}
        </div>
      ) : (
        <div className="overflow-hidden rounded-xl border border-border">
          {(data?.data ?? []).map((flag, i) => (
            <div
              key={flag.id}
              className={`flex items-center justify-between gap-4 px-4 py-3.5 ${i > 0 ? "border-t border-border" : ""}`}
            >
              <div className="min-w-0">
                <p className="font-mono text-sm font-medium">{flag.key}</p>
                {flag.description && (
                  <p className="truncate text-xs text-muted-foreground">{flag.description}</p>
                )}
              </div>
              <button
                role="switch"
                aria-checked={flag.enabled}
                onClick={() => toggle(flag)}
                disabled={action.isPending}
                className={`relative h-6 w-11 shrink-0 rounded-full transition-colors ${flag.enabled ? "bg-primary" : "bg-muted"}`}
              >
                <span
                  className={`absolute top-0.5 size-5 rounded-full bg-white shadow transition-all ${flag.enabled ? "start-5.5 rtl:start-0.5" : "start-0.5 rtl:start-5.5"}`}
                />
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
