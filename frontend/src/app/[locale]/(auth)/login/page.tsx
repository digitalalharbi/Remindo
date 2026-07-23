"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useTranslations } from "next-intl";
import { AxiosError } from "axios";
import { useLogin, useTwoFactorChallenge, useOAuthStatus } from "@/lib/hooks";
import { useRouter, Link } from "@/i18n/navigation";
import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { AlertCircle } from "lucide-react";

const schema = z.object({
  email: z.string().email(),
  password: z.string().min(1),
});
type FormValues = z.infer<typeof schema>;

const API = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api";
const PREVIEW = process.env.NEXT_PUBLIC_PREVIEW_MODE === "true";
const DEMO_EMAIL = process.env.NEXT_PUBLIC_DEMO_EMAIL ?? "demo@remindo.me";
const DEMO_PASSWORD = process.env.NEXT_PUBLIC_DEMO_PASSWORD ?? "DemoPass123!";

export default function LoginPage() {
  const t = useTranslations("auth");
  const router = useRouter();
  const login = useLogin();
  const challenge = useTwoFactorChallenge();
  const { data: oauth } = useOAuthStatus();

  const [needsTwoFactor, setNeedsTwoFactor] = useState(false);
  const [code, setCode] = useState("");
  const [useRecovery, setUseRecovery] = useState(false);

  const {
    register,
    handleSubmit,
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  const onSubmit = async (values: FormValues) => {
    try {
      const user = await login.mutateAsync(values);
      // The login endpoint returns { two_factor: true } instead of a user when 2FA is on.
      if ((user as unknown as { two_factor?: boolean })?.two_factor) {
        setNeedsTwoFactor(true);
        return;
      }
      router.push("/dashboard");
    } catch {
      /* surfaced below */
    }
  };

  const tryDemo = () =>
    onSubmit({ email: DEMO_EMAIL, password: DEMO_PASSWORD });

  const submitCode = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await challenge.mutateAsync(useRecovery ? { recovery_code: code } : { code });
      router.push("/dashboard");
    } catch {
      /* surfaced below */
    }
  };

  const serverError = (err: unknown) =>
    err instanceof AxiosError ? (err.response?.data as { message?: string })?.message : null;

  if (needsTwoFactor) {
    return (
      <div>
        <div className="text-center">
          <h1 className="text-2xl font-semibold tracking-tight">{t("twoFactorTitle")}</h1>
          <p className="mt-2 text-sm text-muted-foreground">{t("twoFactorPrompt")}</p>
        </div>
        <form onSubmit={submitCode} className="mt-8 space-y-4">
          {serverError(challenge.error) && (
            <div className="flex items-center gap-2 rounded-lg bg-danger/10 px-3 py-2.5 text-sm text-danger">
              <AlertCircle className="size-4 shrink-0" />
              {serverError(challenge.error)}
            </div>
          )}
          <div className="space-y-1.5">
            <Label htmlFor="code">{useRecovery ? t("recoveryCode") : t("code")}</Label>
            <Input
              id="code"
              inputMode={useRecovery ? "text" : "numeric"}
              autoFocus
              value={code}
              onChange={(e) => setCode(e.target.value)}
            />
          </div>
          <Button type="submit" size="lg" className="w-full" disabled={challenge.isPending || !code}>
            {challenge.isPending ? "…" : t("verify")}
          </Button>
          <button
            type="button"
            onClick={() => setUseRecovery((v) => !v)}
            className="w-full text-center text-xs text-primary hover:underline"
          >
            {useRecovery ? t("code") : t("useRecoveryCode")}
          </button>
        </form>
      </div>
    );
  }

  return (
    <div>
      <div className="text-center">
        <h1 className="text-2xl font-semibold tracking-tight">{t("loginTitle")}</h1>
        <p className="mt-2 text-sm text-muted-foreground">{t("loginSubtitle")}</p>
      </div>

      {(oauth?.google || oauth?.microsoft) && (
        <div className="mt-8 space-y-2">
          {oauth?.google && (
            <a
              href={`${API}/auth/oauth/google/redirect`}
              className="flex h-11 w-full items-center justify-center gap-2 rounded-lg border border-border bg-card text-sm font-medium hover:bg-muted"
            >
              {t("continueWith", { provider: "Google" })}
            </a>
          )}
          {oauth?.microsoft && (
            <a
              href={`${API}/auth/oauth/microsoft/redirect`}
              className="flex h-11 w-full items-center justify-center gap-2 rounded-lg border border-border bg-card text-sm font-medium hover:bg-muted"
            >
              {t("continueWith", { provider: "Microsoft" })}
            </a>
          )}
          <div className="relative py-2 text-center">
            <span className="relative z-10 bg-background px-3 text-xs text-muted-foreground">
              {t("orContinueWith")}
            </span>
            <span className="absolute inset-x-0 top-1/2 h-px bg-border" />
          </div>
        </div>
      )}

      <form onSubmit={handleSubmit(onSubmit)} className={oauth?.google || oauth?.microsoft ? "space-y-4" : "mt-8 space-y-4"}>
        {serverError(login.error) && (
          <div className="flex items-center gap-2 rounded-lg bg-danger/10 px-3 py-2.5 text-sm text-danger">
            <AlertCircle className="size-4 shrink-0" />
            {serverError(login.error)}
          </div>
        )}

        <div className="space-y-1.5">
          <Label htmlFor="email">{t("email")}</Label>
          <Input id="email" type="email" autoComplete="email" {...register("email")} />
        </div>

        <div className="space-y-1.5">
          <div className="flex items-center justify-between">
            <Label htmlFor="password">{t("password")}</Label>
            <Link href="/forgot-password" className="text-xs text-primary hover:underline">
              {t("forgotPassword")}
            </Link>
          </div>
          <Input id="password" type="password" autoComplete="current-password" {...register("password")} />
        </div>

        <Button type="submit" size="lg" className="w-full" disabled={login.isPending}>
          {login.isPending ? "…" : t("submitLogin")}
        </Button>

        {PREVIEW && (
          <Button
            type="button"
            variant="secondary"
            size="lg"
            className="w-full"
            onClick={tryDemo}
            disabled={login.isPending}
          >
            {t("tryDemo")}
          </Button>
        )}
      </form>

      <p className="mt-6 text-center text-sm text-muted-foreground">
        {t("noAccount")}{" "}
        <Link href="/register" className="font-medium text-primary hover:underline">
          {t("submitRegister")}
        </Link>
      </p>
    </div>
  );
}
