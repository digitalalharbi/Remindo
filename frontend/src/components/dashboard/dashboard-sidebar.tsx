"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import {
  LayoutGrid,
  BellRing,
  Calendar,
  FileText,
  Users,
  BarChart3,
  CreditCard,
  Settings,
  LogOut,
  ChevronDown,
} from "lucide-react";
import { Link, usePathname, useRouter } from "@/i18n/navigation";
import { Logo } from "@/components/site/logo";
import { useLogout } from "@/lib/hooks";
import { cn } from "@/lib/utils";

type NavItem = { href: string; key: string; icon: typeof LayoutGrid; exact?: boolean };

// Primary items stay short; the rest live under "More".
const PRIMARY: NavItem[] = [
  { href: "/dashboard", key: "overview", icon: LayoutGrid, exact: true },
  { href: "/reminders", key: "reminders", icon: BellRing },
  { href: "/calendar", key: "calendar", icon: Calendar },
  { href: "/documents", key: "documents", icon: FileText },
  { href: "/billing", key: "billing", icon: CreditCard },
  { href: "/settings", key: "settings", icon: Settings },
];

const MORE: NavItem[] = [
  { href: "/team", key: "team", icon: Users },
  { href: "/reports", key: "reports", icon: BarChart3 },
];

export function DashboardSidebar({ onNavigate }: { onNavigate?: () => void }) {
  const t = useTranslations("app.nav");
  const tc = useTranslations("common");
  const pathname = usePathname();
  const router = useRouter();
  const logout = useLogout();
  const [moreOpen, setMoreOpen] = useState(
    MORE.some((i) => pathname.startsWith(i.href)),
  );

  const isActive = (href: string, exact?: boolean) =>
    exact ? pathname === href : pathname.startsWith(href);

  const itemClass = (active: boolean) =>
    cn(
      "flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors",
      active
        ? "bg-primary/10 text-primary"
        : "text-muted-foreground hover:bg-muted hover:text-foreground",
    );

  return (
    <div className="flex h-full flex-col">
      <div className="px-4 py-5">
        <Logo href="/dashboard" />
      </div>
      <nav className="flex-1 space-y-1 overflow-y-auto px-3">
        {PRIMARY.map((item) => (
          <Link
            key={item.href}
            href={item.href}
            onClick={onNavigate}
            className={itemClass(isActive(item.href, item.exact))}
          >
            <item.icon className="size-4.5" />
            {t(item.key)}
          </Link>
        ))}

        <button
          onClick={() => setMoreOpen((o) => !o)}
          className={cn(itemClass(false), "w-full justify-between")}
        >
          <span className="flex items-center gap-3">
            <ChevronDown
              className={cn("size-4.5 transition-transform", !moreOpen && "-rotate-90 rtl:rotate-90")}
            />
            {t("more")}
          </span>
        </button>
        {moreOpen &&
          MORE.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              onClick={onNavigate}
              className={cn(itemClass(isActive(item.href)), "ps-9")}
            >
              <item.icon className="size-4.5" />
              {t(item.key)}
            </Link>
          ))}
      </nav>
      <div className="border-t border-border p-3">
        <button
          onClick={async () => {
            await logout.mutateAsync();
            router.push("/login");
          }}
          className="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
        >
          <LogOut className="size-4.5" />
          {tc("signOut")}
        </button>
      </div>
    </div>
  );
}
