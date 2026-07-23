"use client";

import { useState } from "react";
import { useLocale } from "next-intl";
import { api } from "@/lib/api";
import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { CheckCircle2 } from "lucide-react";
import type { PageContent } from "@/content/marketing/types";

type ContactContent = PageContent & {
  nameLabel: string;
  emailLabel: string;
  messageLabel: string;
  send: string;
  sent: string;
};

export function ContactForm({ content }: { content: ContactContent }) {
  const locale = useLocale();
  const [form, setForm] = useState({ name: "", email: "", message: "" });
  const [sent, setSent] = useState(false);
  const [pending, setPending] = useState(false);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setPending(true);
    try {
      await api.post("/contact", { ...form, locale });
      setSent(true);
    } catch {
      /* keep the form; user can retry */
    } finally {
      setPending(false);
    }
  };

  if (sent) {
    return (
      <div className="rounded-2xl border border-border bg-card p-10 text-center">
        <CheckCircle2 className="mx-auto size-8 text-success" />
        <p className="mt-4 font-medium">{content.sent}</p>
      </div>
    );
  }

  return (
    <form onSubmit={submit} className="space-y-4 rounded-2xl border border-border bg-card p-6 sm:p-8">
      <div className="space-y-1.5">
        <Label htmlFor="name">{content.nameLabel}</Label>
        <Input id="name" required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
      </div>
      <div className="space-y-1.5">
        <Label htmlFor="email">{content.emailLabel}</Label>
        <Input id="email" type="email" required value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
      </div>
      <div className="space-y-1.5">
        <Label htmlFor="message">{content.messageLabel}</Label>
        <textarea
          id="message"
          required
          rows={5}
          className="w-full rounded-lg border border-input bg-background p-3 text-sm"
          value={form.message}
          onChange={(e) => setForm({ ...form, message: e.target.value })}
        />
      </div>
      <Button type="submit" size="lg" className="w-full" disabled={pending}>
        {pending ? "…" : content.send}
      </Button>
    </form>
  );
}
