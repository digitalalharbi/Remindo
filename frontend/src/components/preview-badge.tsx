"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { FlaskConical, X } from "lucide-react";

/**
 * A clear, dismissible "Preview Mode" badge shown across every surface when the
 * app runs against the public preview (sandbox data, mocked integrations).
 * Gated by NEXT_PUBLIC_PREVIEW_MODE so production never renders it.
 */
export function PreviewBadge() {
  const t = useTranslations("common");
  const [hidden, setHidden] = useState(false);

  if (process.env.NEXT_PUBLIC_PREVIEW_MODE !== "true" || hidden) return null;

  // The pill is decorative and must never intercept clicks on the UI beneath it
  // (e.g. a Save button at the bottom of a dialog): the container and the pill
  // are pointer-events-none; only the dismiss button re-enables pointer events.
  return (
    <div className="pointer-events-none fixed inset-x-0 bottom-0 z-40 flex justify-center px-3 pb-3">
      <div className="pointer-events-none flex items-center gap-2.5 rounded-full border border-amber-500/30 bg-amber-500/15 px-4 py-2 text-xs font-medium text-amber-700 shadow-lg backdrop-blur-md dark:text-amber-300">
        <FlaskConical className="size-3.5 shrink-0" />
        <span className="font-semibold">{t("previewMode")}</span>
        <span className="hidden text-amber-700/70 sm:inline dark:text-amber-300/70">·</span>
        <span className="hidden text-amber-700/70 sm:inline dark:text-amber-300/70">
          {t("previewNote")}
        </span>
        <button
          type="button"
          aria-label="Dismiss"
          onClick={() => setHidden(true)}
          className="pointer-events-auto ms-1 rounded-full p-0.5 hover:bg-amber-500/20"
        >
          <X className="size-3.5" />
        </button>
      </div>
    </div>
  );
}
