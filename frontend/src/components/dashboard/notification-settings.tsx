"use client";

import { useTranslations, useLocale } from "next-intl";
import {
  useChannelPreferences,
  useUpdateChannelPreferences,
  useCredits,
  useBuyCredits,
} from "@/lib/hooks";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { formatPrice, cn } from "@/lib/utils";
import { useToast } from "@/components/ui/toast";

const ALL_CHANNELS = ["email", "in_app", "web_push", "sms", "whatsapp"] as const;

export function NotificationSettings() {
  const t = useTranslations("app.notifications");
  const locale = useLocale();
  const { toast } = useToast();
  const { data: pref, isLoading } = useChannelPreferences();
  const update = useUpdateChannelPreferences();
  const { data: credits } = useCredits();
  const buy = useBuyCredits();

  const enabled = (c: string) => !pref?.channels || pref.channels.includes(c);

  const toggleChannel = (c: string) => {
    const current = pref?.channels ?? [...ALL_CHANNELS];
    const next = current.includes(c) ? current.filter((x) => x !== c) : [...current, c];
    update.mutate({ channels: next });
  };

  if (isLoading) return <Skeleton className="h-40 rounded-xl" />;

  return (
    <div className="space-y-6">
      {/* Channels */}
      <div className="rounded-xl border border-border bg-card p-6">
        <h2 className="text-sm font-semibold">{t("channels")}</h2>
        <div className="mt-4 flex flex-wrap gap-2">
          {ALL_CHANNELS.map((c) => (
            <button
              key={c}
              onClick={() => toggleChannel(c)}
              className={cn(
                "rounded-full border px-3 py-1.5 text-sm transition-colors",
                enabled(c)
                  ? "border-primary bg-primary/10 text-primary"
                  : "border-border text-muted-foreground hover:bg-muted",
              )}
            >
              {t(c)}
            </button>
          ))}
        </div>

        {/* Quiet hours */}
        <div className="mt-6 border-t border-border pt-5">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-sm font-medium">{t("quietHours")}</h3>
              <p className="text-xs text-muted-foreground">{t("quietHoursHint")}</p>
            </div>
            <button
              role="switch"
              aria-checked={pref?.quiet_hours_enabled}
              onClick={() => update.mutate({ quiet_hours_enabled: !pref?.quiet_hours_enabled })}
              className={cn(
                "relative h-6 w-11 shrink-0 rounded-full transition-colors",
                pref?.quiet_hours_enabled ? "bg-primary" : "bg-muted",
              )}
            >
              <span
                className={cn(
                  "absolute top-0.5 size-5 rounded-full bg-white shadow transition-all",
                  pref?.quiet_hours_enabled ? "start-5.5 rtl:start-0.5" : "start-0.5 rtl:start-5.5",
                )}
              />
            </button>
          </div>
          {pref?.quiet_hours_enabled && (
            <div className="mt-3 flex items-center gap-3">
              <label className="flex items-center gap-2 text-sm">
                {t("from")}
                <select
                  className="h-9 rounded-lg border border-input bg-background px-2 text-sm"
                  value={pref?.quiet_start ?? 22}
                  onChange={(e) => update.mutate({ quiet_start: Number(e.target.value) })}
                >
                  {Array.from({ length: 24 }, (_, i) => (
                    <option key={i} value={i}>{String(i).padStart(2, "0")}:00</option>
                  ))}
                </select>
              </label>
              <label className="flex items-center gap-2 text-sm">
                {t("to")}
                <select
                  className="h-9 rounded-lg border border-input bg-background px-2 text-sm"
                  value={pref?.quiet_end ?? 7}
                  onChange={(e) => update.mutate({ quiet_end: Number(e.target.value) })}
                >
                  {Array.from({ length: 24 }, (_, i) => (
                    <option key={i} value={i}>{String(i).padStart(2, "0")}:00</option>
                  ))}
                </select>
              </label>
            </div>
          )}
        </div>
      </div>

      {/* Credits */}
      <div className="rounded-xl border border-border bg-card p-6">
        <h2 className="text-sm font-semibold">{t("credits")}</h2>
        <div className="mt-4 grid grid-cols-3 gap-3">
          {(["sms", "whatsapp", "ai"] as const).map((ch) => (
            <div key={ch} className="rounded-lg border border-border p-3 text-center">
              <div className="text-2xl font-semibold">{credits?.balances[ch] ?? 0}</div>
              <div className="text-xs uppercase text-muted-foreground">{ch}</div>
            </div>
          ))}
        </div>
        <div className="mt-4 space-y-2">
          {credits?.packs.map((pack) => (
            <div key={pack.id} className="flex items-center justify-between rounded-lg border border-border px-3 py-2">
              <span className="text-sm">
                {pack.name} <span className="text-muted-foreground">· {pack.channel}</span>
              </span>
              <Button
                size="sm"
                variant="secondary"
                onClick={async () => {
                  await buy.mutateAsync(pack.id);
                  toast(t("credits"), "success");
                }}
                disabled={buy.isPending}
              >
                {formatPrice(pack.price, pack.currency, locale)} · {t("buy")}
              </Button>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
