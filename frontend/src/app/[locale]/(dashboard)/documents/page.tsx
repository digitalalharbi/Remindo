"use client";

import { useState } from "react";
import { useTranslations, useLocale } from "next-intl";
import { Upload, FileText, Loader2, Sparkles, Trash2 } from "lucide-react";
import { useDocuments, useUploadDocument, useExtractDocument, useDeleteDocument } from "@/lib/hooks";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { useToast } from "@/components/ui/toast";
import { useUiStore } from "@/stores/ui";
import { cn } from "@/lib/utils";

function formatSize(bytes: number) {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

export default function DocumentsPage() {
  const t = useTranslations("app.documents");
  const tn = useTranslations("app.nav");
  const tc = useTranslations("common");
  const locale = useLocale();
  const { data: documents, isLoading, refetch } = useDocuments();
  const upload = useUploadDocument();
  const extract = useExtractDocument();
  const del = useDeleteDocument();
  const { toast } = useToast();
  const openCreate = useUiStore((s) => s.openCreateReminder);
  const [dragging, setDragging] = useState(false);

  const busy = upload.isPending || extract.isPending;

  const onFile = async (file: File | undefined) => {
    if (!file) return;
    try {
      const doc = await upload.mutateAsync(file);
      await extract.mutateAsync(doc.id);
      toast(t("uploaded"), "success");
      refetch();
    } catch {
      toast(tc("loading"), "error");
    }
  };

  const dateFmt = new Intl.DateTimeFormat(locale, { dateStyle: "medium" });

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold tracking-tight">{tn("documents")}</h1>
      </div>

      {/* Upload zone */}
      <label
        onDragOver={(e) => {
          e.preventDefault();
          setDragging(true);
        }}
        onDragLeave={() => setDragging(false)}
        onDrop={(e) => {
          e.preventDefault();
          setDragging(false);
          onFile(e.dataTransfer.files?.[0]);
        }}
        className={cn(
          "flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-6 py-10 text-center transition-colors",
          dragging ? "border-primary bg-primary/5" : "border-border bg-card/50 hover:bg-muted/50",
          busy && "pointer-events-none opacity-70",
        )}
      >
        {busy ? (
          <Loader2 className="size-6 animate-spin text-primary" />
        ) : (
          <Upload className="size-6 text-muted-foreground" />
        )}
        <span className="text-sm font-medium">{t("dropHint")}</span>
        <span className="text-xs text-muted-foreground">{t("accepted")}</span>
        <input
          type="file"
          className="hidden"
          accept=".pdf,.png,.jpg,.jpeg,.webp,.doc,.docx"
          onChange={(e) => onFile(e.target.files?.[0])}
          disabled={busy}
        />
      </label>

      <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
        <Sparkles className="size-3.5 text-primary" />
        {t("aiHint")}
      </p>

      {/* Document list */}
      {isLoading ? (
        <div className="space-y-2">
          {Array.from({ length: 3 }).map((_, i) => (
            <Skeleton key={i} className="h-14 rounded-xl" />
          ))}
        </div>
      ) : !documents || documents.length === 0 ? (
        <div className="rounded-xl border border-dashed border-border bg-card/50 px-6 py-12 text-center text-sm text-muted-foreground">
          {t("empty")}
        </div>
      ) : (
        <div className="space-y-2">
          {documents.map((d) => (
            <div
              key={d.id}
              className="flex items-center gap-3 rounded-xl border border-border bg-card px-4 py-3"
            >
              <div className="grid size-9 shrink-0 place-items-center rounded-lg bg-muted text-muted-foreground">
                <FileText className="size-4" />
              </div>
              <div className="min-w-0 flex-1">
                <p className="truncate text-sm font-medium">{d.original_name}</p>
                <p className="text-xs text-muted-foreground">
                  {formatSize(d.size)} · {dateFmt.format(new Date(d.created_at))}
                </p>
              </div>
              {d.extraction_status === "completed" && (
                <Badge variant="success">{t("extracted")}</Badge>
              )}
              <button
                onClick={openCreate}
                className="text-xs font-medium text-primary hover:underline"
              >
                {t("createReminder")}
              </button>
              <button
                onClick={() => del.mutate(d.id)}
                className="text-danger hover:opacity-70"
                aria-label="Delete"
              >
                <Trash2 className="size-4" />
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
