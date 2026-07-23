"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { Dialog } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { useRenewReminder } from "@/lib/hooks";
import { useToast } from "@/components/ui/toast";
import type { Reminder } from "@/lib/types";

export function RenewReminderDialog({
  reminder,
  open,
  onClose,
}: {
  reminder: Reminder;
  open: boolean;
  onClose: () => void;
}) {
  const t = useTranslations("app.form");
  const tr = useTranslations("app.reminders");
  const tc = useTranslations("common");
  const renew = useRenewReminder();
  const { toast } = useToast();
  const [date, setDate] = useState("");

  const submit = async () => {
    if (!date) return;
    await renew.mutateAsync({ id: reminder.id, body: { new_expiry_date: date } });
    toast(tr("renewedToast"));
    setDate("");
    onClose();
  };

  return (
    <Dialog open={open} onClose={onClose} title={t("renewTitle")}>
      <div className="space-y-4">
        <div className="space-y-1.5">
          <Label htmlFor="new_expiry_date">{t("renewQuestion")}</Label>
          <Input
            id="new_expiry_date"
            type="date"
            value={date}
            onChange={(e) => setDate(e.target.value)}
            autoFocus
          />
        </div>
        <div className="flex justify-end gap-2">
          <Button variant="ghost" onClick={onClose}>
            {tc("cancel")}
          </Button>
          <Button onClick={submit} disabled={renew.isPending || !date}>
            {t("renewSave")}
          </Button>
        </div>
      </div>
    </Dialog>
  );
}
