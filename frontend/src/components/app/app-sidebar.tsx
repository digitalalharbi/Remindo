"use client";

import { useTranslations } from "next-intl";
import {
  Home,
  BellRing,
  Calendar,
  FileText,
  Users,
  CreditCard,
  Settings,
  LogOut,
} from "lucide-react";
import { Link, usePathname, useRouter } from "@/i18n/navigation";
import { Logo } from "@/components/site/logo";
import { useLogout } from "@/lib/hooks";
import { cn } from "@/lib/utils";

type NavItem = {
  href: "/app" | "/app/reminders" | "/app/calendar" | "/app/documents" | "/app/team" | "/app/subscription" | "/app/settings";
  key: string;
  icon: typeof Home;
  exact?: boolean;
};

const ITEMS: NavItem[] = [
  { href: "/app", key: "home", icon: Home, exact: true },
  { href: "/app/reminders", key: "reminders", icon: BellRing },
  { href: "/app/calendar", key: "calendar", icon: Calendar },
  { href: "/app/documents", key: "documents", icon: FileText },
  { href: "/app/team", key: "team", icon: Users },
  { href: "/app/subscription", key: "subscription", icon: CreditCard },
  { href: "/app/settings", key: "settings", icon: Settings },
];

export function AppSidebar({ onNavigate }: { onNavigate?: () => void }) {
  const t = useTranslations("app.nav");
  const tc = useTranslations("common");
  const pathname = usePathname();
  const router = useRouter();
  const logout = useLogout();

  const isActive = (href: string, exact?: boolean) =>
    exact ? pathname === href : pathname.startsWith(href);

  return (
    <div className="flex h-full flex-col">
      <div className="px-4 py-5">
        <Logo />
      </div>
      <nav className="flex-1 space-y-1 px-3">
        {ITEMS.map((item) => (
          <Link
            key={item.href}
            href={item.href}
            onClick={onNavigate}
            className={cn(
              "flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors",
              isActive(item.href, item.exact)
                ? "bg-primary/10 text-primary"
                : "text-muted-foreground hover:bg-muted hover:text-foreground",
            )}
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
