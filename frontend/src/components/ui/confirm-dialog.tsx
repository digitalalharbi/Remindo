"use client";

import { useTranslations } from "next-intl";
import { AlertTriangle } from "lucide-react";
import { Dialog } from "./dialog";
import { Button } from "./button";

/** Confirmation gate for destructive/irreversible admin actions. */
export function ConfirmDialog({
  open,
  onClose,
  onConfirm,
  title,
  message,
  confirmLabel,
  danger = true,
  loading = false,
}: {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  title: string;
  message: string;
  confirmLabel?: string;
  danger?: boolean;
  loading?: boolean;
}) {
  const tc = useTranslations("common");

  return (
    <Dialog open={open} onClose={onClose} title={title}>
      <div className="space-y-5">
        <div className="flex gap-3">
          {danger && (
            <div className="grid size-9 shrink-0 place-items-center rounded-full bg-danger/12 text-danger">
              <AlertTriangle className="size-4.5" />
            </div>
          )}
          <p className="text-sm text-muted-foreground">{message}</p>
        </div>
        <div className="flex justify-end gap-2">
          <Button variant="ghost" onClick={onClose} disabled={loading}>
            {tc("cancel")}
          </Button>
          <Button variant={danger ? "danger" : "primary"} onClick={onConfirm} disabled={loading}>
            {loading ? "…" : confirmLabel ?? tc("delete")}
          </Button>
        </div>
      </div>
    </Dialog>
  );
}
