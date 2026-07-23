"use client";

import { useTranslations } from "next-intl";
import { Users } from "lucide-react";
import { useTeam } from "@/lib/hooks";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";

export default function TeamPage() {
  const t = useTranslations("app.team");
  const tn = useTranslations("app.nav");
  const { data, isLoading } = useTeam();
  const members = data?.data ?? [];
  const seats = (data?.meta?.user_limit as number) ?? 1;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold tracking-tight">{tn("team")}</h1>
        <span className="text-sm text-muted-foreground">
          {t("seats", { used: members.length, total: seats })}
        </span>
      </div>

      {isLoading ? (
        <div className="space-y-2">
          {Array.from({ length: 3 }).map((_, i) => (
            <Skeleton key={i} className="h-14 rounded-xl" />
          ))}
        </div>
      ) : members.length === 0 ? (
        <div className="rounded-xl border border-dashed border-border bg-card/50 px-6 py-12 text-center text-sm text-muted-foreground">
          <Users className="mx-auto mb-3 size-6" />
          {t("empty")}
        </div>
      ) : (
        <div className="overflow-hidden rounded-xl border border-border">
          {members.map((m, i) => (
            <div
              key={m.id}
              className={`flex items-center gap-3 px-4 py-3 ${i > 0 ? "border-t border-border" : ""}`}
            >
              <div className="grid size-9 place-items-center rounded-full bg-primary/10 text-sm font-medium text-primary">
                {m.name.charAt(0).toUpperCase()}
              </div>
              <div className="min-w-0 flex-1">
                <p className="truncate text-sm font-medium">{m.name}</p>
                <p className="truncate text-xs text-muted-foreground">{m.email}</p>
              </div>
              <Badge variant={m.role === "owner" ? "primary" : "neutral"}>
                {t(`role_${m.role}` as "role_owner" | "role_admin" | "role_member")}
              </Badge>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
