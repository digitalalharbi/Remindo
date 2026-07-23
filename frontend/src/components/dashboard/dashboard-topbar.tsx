"use client";

import { useState, useRef, useEffect } from "react";
import { useTranslations } from "next-intl";
import { Search, Plus, Bell, Menu, User as UserIcon, Settings, LogOut } from "lucide-react";
import { Link, useRouter } from "@/i18n/navigation";
import { LocaleSwitcher } from "@/components/site/locale-switcher";
import { ThemeToggle } from "@/components/site/theme-toggle";
import { Logo } from "@/components/site/logo";
import { Button } from "@/components/ui/button";
import { useUiStore } from "@/stores/ui";
import { useNotifications, useMarkAllRead, useMe, useLogout } from "@/lib/hooks";
import { cn } from "@/lib/utils";

export function DashboardTopbar({ onOpenMenu }: { onOpenMenu: () => void }) {
  const t = useTranslations("app.reminders");
  const tc = useTranslations("common");
  const tn = useTranslations("app.nav");
  const router = useRouter();
  const openCreate = useUiStore((s) => s.openCreateReminder);
  const [search, setSearch] = useState("");

  const submitSearch = (e: React.FormEvent) => {
    e.preventDefault();
    const q = search.trim();
    router.push(q ? `/reminders?q=${encodeURIComponent(q)}` : "/reminders");
  };

  return (
    <header className="sticky top-0 z-30 flex items-center gap-3 border-b border-border bg-card/70 px-4 py-2.5 backdrop-blur lg:px-6">
      <button
        className="grid size-9 shrink-0 place-items-center rounded-lg border border-border lg:hidden"
        aria-label="Menu"
        onClick={onOpenMenu}
      >
        <Menu className="size-4" />
      </button>

      <div className="lg:hidden">
        <Logo href="/dashboard" />
      </div>

      <form onSubmit={submitSearch} className="relative hidden flex-1 sm:block">
        <Search className="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
        <input
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder={t("searchPlaceholder")}
          className="h-9 w-full max-w-md rounded-lg border border-input bg-background ps-9 pe-3 text-sm outline-none focus-visible:border-ring"
        />
      </form>

      <div className="ms-auto flex items-center gap-1.5">
        <Button size="sm" onClick={openCreate} className="hidden sm:inline-flex">
          <Plus className="size-4" />
          {t("add")}
        </Button>
        <button
          onClick={openCreate}
          aria-label={t("add")}
          className="grid size-9 place-items-center rounded-lg bg-primary text-primary-foreground sm:hidden"
        >
          <Plus className="size-4" />
        </button>

        <NotificationsBell label={tn("reminders")} />
        <LocaleSwitcher />
        <ThemeToggle />
        <AccountMenu settingsLabel={tn("settings")} signOutLabel={tc("signOut")} />
      </div>
    </header>
  );
}

function NotificationsBell({ label }: { label: string }) {
  const { data } = useNotifications();
  const markAll = useMarkAllRead();
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);
  const unread = (data?.meta?.unread as number) ?? 0;
  const items = data?.data ?? [];

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", onClick);
    return () => document.removeEventListener("mousedown", onClick);
  }, []);

  return (
    <div ref={ref} className="relative">
      <button
        aria-label={label}
        onClick={() => setOpen((o) => !o)}
        className="relative grid size-9 place-items-center rounded-lg border border-border bg-card transition-colors hover:bg-muted"
      >
        <Bell className="size-4" />
        {unread > 0 && (
          <span className="absolute -end-0.5 -top-0.5 grid min-w-4 place-items-center rounded-full bg-danger px-1 text-[10px] font-medium text-danger-foreground">
            {unread}
          </span>
        )}
      </button>
      {open && (
        <div className="absolute end-0 z-50 mt-2 w-80 animate-fade-up overflow-hidden rounded-xl border border-border bg-card shadow-lg">
          <div className="flex items-center justify-between border-b border-border px-3 py-2">
            <span className="text-sm font-medium">{label}</span>
            {unread > 0 && (
              <button
                onClick={() => markAll.mutate()}
                className="text-xs text-primary hover:underline"
              >
                ✓
              </button>
            )}
          </div>
          <div className="max-h-80 overflow-y-auto">
            {items.length === 0 ? (
              <p className="px-3 py-6 text-center text-sm text-muted-foreground">—</p>
            ) : (
              items.map((n) => (
                <div
                  key={n.id}
                  className={cn(
                    "border-b border-border px-3 py-2.5 text-sm last:border-0",
                    !n.read && "bg-primary/5",
                  )}
                >
                  <p className="font-medium">{n.data.title ?? "Reminder"}</p>
                  {n.data.expiry_date && (
                    <p className="text-xs text-muted-foreground">{n.data.expiry_date}</p>
                  )}
                </div>
              ))
            )}
          </div>
        </div>
      )}
    </div>
  );
}

function AccountMenu({
  settingsLabel,
  signOutLabel,
}: {
  settingsLabel: string;
  signOutLabel: string;
}) {
  const { data: user } = useMe();
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", onClick);
    return () => document.removeEventListener("mousedown", onClick);
  }, []);

  const initial = user?.name?.charAt(0)?.toUpperCase() ?? "U";

  return (
    <div ref={ref} className="relative">
      <button
        aria-label="Account"
        onClick={() => setOpen((o) => !o)}
        className="grid size-9 place-items-center rounded-full bg-primary/10 text-sm font-medium text-primary"
      >
        {initial}
      </button>
      {open && (
        <div className="absolute end-0 z-50 mt-2 w-56 animate-fade-up overflow-hidden rounded-xl border border-border bg-card p-1 shadow-lg">
          <div className="border-b border-border px-3 py-2">
            <p className="truncate text-sm font-medium">{user?.name}</p>
            <p className="truncate text-xs text-muted-foreground">{user?.email}</p>
          </div>
          <Link
            href="/settings"
            onClick={() => setOpen(false)}
            className="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-muted"
          >
            <Settings className="size-4" />
            {settingsLabel}
          </Link>
          {user?.is_super_admin && (
            <Link
              href="/admin"
              onClick={() => setOpen(false)}
              className="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-muted"
            >
              <UserIcon className="size-4" />
              Admin
            </Link>
          )}
          <SignOutButton label={signOutLabel} />
        </div>
      )}
    </div>
  );
}

function SignOutButton({ label }: { label: string }) {
  const router = useRouter();
  const logout = useLogout();
  return (
    <button
      onClick={async () => {
        await logout.mutateAsync();
        router.push("/login");
      }}
      className="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-danger hover:bg-muted"
    >
      <LogOut className="size-4" />
      {label}
    </button>
  );
}
