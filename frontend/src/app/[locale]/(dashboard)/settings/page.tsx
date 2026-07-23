"use client";

import { useForm } from "react-hook-form";
import { useTranslations, useLocale } from "next-intl";
import { useEffect } from "react";
import { useMe, useUpdateProfile } from "@/lib/hooks";
import { useRouter } from "@/i18n/navigation";
import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { useToast } from "@/components/ui/toast";
import { locales } from "@/i18n/routing";
import { SecuritySettings } from "@/components/dashboard/security-settings";
import { NotificationSettings } from "@/components/dashboard/notification-settings";

interface ProfileForm {
  name: string;
  phone: string;
  locale: string;
  country: string;
  timezone: string;
}

export default function SettingsPage() {
  const t = useTranslations("app.settings");
  const tn = useTranslations("app.nav");
  const currentLocale = useLocale();
  const router = useRouter();
  const { data: user } = useMe();
  const update = useUpdateProfile();
  const { toast } = useToast();

  const { register, handleSubmit, reset } = useForm<ProfileForm>();

  useEffect(() => {
    if (user) {
      reset({
        name: user.name,
        phone: user.phone ?? "",
        locale: user.locale,
        country: user.country ?? "",
        timezone: user.timezone,
      });
    }
  }, [user, reset]);

  const onSubmit = async (values: ProfileForm) => {
    const updated = await update.mutateAsync(values);
    toast(t("saved"), "success");
    // If the language changed, move to that locale's settings page.
    if (updated.locale !== currentLocale && locales.includes(updated.locale as never)) {
      router.replace("/settings", { locale: updated.locale as never });
    }
  };

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold tracking-tight">{tn("settings")}</h1>

      <form
        onSubmit={handleSubmit(onSubmit)}
        className="max-w-xl space-y-5 rounded-xl border border-border bg-card p-6"
      >
        <h2 className="text-sm font-semibold text-muted-foreground">{t("profile")}</h2>

        <div className="space-y-1.5">
          <Label htmlFor="name">{t("fullName")}</Label>
          <Input id="name" {...register("name")} />
        </div>

        <div className="grid gap-4 sm:grid-cols-2">
          <div className="space-y-1.5">
            <Label htmlFor="phone">{t("phone")}</Label>
            <Input id="phone" {...register("phone")} />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="country">{t("country")}</Label>
            <Input id="country" maxLength={2} placeholder="SA" {...register("country")} />
          </div>
        </div>

        <div className="grid gap-4 sm:grid-cols-2">
          <div className="space-y-1.5">
            <Label htmlFor="locale">{t("language")}</Label>
            <select
              id="locale"
              className="h-11 w-full rounded-lg border border-input bg-background px-3 text-sm"
              {...register("locale")}
            >
              {locales.map((l) => (
                <option key={l} value={l}>
                  {l.toUpperCase()}
                </option>
              ))}
            </select>
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="timezone">{t("timezone")}</Label>
            <Input id="timezone" {...register("timezone")} />
          </div>
        </div>

        <div className="flex justify-end">
          <Button type="submit" disabled={update.isPending}>
            {update.isPending ? "…" : t("save")}
          </Button>
        </div>
      </form>

      <SecuritySettings />
      <NotificationSettings />
    </div>
  );
}
