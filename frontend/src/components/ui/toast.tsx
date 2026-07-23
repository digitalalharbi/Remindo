"use client";

import * as React from "react";
import { createPortal } from "react-dom";
import { CheckCircle2, AlertCircle, X } from "lucide-react";
import { cn } from "@/lib/utils";

type ToastKind = "success" | "error";
interface ToastItem {
  id: number;
  message: string;
  kind: ToastKind;
}

const ToastContext = React.createContext<{
  toast: (message: string, kind?: ToastKind) => void;
} | null>(null);

export function useToast() {
  const ctx = React.useContext(ToastContext);
  if (!ctx) throw new Error("useToast must be used within <ToastProvider>");
  return ctx;
}

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [items, setItems] = React.useState<ToastItem[]>([]);
  const [mounted, setMounted] = React.useState(false);
  // Portal target only exists on the client.
  // eslint-disable-next-line react-hooks/set-state-in-effect
  React.useEffect(() => setMounted(true), []);

  const toast = React.useCallback((message: string, kind: ToastKind = "success") => {
    const id = Date.now() + Math.random();
    setItems((prev) => [...prev, { id, message, kind }]);
    setTimeout(() => {
      setItems((prev) => prev.filter((t) => t.id !== id));
    }, 4000);
  }, []);

  const dismiss = (id: number) =>
    setItems((prev) => prev.filter((t) => t.id !== id));

  return (
    <ToastContext.Provider value={{ toast }}>
      {children}
      {mounted &&
        createPortal(
          <div className="pointer-events-none fixed inset-x-0 bottom-4 z-[100] flex flex-col items-center gap-2 px-4">
            {items.map((t) => (
              <div
                key={t.id}
                role="status"
                className="pointer-events-auto flex w-full max-w-sm animate-fade-up items-center gap-3 rounded-xl border border-border bg-card px-4 py-3 shadow-lg"
              >
                {t.kind === "success" ? (
                  <CheckCircle2 className="size-5 shrink-0 text-success" />
                ) : (
                  <AlertCircle className="size-5 shrink-0 text-danger" />
                )}
                <p className="flex-1 text-sm">{t.message}</p>
                <button
                  onClick={() => dismiss(t.id)}
                  className={cn("rounded p-0.5 text-muted-foreground hover:bg-muted")}
                  aria-label="Dismiss"
                >
                  <X className="size-4" />
                </button>
              </div>
            ))}
          </div>,
          document.body,
        )}
    </ToastContext.Provider>
  );
}
