"use client";

import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useTranslations, useLocale } from "next-intl";
import { AxiosError } from "axios";
import { useRegister } from "@/lib/hooks";
import { useRouter, Link } from "@/i18n/navigation";
import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { AlertCircle } from "lucide-react";

const schema = z
  .object({
    name: z.string().min(2),
    email: z.string().email(),
    password: z.string().min(8),
    password_confirmation: z.string(),
  })
  .refine((d) => d.password === d.password_confirmation, {
    path: ["password_confirmation"],
    message: "mismatch",
  });
type FormValues = z.infer<typeof schema>;

export default function RegisterPage() {
  const t = useTranslations("auth");
  const locale = useLocale();
  const router = useRouter();
  const registerMut = useRegister();
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  const onSubmit = async (values: FormValues) => {
    try {
      const tz =
        Intl.DateTimeFormat().resolvedOptions().timeZone ?? "UTC";
      await registerMut.mutateAsync({ ...values, locale, timezone: tz });
      router.push("/app");
    } catch {
      /* surfaced below */
    }
  };

  const serverError =
    registerMut.error instanceof AxiosError
      ? (registerMut.error.response?.data as { message?: string })?.message
      : null;

  return (
    <div>
      <div className="text-center">
        <h1 className="text-2xl font-semibold tracking-tight">{t("registerTitle")}</h1>
        <p className="mt-2 text-sm text-muted-foreground">{t("registerSubtitle")}</p>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="mt-8 space-y-4">
        {serverError && (
          <div className="flex items-center gap-2 rounded-lg bg-danger/10 px-3 py-2.5 text-sm text-danger">
            <AlertCircle className="size-4 shrink-0" />
            {serverError}
          </div>
        )}

        <div className="space-y-1.5">
          <Label htmlFor="name">{t("name")}</Label>
          <Input id="name" autoComplete="name" {...register("name")} />
        </div>

        <div className="space-y-1.5">
          <Label htmlFor="email">{t("email")}</Label>
          <Input id="email" type="email" autoComplete="email" {...register("email")} />
        </div>

        <div className="space-y-1.5">
          <Label htmlFor="password">{t("password")}</Label>
          <Input
            id="password"
            type="password"
            autoComplete="new-password"
            {...register("password")}
          />
          {errors.password && (
            <p className="text-xs text-danger">{t("password")} — 8+</p>
          )}
        </div>

        <div className="space-y-1.5">
          <Label htmlFor="password_confirmation">{t("passwordConfirm")}</Label>
          <Input
            id="password_confirmation"
            type="password"
            autoComplete="new-password"
            {...register("password_confirmation")}
          />
          {errors.password_confirmation && (
            <p className="text-xs text-danger">{t("passwordConfirm")}</p>
          )}
        </div>

        <Button type="submit" size="lg" className="w-full" disabled={registerMut.isPending}>
          {registerMut.isPending ? "…" : t("submitRegister")}
        </Button>

        <p className="text-center text-xs text-muted-foreground">{t("agree")}</p>
      </form>

      <p className="mt-6 text-center text-sm text-muted-foreground">
        {t("haveAccount")}{" "}
        <Link href="/login" className="font-medium text-primary hover:underline">
          {t("submitLogin")}
        </Link>
      </p>
    </div>
  );
}
