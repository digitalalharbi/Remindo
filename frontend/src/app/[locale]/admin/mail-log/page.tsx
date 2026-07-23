"use client";

import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { useTranslations, useLocale } from "next-intl";
import { api, type ApiEnvelope } from "@/lib/api";
import { useAdminList } from "@/lib/hooks";
import { Mail } from "lucide-react";
import { cn } from "@/lib/utils";

interface MailRow extends Record<string, unknown> {
  id: string;
  to: string;
  subject: string;
  from: string;
  created_at: string;
}

interface MailFull extends MailRow {
  cc?: string;
  html?: string | null;
  text?: string | null;
}

function useMail(id: string | null) {
  return useQuery({
    queryKey: ["admin", "mail-log", id],
    enabled: !!id,
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<MailFull>>(`/admin/mail-log/${id}`);
      return data.data;
    },
  });
}

export default function AdminMailLogPage() {
  const t = useTranslations("admin.mail");
  const locale = useLocale();
  const { data, isLoading } = useAdminList<MailRow>("mail-log");
  const [selected, setSelected] = useState<string | null>(null);
  const { data: mail } = useMail(selected);
  const dateFmt = new Intl.DateTimeFormat(locale, { dateStyle: "medium", timeStyle: "short" });

  const rows = data?.data ?? [];

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-xl font-semibold tracking-tight">{t("title")}</h1>
        <p className="mt-1 text-sm text-muted-foreground">{t("subtitle")}</p>
      </div>

      <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)]">
        {/* List */}
        <div className="rounded-xl border border-border bg-card">
          {isLoading ? (
            <div className="p-6 text-sm text-muted-foreground">…</div>
          ) : rows.length === 0 ? (
            <div className="flex flex-col items-center gap-2 p-10 text-center text-sm text-muted-foreground">
              <Mail className="size-6 opacity-40" />
              {t("empty")}
            </div>
          ) : (
            <ul className="divide-y divide-border">
              {rows.map((m) => (
                <li key={m.id}>
                  <button
                    type="button"
                    onClick={() => setSelected(m.id)}
                    className={cn(
                      "flex w-full flex-col gap-0.5 px-4 py-3 text-start transition-colors hover:bg-muted",
                      selected === m.id && "bg-muted",
                    )}
                  >
                    <span className="truncate text-sm font-medium">{m.subject || "—"}</span>
                    <span className="truncate text-xs text-muted-foreground">{m.to}</span>
                    <span className="text-xs text-muted-foreground/70">
                      {dateFmt.format(new Date(m.created_at))}
                    </span>
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>

        {/* Detail */}
        <div className="rounded-xl border border-border bg-card">
          {!selected ? (
            <div className="grid h-full min-h-64 place-items-center p-10 text-center text-sm text-muted-foreground">
              {t("selectHint")}
            </div>
          ) : mail ? (
            <div className="flex h-full flex-col">
              <div className="space-y-1 border-b border-border p-4 text-sm">
                <div className="text-base font-semibold">{mail.subject}</div>
                <div className="text-muted-foreground">
                  <span className="font-medium">{t("from")}:</span> {mail.from}
                </div>
                <div className="text-muted-foreground">
                  <span className="font-medium">{t("to")}:</span> {mail.to}
                </div>
              </div>
              <div className="flex-1 overflow-auto p-1">
                {mail.html ? (
                  <iframe
                    title={mail.subject}
                    sandbox=""
                    srcDoc={mail.html}
                    className="h-[26rem] w-full rounded-md border-0 bg-white"
                  />
                ) : (
                  <pre className="whitespace-pre-wrap p-4 text-sm text-muted-foreground">
                    {mail.text}
                  </pre>
                )}
              </div>
            </div>
          ) : (
            <div className="p-6 text-sm text-muted-foreground">…</div>
          )}
        </div>
      </div>
    </div>
  );
}
