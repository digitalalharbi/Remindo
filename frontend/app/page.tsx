"use client";

import { useMemo, useState } from "react";

const copy = {
  ar: {
    dir: "rtl",
    nav: ["الرئيسية", "كيف يعمل", "الأسعار", "للشركات"],
    signIn: "تسجيل الدخول",
    start: "ابدأ مجانًا",
    eyebrow: "كل مواعيدك المهمة في مكان واحد",
    title: "لا تفوّت موعد انتهاء مهم.",
    lead: "Remindo يذكّرك قبل انتهاء العقود والتراخيص والتأمينات والاشتراكات—ببساطة وبدون تعقيد.",
    primary: "أنشئ أول تذكير",
    secondary: "شاهد كيف يعمل",
    trust: "مجاني للبدء · بدون بطاقة · إعداد خلال 30 ثانية",
    previewTitle: "المواعيد القريبة",
    viewAll: "عرض الكل",
    today: "اليوم",
    days: "أيام",
    overdue: "متأخر",
    add: "تذكير جديد",
    thisWeek: "هذا الأسبوع",
    howTitle: "أضف التاريخ. واترك الباقي علينا.",
    howLead: "ثلاث خطوات بسيطة تفصلك عن راحة البال.",
    steps: [
      ["01", "أضف ما تريد تذكّره", "اكتب الاسم وحدد تاريخ الانتهاء."],
      ["02", "اختر وقت التنبيه", "قبل يوم، أسبوع، شهر، أو وقت تختاره."],
      ["03", "استلم التذكير", "عبر البريد أو الجوال أو داخل Remindo."],
    ],
    modalTitle: "تذكير جديد",
    modalSub: "أضف الأساسيات الآن، ويمكنك ضبط التفاصيل لاحقًا.",
    what: "وش تبغى نذكّرك فيه؟",
    when: "متى ينتهي؟",
    remind: "متى تحب نذكّرك؟",
    channel: "كيف تحب يصلك التذكير؟",
    itemPlaceholder: "مثال: تأمين السيارة",
    before: "قبل 7 أيام",
    email: "البريد الإلكتروني",
    cancel: "إلغاء",
    save: "حفظ التذكير",
    saved: "تم حفظ التذكير.",
  },
  en: {
    dir: "ltr",
    nav: ["Home", "How it works", "Pricing", "For business"],
    signIn: "Sign in",
    start: "Start free",
    eyebrow: "Every important date in one place",
    title: "Never miss an important expiration.",
    lead: "Remindo reminds you before contracts, licenses, insurance, and subscriptions expire—simply, without the clutter.",
    primary: "Create your first reminder",
    secondary: "See how it works",
    trust: "Free to start · No card · Set up in 30 seconds",
    previewTitle: "Coming up",
    viewAll: "View all",
    today: "Today",
    days: "days",
    overdue: "Overdue",
    add: "New reminder",
    thisWeek: "This week",
    howTitle: "Add the date. Leave the rest to us.",
    howLead: "Three simple steps to peace of mind.",
    steps: [
      ["01", "Add what matters", "Name it and choose the expiration date."],
      ["02", "Choose when to know", "A day, week, month, or custom time before."],
      ["03", "Get the reminder", "By email, mobile, or right inside Remindo."],
    ],
    modalTitle: "New reminder",
    modalSub: "Add the essentials now. Fine-tune the details later.",
    what: "What should we remind you about?",
    when: "When does it expire?",
    remind: "When should we remind you?",
    channel: "How should we reach you?",
    itemPlaceholder: "Example: Car insurance",
    before: "7 days before",
    email: "Email",
    cancel: "Cancel",
    save: "Save reminder",
    saved: "Reminder saved.",
  },
} as const;

const reminders = {
  ar: [
    ["تأمين السيارة", "اليوم", "danger", "تأمين"],
    ["اشتراك Adobe", "بعد 7 أيام", "warning", "اشتراك"],
    ["رخصة البلدية", "بعد 18 يومًا", "calm", "ترخيص"],
  ],
  en: [
    ["Car insurance", "Today", "danger", "Insurance"],
    ["Adobe subscription", "In 7 days", "warning", "Subscription"],
    ["Business license", "In 18 days", "calm", "License"],
  ],
};

export default function Home() {
  const [language, setLanguage] = useState<"ar" | "en">("ar");
  const [dark, setDark] = useState(false);
  const [modal, setModal] = useState(false);
  const [saved, setSaved] = useState(false);
  const [name, setName] = useState("");
  const t = copy[language];
  const today = useMemo(() => new Date().toISOString().slice(0, 10), []);

  function saveReminder(event: React.FormEvent) {
    event.preventDefault();
    setModal(false);
    setSaved(true);
    setName("");
    window.setTimeout(() => setSaved(false), 3200);
  }

  return (
    <main className={dark ? "site dark" : "site"} dir={t.dir}>
      <header className="nav-wrap">
        <nav className="nav shell" aria-label="Main navigation">
          <a className="brand" href="#top" aria-label="Remindo home">
            <span className="brand-mark" aria-hidden="true"><i /></span>
            <span>Remindo</span>
          </a>
          <div className="nav-links">
            {t.nav.map((item, index) => <a href={index === 1 ? "#how" : "#top"} key={item}>{item}</a>)}
          </div>
          <div className="nav-actions">
            <button className="icon-button" onClick={() => setDark(!dark)} aria-label="Toggle color theme">
              {dark ? "☀" : "◐"}
            </button>
            <button className="language" onClick={() => setLanguage(language === "ar" ? "en" : "ar")}>
              {language === "ar" ? "EN" : "العربية"}
            </button>
            <button className="text-button">{t.signIn}</button>
            <button className="button small" onClick={() => setModal(true)}>{t.start}</button>
          </div>
        </nav>
      </header>

      <section className="hero shell" id="top">
        <div className="hero-copy">
          <div className="eyebrow"><span>✓</span>{t.eyebrow}</div>
          <h1>{t.title}</h1>
          <p className="lead">{t.lead}</p>
          <div className="hero-actions">
            <button className="button" onClick={() => setModal(true)}>{t.primary}<span aria-hidden="true">←</span></button>
            <a className="secondary-button" href="#how"><span className="play">▶</span>{t.secondary}</a>
          </div>
          <p className="trust"><span>✓</span>{t.trust}</p>
        </div>

        <div className="product-stage" aria-label="Remindo product preview">
          <div className="soft-orbit orbit-one" />
          <div className="soft-orbit orbit-two" />
          <div className="app-card">
            <div className="app-top">
              <div>
                <span className="mini-label">{t.thisWeek}</span>
                <h2>{t.previewTitle}</h2>
              </div>
              <button className="round-add" onClick={() => setModal(true)} aria-label={t.add}>＋</button>
            </div>
            <div className="summary-row">
              <div><b>3</b><span>{t.previewTitle}</span></div>
              <div><b className="red">1</b><span>{t.overdue}</span></div>
              <a href="#how">{t.viewAll} <span>←</span></a>
            </div>
            <div className="reminder-list">
              {reminders[language].map(([title, date, tone, tag], index) => (
                <article className="reminder" key={title}>
                  <div className={`date-tile ${tone}`}>
                    <span>{index === 0 ? "12" : index === 1 ? "19" : "30"}</span>
                    <small>{language === "ar" ? "يونيو" : "JUN"}</small>
                  </div>
                  <div className="reminder-copy">
                    <h3>{title}</h3>
                    <p><span className={`status-dot ${tone}`} />{date}</p>
                  </div>
                  <span className="tag">{tag}</span>
                  <button className="more" aria-label="More options">•••</button>
                </article>
              ))}
            </div>
            <button className="add-wide" onClick={() => setModal(true)}>＋ {t.add}</button>
          </div>
          <div className="floating-alert">
            <span className="bell">♧</span>
            <div><b>{language === "ar" ? "تنبيه قريب" : "Coming up"}</b><small>{language === "ar" ? "باقي 7 أيام على الموعد" : "7 days until renewal"}</small></div>
            <span className="check">✓</span>
          </div>
        </div>
      </section>

      <section className="how shell" id="how">
        <div className="section-heading">
          <span>{language === "ar" ? "كيف يعمل" : "HOW IT WORKS"}</span>
          <h2>{t.howTitle}</h2>
          <p>{t.howLead}</p>
        </div>
        <div className="steps">
          {t.steps.map(([number, title, description]) => (
            <article className="step" key={number}>
              <span>{number}</span>
              <h3>{title}</h3>
              <p>{description}</p>
            </article>
          ))}
        </div>
      </section>

      {modal && (
        <div className="modal-backdrop" role="presentation" onMouseDown={() => setModal(false)}>
          <form className="modal" onSubmit={saveReminder} onMouseDown={(e) => e.stopPropagation()}>
            <button className="modal-close" type="button" onClick={() => setModal(false)} aria-label="Close">×</button>
            <div className="modal-icon">◇</div>
            <h2>{t.modalTitle}</h2>
            <p>{t.modalSub}</p>
            <label>{t.what}<input autoFocus required value={name} onChange={(e) => setName(e.target.value)} placeholder={t.itemPlaceholder} /></label>
            <div className="field-grid">
              <label>{t.when}<input required type="date" min={today} /></label>
              <label>{t.remind}<select defaultValue="7"><option value="1">1</option><option value="7">{t.before}</option><option value="30">30</option></select></label>
            </div>
            <label>{t.channel}<select defaultValue="email"><option value="email">{t.email}</option><option value="app">Remindo</option></select></label>
            <div className="modal-actions">
              <button className="secondary-button" type="button" onClick={() => setModal(false)}>{t.cancel}</button>
              <button className="button" type="submit">{t.save}</button>
            </div>
          </form>
        </div>
      )}

      {saved && <div className="toast" role="status"><span>✓</span>{t.saved}</div>}
    </main>
  );
}
