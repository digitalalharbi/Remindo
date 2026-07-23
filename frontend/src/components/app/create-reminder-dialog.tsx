"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useTranslations, useLocale } from "next-intl";
import { ChevronDown, Mail, Bell, Smartphone, MessageCircle } from "lucide-react";
import { Dialog } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { useCreateReminder, useCategories } from "@/lib/hooks";
import { useToast } from "@/components/ui/toast";
import { cn } from "@/lib/utils";
import type { Channel } from "@/lib/types";

const schema = z.object({
  title: z.string().min(1),
  expiry_date: z.string().min(1),
  category_id: z.string().optional(),
  reference_number: z.string().optional(),
  issuer: z.string().optional(),
  recurrence: z.enum(["none", "monthly", "yearly"]).optional(),
});
type FormValues = z.infer<typeof schema>;

const OFFSETS = [30, 7, 1, 0] as const;
const CHANNELS: { key: Channel; icon: typeof Mail; labelKey: string }[] = [
  { key: "email", icon: Mail, labelKey: "channelEmail" },
  { key: "in_app", icon: Bell, labelKey: "channelInApp" },
  { key: "web_push", icon: Smartphone, labelKey: "channelWebPush" },
  { key: "whatsapp", icon: MessageCircle, labelKey: "channelWhatsapp" },
];

export function CreateReminderDialog({
  open,
  onClose,
}: {
  open: boolean;
  onClose: () => void;
}) {
  const t = useTranslations("app.form");
  const tc = useTranslations("common");
  const locale = useLocale();
  const { data: categories } = useCategories();
  const create = useCreateReminder();
  const { toast } = useToast();

  const [offsets, setOffsets] = useState<number[]>([7, 1]);
  const [channels, setChannels] = useState<Channel[]>(["email", "in_app"]);
  const [advanced, setAdvanced] = useState(false);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { recurrence: "none" },
  });

  const toggle = <T,>(list: T[], value: T, setter: (v: T[]) => void) =>
    setter(list.includes(value) ? list.filter((v) => v !== value) : [...list, value]);

  const offsetLabel = (o: number) =>
    o === 0 ? t("offsetSame") : o === 1 ? t("offset1") : o === 7 ? t("offset7") : t("offset30");

  const onSubmit = async (values: FormValues) => {
    try {
      await create.mutateAsync({
        ...values,
        category_id: values.category_id || undefined,
        reminder_offsets: offsets.length ? offsets : [7],
        channels: channels.length ? channels : ["email", "in_app"],
      });
      toast(t("saved"), "success");
      reset();
      setOffsets([7, 1]);
      setChannels(["email", "in_app"]);
      setAdvanced(false);
      onClose();
    } catch {
      toast(tc("loading"), "error");
    }
  };

  const catName = (name: Record<string, string> | string) =>
    typeof name === "string" ? name : name[locale] ?? name.en;

  return (
    <Dialog open={open} onClose={onClose} title={t("createTitle")}>
      <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
        <div className="space-y-1.5">
          <Label htmlFor="title">{t("nameQuestion")}</Label>
          <Input
            id="title"
            placeholder={t("namePlaceholder")}
            autoFocus
            {...register("title")}
          />
          {errors.title && <p className="text-xs text-danger">{t("nameQuestion")}</p>}
        </div>

        <div className="grid grid-cols-2 gap-3">
          <div className="space-y-1.5">
            <Label htmlFor="expiry_date">{t("expiryQuestion")}</Label>
            <Input id="expiry_date" type="date" {...register("expiry_date")} />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="category_id">{t("category")}</Label>
            <select
              id="category_id"
              className="h-11 w-full rounded-lg border border-input bg-background px-3 text-sm"
              {...register("category_id")}
            >
              <option value="">—</option>
              {categories?.map((c) => (
                <option key={c.id} value={c.id}>
                  {catName(c.name)}
                </option>
              ))}
            </select>
          </div>
        </div>

        <div className="space-y-2">
          <Label>{t("remindQuestion")}</Label>
          <div className="flex flex-wrap gap-2">
            {OFFSETS.map((o) => (
              <button
                key={o}
                type="button"
                onClick={() => toggle(offsets, o, setOffsets)}
                className={cn(
                  "rounded-full border px-3 py-1.5 text-sm transition-colors",
                  offsets.includes(o)
                    ? "border-primary bg-primary/10 text-primary"
                    : "border-border text-muted-foreground hover:bg-muted",
                )}
              >
                {offsetLabel(o)}
              </button>
            ))}
          </div>
        </div>

        <div className="space-y-2">
          <Label>{t("channelQuestion")}</Label>
          <div className="flex flex-wrap gap-2">
            {CHANNELS.map((c) => (
              <button
                key={c.key}
                type="button"
                onClick={() => toggle(channels, c.key, setChannels)}
                className={cn(
                  "inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm transition-colors",
                  channels.includes(c.key)
                    ? "border-primary bg-primary/10 text-primary"
                    : "border-border text-muted-foreground hover:bg-muted",
                )}
              >
                <c.icon className="size-3.5" />
                {t(c.labelKey)}
              </button>
            ))}
          </div>
        </div>

        {/* Progressive disclosure */}
        <div>
          <button
            type="button"
            onClick={() => setAdvanced((a) => !a)}
            className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
          >
            <ChevronDown
              className={cn("size-4 transition-transform", advanced && "rotate-180")}
            />
            {t("advanced")}
          </button>
          {advanced && (
            <div className="mt-3 grid grid-cols-2 gap-3">
              <div className="space-y-1.5">
                <Label htmlFor="reference_number">{t("referenceNumber")}</Label>
                <Input id="reference_number" {...register("reference_number")} />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="issuer">{t("issuer")}</Label>
                <Input id="issuer" {...register("issuer")} />
              </div>
              <div className="col-span-2 space-y-1.5">
                <Label htmlFor="recurrence">{t("recurrence")}</Label>
                <select
                  id="recurrence"
                  className="h-11 w-full rounded-lg border border-input bg-background px-3 text-sm"
                  {...register("recurrence")}
                >
                  <option value="none">{t("recurrenceNone")}</option>
                  <option value="monthly">{t("recurrenceMonthly")}</option>
                  <option value="yearly">{t("recurrenceYearly")}</option>
                </select>
              </div>
            </div>
          )}
        </div>

        <div className="flex justify-end gap-2 pt-2">
          <Button type="button" variant="ghost" onClick={onClose}>
            {tc("cancel")}
          </Button>
          <Button type="submit" disabled={create.isPending}>
            {create.isPending ? "…" : tc("save")}
          </Button>
        </div>
      </form>
    </Dialog>
  );
}
