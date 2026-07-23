"use client";

import Link from "next/link";
import { FormEvent, useCallback, useEffect, useState } from "react";
import { api, csrf } from "../../lib/api";
import { appCopy } from "../../lib/app-copy";

type Schedule = { id?: string; scheduled_at: string; channel: string; status?: string };
type Reminder = { id: string; name: string; expires_at: string; status: string; notes?: string; schedules: Schedule[] };
type User = { name: string; email: string; phone?: string; country: string; timezone: string; locale: "ar" | "en"; currency: string };
type Dashboard = {
  counts: { active: number; due_7_days: number; due_30_days: number; overdue: number };
  upcoming: Reminder[];
  activity: Array<{ id: string; subject: string; body: string; read_at: string | null; sent_at: string }>;
  subscription: { plan: { name: string; reminder_limit: number }; current_period_ends_at?: string } | null;
};
type AuthPayload = { data: { user: User } };
type ReminderList = { data: Reminder[] };
type DashboardPayload = { data: Dashboard };
type NotificationsPayload = { data: { data: Dashboard["activity"] } };

const emptyDashboard: Dashboard = {
  counts: { active: 0, due_7_days: 0, due_30_days: 0, overdue: 0 },
  upcoming: [], activity: [], subscription: null,
};

export default function RemindoApp() {
  const [locale, setLocale] = useState<"ar" | "en">("ar");
  const [mode, setMode] = useState<"login" | "register">("login");
  const [section, setSection] = useState(0);
  const [user, setUser] = useState<User | null>(null);
  const [dashboard, setDashboard] = useState(emptyDashboard);
  const [reminders, setReminders] = useState<Reminder[]>([]);
  const [notifications, setNotifications] = useState<Dashboard["activity"]>([]);
  const [editing, setEditing] = useState<Reminder | null>(null);
  const [showForm, setShowForm] = useState(false);
  const [scheduleCount, setScheduleCount] = useState(1);
  const [busy, setBusy] = useState(true);
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");
  const t = appCopy[locale];

  const loadData = useCallback(async () => {
    const [me, list, stats, inbox] = await Promise.all([
      api<{ data: User }>("/auth/me"),
      api<ReminderList>("/reminders?per_page=100"),
      api<DashboardPayload>("/dashboard"),
      api<NotificationsPayload>("/notifications"),
    ]);
    setUser(me.data);
    setLocale(me.data.locale);
    setReminders(list.data);
    setDashboard(stats.data);
    setNotifications(inbox.data.data);
  }, []);

  useEffect(() => {
    // The initial request intentionally owns the authentication/loading state.
    // eslint-disable-next-line react-hooks/set-state-in-effect
    loadData().catch(() => setUser(null)).finally(() => setBusy(false));
  }, [loadData]);

  async function authenticate(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setBusy(true); setError("");
    const form = new FormData(event.currentTarget);
    const password = String(form.get("password"));
    const body = mode === "register"
      ? { name: form.get("name"), email: form.get("email"), password, password_confirmation: password, timezone: form.get("timezone"), locale, country: "SA" }
      : { email: form.get("email"), password, device_name: "remindo-web" };
    try {
      await csrf();
      const result = await api<AuthPayload>(`/auth/${mode}`, { method: "POST", body: JSON.stringify(body) });
      setUser(result.data.user);
      await loadData();
    } catch (requestError) {
      setError(requestError instanceof Error ? requestError.message : t.error);
    } finally { setBusy(false); }
  }

  async function demoLogin() {
    setBusy(true); setError("");
    try {
      await csrf();
      const result = await api<AuthPayload>("/auth/login", {
        method: "POST",
        body: JSON.stringify({ email: "demo@remindo.test", password: "DemoPass123", device_name: "demo-web" }),
      });
      setUser(result.data.user); await loadData();
    } catch (requestError) {
      setError(requestError instanceof Error ? requestError.message : t.error);
    } finally { setBusy(false); }
  }

  async function saveReminder(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); setBusy(true); setError("");
    const form = new FormData(event.currentTarget);
    const schedules = Array.from({ length: scheduleCount }, (_, index) => ({
      channel: form.get(`channel_${index}`),
      scheduled_at: form.get(`scheduled_at_${index}`),
    }));
    try {
      await api(editing ? `/reminders/${editing.id}` : "/reminders", {
        method: editing ? "PUT" : "POST",
        body: JSON.stringify({
          name: form.get("name"), expires_at: form.get("expires_at"), notes: form.get("notes"),
          priority: "normal", recurrence: "none", ...(editing ? {} : { schedules }),
        }),
      });
      setShowForm(false); setEditing(null); setScheduleCount(1); setMessage(t.saved); await loadData();
    } catch (requestError) {
      setError(requestError instanceof Error ? requestError.message : t.error);
    } finally { setBusy(false); }
  }

  async function reminderAction(reminder: Reminder, action: "snooze" | "complete" | "renew" | "delete") {
    setBusy(true); setError("");
    const options: RequestInit = action === "delete"
      ? { method: "DELETE" }
      : {
          method: "POST",
          body: JSON.stringify(action === "snooze"
            ? { until: new Date(Date.now() + 86400000).toISOString() }
            : action === "renew"
              ? { expires_at: new Date(new Date(reminder.expires_at).setFullYear(new Date(reminder.expires_at).getFullYear() + 1)).toISOString().slice(0, 10) }
              : {}),
        };
    try {
      await api(`/reminders/${reminder.id}${action === "delete" ? "" : `/${action}`}`, options);
      await loadData(); setMessage(t.saved);
    } catch (requestError) {
      setError(requestError instanceof Error ? requestError.message : t.error);
    } finally { setBusy(false); }
  }

  async function saveProfile(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); if (!user) return;
    setBusy(true); setError("");
    const form = new FormData(event.currentTarget);
    try {
      const result = await api<{ data: User }>("/profile", {
        method: "PUT",
        body: JSON.stringify({
          name: form.get("name"), phone: form.get("phone"), country: form.get("country"),
          timezone: form.get("timezone"), locale, currency: form.get("currency"),
        }),
      });
      setUser(result.data); setMessage(t.saved);
    } catch (requestError) {
      setError(requestError instanceof Error ? requestError.message : t.error);
    } finally { setBusy(false); }
  }

  async function logout() {
    await api("/auth/logout", { method: "POST" }).catch(() => undefined);
    setUser(null); setReminders([]); setDashboard(emptyDashboard);
  }

  if (busy && !user) return <main className="product-app app-loading" dir={t.dir}>{t.loading}</main>;

  if (!user) return (
    <main className="product-app" dir={t.dir}>
      <header className="product-header">
        <Link className="brand" href="/"><span className="brand-mark"><i /></span><span>Remindo</span></Link>
        <button className="language" onClick={() => setLocale(locale === "ar" ? "en" : "ar")}>{locale === "ar" ? "EN" : "العربية"}</button>
      </header>
      <section className="auth-shell">
        <div className="auth-intro"><span className="eyebrow">Remindo</span><h1>{t.title}</h1><p>{t.subtitle}</p></div>
        <form className="auth-card" onSubmit={authenticate}>
          <div className="auth-tabs">
            <button type="button" className={mode === "login" ? "active" : ""} onClick={() => setMode("login")}>{t.login}</button>
            <button type="button" className={mode === "register" ? "active" : ""} onClick={() => setMode("register")}>{t.register}</button>
          </div>
          {mode === "register" && <label>{t.name}<input name="name" required autoComplete="name" /></label>}
          <label>{t.email}<input name="email" type="email" required autoComplete="email" /></label>
          <label>{t.password}<input name="password" type="password" minLength={10} required autoComplete={mode === "login" ? "current-password" : "new-password"} /></label>
          {mode === "register" && <label>{t.timezone}<select name="timezone" defaultValue="Asia/Riyadh"><option>Asia/Riyadh</option><option>Europe/Istanbul</option><option>Europe/Madrid</option><option>UTC</option></select></label>}
          {error && <p className="form-error" role="alert">{error}</p>}
          <button className="button" disabled={busy}>{busy ? t.loading : t.submit}</button>
          <button className="secondary-button demo-button" type="button" onClick={demoLogin}>{t.demo}</button>
        </form>
      </section>
    </main>
  );

  const metrics = [
    [t.active, dashboard.counts.active],
    [t.seven, dashboard.counts.due_7_days],
    [t.thirty, dashboard.counts.due_30_days],
    [t.overdue, dashboard.counts.overdue],
  ];

  return (
    <main className="product-app dashboard-app" dir={t.dir}>
      <aside className="app-sidebar">
        <Link className="brand" href="/"><span className="brand-mark"><i /></span><span>Remindo</span></Link>
        <nav>{t.nav.map((item, index) => <button key={item} className={section === index ? "active" : ""} onClick={() => setSection(index)}><span>{["⌂", "◫", "□", "○", "◇", "⚙"][index]}</span>{item}</button>)}</nav>
        <button className="sidebar-logout" onClick={logout}>{t.logout}</button>
      </aside>
      <section className="app-content">
        <header className="app-content-header">
          <div><span>{t.welcome}، {user.name}</span><h1>{t.nav[section]}</h1></div>
          <div><button className="language" onClick={() => setLocale(locale === "ar" ? "en" : "ar")}>{locale === "ar" ? "EN" : "العربية"}</button><button className="button small" onClick={() => { setEditing(null); setShowForm(true); }}>{t.add}</button></div>
        </header>
        {message && <div className="success-banner" role="status">{message}<button onClick={() => setMessage("")}>×</button></div>}
        {error && <p className="form-error" role="alert">{error}</p>}

        {section === 0 && <><div className="metric-grid">{metrics.map(([label, value]) => <article className="metric-card" key={String(label)}><span>{label}</span><strong>{value}</strong></article>)}</div><div className="overview-grid"><DataList title={t.upcoming} reminders={dashboard.upcoming} empty={t.empty} /><ActivityList title={t.activity} activity={dashboard.activity} empty={t.empty} /></div></>}
        {section === 1 && <ReminderTable reminders={reminders} t={t} onEdit={(item) => { setEditing(item); setShowForm(true); }} onAction={reminderAction} />}
        {section === 2 && <div className="calendar-grid">{reminders.map((item) => <article key={item.id}><time>{item.expires_at}</time><strong>{item.name}</strong><span>{item.status}</span></article>)}</div>}
        {section === 3 && <ActivityList title={t.notifications} activity={notifications} empty={t.empty} action={<button className="secondary-button" onClick={async () => { await api("/notifications/read-all", { method: "POST" }); await loadData(); }}>{t.markRead}</button>} />}
        {section === 4 && <div className="billing-card"><span>{t.plan}</span><h2>{dashboard.subscription?.plan.name ?? "Free"}</h2><p>{t.usage}: {dashboard.counts.active} / {dashboard.subscription?.plan.reminder_limit ?? 5}</p><div className="usage-bar"><i style={{ width: `${Math.min(100, dashboard.counts.active / (dashboard.subscription?.plan.reminder_limit ?? 5) * 100)}%` }} /></div></div>}
        {section === 5 && <form className="settings-card" onSubmit={saveProfile}><h2>{t.settings}</h2><label>{t.name}<input name="name" defaultValue={user.name} required /></label><label>{t.phone}<input name="phone" defaultValue={user.phone} /></label><label>{t.country}<input name="country" defaultValue={user.country} maxLength={2} required /></label><label>{t.timezone}<select name="timezone" defaultValue={user.timezone}><option>Asia/Riyadh</option><option>Europe/Istanbul</option><option>Europe/Madrid</option><option>UTC</option></select></label><label>{t.currency}<select name="currency" defaultValue={user.currency}><option>SAR</option><option>USD</option><option>EUR</option><option>TRY</option></select></label><button className="button">{t.saveSettings}</button></form>}
      </section>

      {showForm && <div className="modal-backdrop" onMouseDown={() => setShowForm(false)}><form className="modal reminder-form" onSubmit={saveReminder} onMouseDown={(event) => event.stopPropagation()}><button className="modal-close" type="button" onClick={() => setShowForm(false)}>×</button><h2>{editing ? t.edit : t.add}</h2><label>{t.reminderName}<input name="name" defaultValue={editing?.name} required /></label><label>{t.expiry}<input name="expires_at" type="date" defaultValue={editing?.expires_at} required /></label><label>Notes<textarea name="notes" defaultValue={editing?.notes} /></label>{!editing && <><h3>{t.schedules}</h3>{Array.from({ length: scheduleCount }, (_, index) => <div className="schedule-row" key={index}><label>{t.notificationAt}<input name={`scheduled_at_${index}`} type="datetime-local" required /></label><label>{t.channel}<select name={`channel_${index}`}><option value="in_app">Remindo</option><option value="email">Email</option><option value="sms">SMS Mock</option><option value="whatsapp">WhatsApp Mock</option></select></label></div>)}<button className="secondary-button" type="button" onClick={() => setScheduleCount(Math.min(10, scheduleCount + 1))}>{t.addSchedule}</button></>}<div className="modal-actions"><button className="secondary-button" type="button" onClick={() => setShowForm(false)}>{t.cancel}</button><button className="button" disabled={busy}>{t.save}</button></div></form></div>}
    </main>
  );
}

function DataList({ title, reminders, empty }: { title: string; reminders: Reminder[]; empty: string }) {
  return <section className="data-panel"><h2>{title}</h2>{reminders.length ? reminders.map((item) => <article key={item.id}><div><strong>{item.name}</strong><span>{item.expires_at}</span></div><small>{item.status}</small></article>) : <p>{empty}</p>}</section>;
}

function ActivityList({ title, activity, empty, action }: { title: string; activity: Dashboard["activity"]; empty: string; action?: React.ReactNode }) {
  return <section className="data-panel"><header><h2>{title}</h2>{action}</header>{activity.length ? activity.map((item) => <article key={item.id}><div><strong>{item.subject}</strong><span>{item.body}</span></div><small>{item.read_at ? "✓" : "●"}</small></article>) : <p>{empty}</p>}</section>;
}

function ReminderTable({ reminders, t, onEdit, onAction }: { reminders: Reminder[]; t: typeof appCopy.ar | typeof appCopy.en; onEdit: (item: Reminder) => void; onAction: (item: Reminder, action: "snooze" | "complete" | "renew" | "delete") => void }) {
  return <section className="reminder-table">{reminders.length ? reminders.map((item) => <article key={item.id}><div><strong>{item.name}</strong><span>{item.expires_at} · {item.status}</span></div><div className="row-actions"><button onClick={() => onEdit(item)}>{t.edit}</button><button onClick={() => onAction(item, "snooze")}>{t.snooze}</button><button onClick={() => onAction(item, "complete")}>{t.complete}</button><button onClick={() => onAction(item, "renew")}>{t.renew}</button><button className="danger-action" onClick={() => onAction(item, "delete")}>{t.remove}</button></div></article>) : <p className="empty-state">{t.empty}</p>}</section>;
}
