"use client";

import { useTranslations } from "next-intl";
import {
  LayoutGrid,
  Users,
  Building2,
  Package,
  Ticket,
  Receipt,
  Flag,
  Languages,
  HelpCircle,
  FileEdit,
  ScrollText,
  ArrowLeft,
} from "lucide-react";
import { Link, usePathname } from "@/i18n/navigation";
import { cn } from "@/lib/utils";

const ITEMS = [
  { href: "/admin", key: "overview", icon: LayoutGrid, exact: true },
  { href: "/admin/users", key: "users", icon: Users },
  { href: "/admin/organizations", key: "organizations", icon: Building2 },
  { href: "/admin/plans", key: "plans", icon: Package },
  { href: "/admin/coupons", key: "coupons", icon: Ticket },
  { href: "/admin/invoices", key: "invoices", icon: Receipt },
  { href: "/admin/flags", key: "flags", icon: Flag },
  { href: "/admin/languages", key: "languages", icon: Languages },
  { href: "/admin/faqs", key: "faqs", icon: HelpCircle },
  { href: "/admin/content", key: "content", icon: FileEdit },
  { href: "/admin/audit-logs", key: "auditLogs", icon: ScrollText },
];

export function AdminSidebar({ onNavigate }: { onNavigate?: () => void }) {
  const t = useTranslations("admin.nav");
  const pathname = usePathname();

  const isActive = (href: string, exact?: boolean) =>
    exact ? pathname === href : pathname.startsWith(href);

  return (
    <div className="flex h-full flex-col">
      <div className="flex items-center gap-2 px-5 py-5">
        <span className="grid size-7 place-items-center rounded-md bg-foreground text-xs font-bold text-background">
          R
        </span>
        <span className="font-semibold tracking-tight">{t("title")}</span>
      </div>
      <nav className="flex-1 space-y-1 overflow-y-auto px-3">
        {ITEMS.map((item) => (
          <Link
            key={item.href}
            href={item.href}
            onClick={onNavigate}
            className={cn(
              "flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors",
              isActive(item.href, item.exact)
                ? "bg-foreground text-background"
                : "text-muted-foreground hover:bg-muted hover:text-foreground",
            )}
          >
            <item.icon className="size-4.5" />
            {t(item.key)}
          </Link>
        ))}
      </nav>
      <div className="border-t border-border p-3">
        <Link
          href="/dashboard"
          className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
        >
          <ArrowLeft className="size-4.5 rtl:rotate-180" />
          {t("backToApp")}
        </Link>
      </div>
    </div>
  );
}
