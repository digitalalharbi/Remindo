"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { ShieldCheck, Monitor } from "lucide-react";
import {
  useMe,
  useEnableTwoFactor,
  useConfirmTwoFactor,
  useDisableTwoFactor,
  useSessions,
  useRevokeSessions,
} from "@/lib/hooks";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { useToast } from "@/components/ui/toast";

export function SecuritySettings() {
  const t = useTranslations("security");
  const { data: user } = useMe();
  const { toast } = useToast();

  const enable = useEnableTwoFactor();
  const confirm = useConfirmTwoFactor();
  const disable = useDisableTwoFactor();
  const { data: sessions } = useSessions();
  const revoke = useRevokeSessions();

  const [setup, setSetup] = useState<{ qr_svg: string; secret: string } | null>(null);
  const [code, setCode] = useState("");
  const [recovery, setRecovery] = useState<string[] | null>(null);

  const startEnable = async () => setSetup(await enable.mutateAsync());
  const confirmEnable = async () => {
    try {
      const res = await confirm.mutateAsync(code);
      setRecovery(res.recovery_codes);
      setSetup(null);
      setCode("");
      toast(t("twoFactorOn"), "success");
    } catch {
      toast(t("confirm"), "error");
    }
  };

  return (
    <div className="space-y-6">
      {/* Two-factor */}
      <div className="rounded-xl border border-border bg-card p-6">
        <div className="flex items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <ShieldCheck className="size-5 text-primary" />
            <div>
              <h2 className="text-sm font-semibold">{t("twoFactor")}</h2>
              <Badge variant={user?.two_factor_enabled ? "success" : "neutral"} className="mt-1">
                {user?.two_factor_enabled ? t("twoFactorOn") : t("twoFactorOff")}
              </Badge>
            </div>
          </div>
          {user?.two_factor_enabled ? (
            <Button variant="secondary" size="sm" onClick={() => disable.mutate()} disabled={disable.isPending}>
              {t("disable")}
            </Button>
          ) : !setup ? (
            <Button size="sm" onClick={startEnable} disabled={enable.isPending}>
              {t("enable")}
            </Button>
          ) : null}
        </div>

        {setup && (
          <div className="mt-5 space-y-3 border-t border-border pt-5">
            <p className="text-sm text-muted-foreground">{t("scanHint")}</p>
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={setup.qr_svg} alt="2FA QR" width={180} height={180} className="rounded-lg border border-border bg-white p-2" />
            <p className="text-xs text-muted-foreground">
              {t("cantScan")} <span className="font-mono">{setup.secret}</span>
            </p>
            <div className="flex gap-2">
              <Input value={code} onChange={(e) => setCode(e.target.value)} inputMode="numeric" placeholder="123456" className="max-w-40" />
              <Button size="sm" onClick={confirmEnable} disabled={confirm.isPending || code.length < 6}>
                {t("confirm")}
              </Button>
            </div>
          </div>
        )}

        {recovery && (
          <div className="mt-5 space-y-2 rounded-lg bg-muted/50 p-4">
            <p className="text-sm font-medium">{t("recoveryTitle")}</p>
            <p className="text-xs text-muted-foreground">{t("recoverySaved")}</p>
            <div className="grid grid-cols-2 gap-1.5 font-mono text-xs">
              {recovery.map((c) => (
                <span key={c}>{c}</span>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* Active sessions */}
      <div className="rounded-xl border border-border bg-card p-6">
        <div className="flex items-center justify-between">
          <h2 className="text-sm font-semibold">{t("sessions")}</h2>
          <Button variant="ghost" size="sm" onClick={() => revoke.mutate(undefined)} disabled={revoke.isPending}>
            {t("revokeOthers")}
          </Button>
        </div>
        <div className="mt-4 space-y-2">
          {(sessions ?? []).map((s) => (
            <div key={s.id} className="flex items-center gap-3 rounded-lg border border-border px-3 py-2.5">
              <Monitor className="size-4 text-muted-foreground" />
              <div className="min-w-0 flex-1">
                <p className="truncate text-sm">{s.user_agent}</p>
                <p className="text-xs text-muted-foreground">{s.ip_address}</p>
              </div>
              {s.current ? (
                <Badge variant="primary">{t("current")}</Badge>
              ) : (
                <button onClick={() => revoke.mutate(s.id)} className="text-xs text-danger hover:opacity-70">
                  {t("revoke")}
                </button>
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
