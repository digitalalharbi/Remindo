"use client";

import { FormEvent, useCallback, useState } from "react";
import Link from "next/link";
import { api } from "../../lib/api";
import { appCopy } from "../../lib/app-copy";

type Reminder = {
  id: string;
  name: string;
  expires_at: string;
  status: string;
  schedules: Array<{ id: string; scheduled_at: string; channel: string; status: string }>;
};

type AuthPayload = { data: { token: string; user: { name: string } } };
type ReminderList = { data: Reminder[] };

export default function RemindoApp() {
  const [locale, setLocale] = useState<"ar" | "en">("ar");
  const [mode, setMode] = useState<"login" | "register">("login");
  const [token, setToken] = useState("");
  const [userName, setUserName] = useState("");
  const [reminders, setReminders] = useState<Reminder[]>([]);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");
  const t = appCopy[locale];

  const loadReminders = useCallback(async (authToken: string) => {
    const result = await api<ReminderList>("/reminders", { token: authToken });
    setReminders(result.data);
  }, []);

  async function authenticate(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setBusy(true);
    setError("");
    const form = new FormData(event.currentTarget);
    const password = String(form.get("password"));
    const body = mode === "register"
      ? {
          name: form.get("name"),
          email: form.get("email"),
          password,
          password_confirmation: password,
          timezone: form.get("timezone"),
          locale,
          country: "SA",
        }
      : { email: form.get("email"), password, device_name: "remindo-web" };

    try {
      const result = await api<AuthPayload>(`/auth/${mode}`, { method: "POST", body: JSON.stringify(body) });
      setToken(result.data.token);
      setUserName(result.data.user.name);
      await loadReminders(result.data.token);
    } catch (requestError) {
      setError(requestError instanceof Error ? requestError.message : t.error);
    } finally {
      setBusy(false);
    }
  }

  async function createReminder(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setBusy(true);
    setError("");
    const form = new FormData(event.currentTarget);
    try {
      await api("/reminders", {
        method: "POST",
        token,
        body: JSON.stringify({
          name: form.get("name"),
          expires_at: form.get("expires_at"),
          priority: "normal",
          recurrence: "none",
          schedules: [{ channel: form.get("channel"), scheduled_at: form.get("scheduled_at") }],
        }),
      });
      event.currentTarget.reset();
      await loadReminders(token);
    } catch (requestError) {
      setError(requestError instanceof Error ? requestError.message : t.error);
    } finally {
      setBusy(false);
    }
  }

  async function logout() {
    await api("/auth/logout", { method: "POST", token }).catch(() => undefined);
    setToken("");
    setUserName("");
    setReminders([]);
  }

  return (
    <main className="product-app" dir={t.dir}>
      <header className="product-header">
        <Link className="brand" href="/"><span className="brand-mark"><i /></span><span>Remindo</span></Link>
        <div className="product-header-actions">
          <button className="language" onClick={() => setLocale(locale === "ar" ? "en" : "ar")}>{locale === "ar" ? "EN" : "العربية"}</button>
          {token && <button className="secondary-button" onClick={logout}>{t.logout}</button>}
        </div>
      </header>

      {!token ? (
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
          </form>
        </section>
      ) : (
        <section className="dashboard-shell">
          <div className="dashboard-heading"><div><span>{userName}</span><h1>{t.reminders}</h1></div><span className="live-badge">● API</span></div>
          <div className="dashboard-grid">
            <form className="create-card" onSubmit={createReminder}>
              <h2>{t.add}</h2>
              <label>{t.reminderName}<input name="name" required /></label>
              <label>{t.expiry}<input name="expires_at" type="date" required /></label>
              <label>{t.notificationAt}<input name="scheduled_at" type="datetime-local" required /></label>
              <label>{t.channel}<select name="channel"><option value="in_app">Remindo</option><option value="email">Email</option><option value="sms">SMS Mock</option><option value="whatsapp">WhatsApp Mock</option></select></label>
              {error && <p className="form-error" role="alert">{error}</p>}
              <button className="button" disabled={busy}>{busy ? t.loading : t.save}</button>
            </form>
            <div className="real-reminders">
              {busy && reminders.length === 0 ? <p className="empty-state">{t.loading}</p> : reminders.length === 0 ? <p className="empty-state">{t.empty}</p> : reminders.map((reminder) => (
                <article className="real-reminder" key={reminder.id}>
                  <div><h3>{reminder.name}</h3><p>{reminder.expires_at}</p></div>
                  <div className="reminder-meta"><span>{reminder.status}</span><span>{reminder.schedules[0]?.channel}</span><span>{reminder.schedules[0]?.status}</span></div>
                </article>
              ))}
            </div>
          </div>
        </section>
      )}
    </main>
  );
}
