"use client";

import * as React from "react";
import { createPortal } from "react-dom";
import { X } from "lucide-react";
import { cn } from "@/lib/utils";

interface DialogProps {
  open: boolean;
  onClose: () => void;
  title?: string;
  description?: string;
  children: React.ReactNode;
  className?: string;
}

/** Lightweight accessible modal: backdrop, Escape to close, focus on open, scroll lock. */
export function Dialog({
  open,
  onClose,
  title,
  description,
  children,
  className,
}: DialogProps) {
  const panelRef = React.useRef<HTMLDivElement>(null);

  React.useEffect(() => {
    if (!open) return;
    const onKey = (e: KeyboardEvent) => e.key === "Escape" && onClose();
    document.addEventListener("keydown", onKey);
    document.body.style.overflow = "hidden";
    panelRef.current?.focus();
    return () => {
      document.removeEventListener("keydown", onKey);
      document.body.style.overflow = "";
    };
  }, [open, onClose]);

  if (!open || typeof document === "undefined") return null;

  return createPortal(
    <div
      className="fixed inset-0 z-50 flex items-end justify-center sm:items-center"
      role="dialog"
      aria-modal="true"
      aria-label={title}
    >
      <div
        className="absolute inset-0 bg-foreground/25 backdrop-blur-[2px] animate-fade-up"
        onClick={onClose}
      />
      <div
        ref={panelRef}
        tabIndex={-1}
        className={cn(
          "relative z-10 w-full max-w-lg animate-fade-up rounded-t-2xl border border-border bg-card p-6 shadow-lg outline-none sm:rounded-2xl",
          className,
        )}
      >
        <button
          onClick={onClose}
          aria-label="Close"
          className="absolute end-4 top-4 rounded-md p-1 text-muted-foreground transition-colors hover:bg-muted"
        >
          <X className="size-4" />
        </button>
        {title && (
          <h2 className="text-lg font-semibold tracking-tight">{title}</h2>
        )}
        {description && (
          <p className="mt-1 text-sm text-muted-foreground">{description}</p>
        )}
        <div className={cn(title && "mt-5")}>{children}</div>
      </div>
    </div>,
    document.body,
  );
}
