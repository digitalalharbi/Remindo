import { getTranslations, setRequestLocale } from "next-intl/server";
import {
  ArrowRight,
  BellRing,
  CalendarClock,
  FileText,
  RefreshCw,
  Search,
  Users,
  CheckCircle2,
} from "lucide-react";
import { Link } from "@/i18n/navigation";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { AppPreview } from "@/components/marketing/app-preview";

export default async function HomePage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);
  const t = await getTranslations("home");
  const tc = await getTranslations("common");

  const steps = [
    { icon: FileText, title: t("step1Title"), body: t("step1Body") },
    { icon: CalendarClock, title: t("step2Title"), body: t("step2Body") },
    { icon: BellRing, title: t("step3Title"), body: t("step3Body") },
  ];

  const features = [
    { icon: BellRing, title: t("feature1Title"), body: t("feature1Body") },
    { icon: RefreshCw, title: t("feature2Title"), body: t("feature2Body") },
    { icon: FileText, title: t("feature3Title"), body: t("feature3Body") },
    { icon: CalendarClock, title: t("feature4Title"), body: t("feature4Body") },
    { icon: Search, title: t("feature5Title"), body: t("feature5Body") },
    { icon: Users, title: t("feature6Title"), body: t("feature6Body") },
  ];

  return (
    <>
      {/* Hero */}
      <section className="relative overflow-hidden">
        <div className="absolute inset-0 bg-grid" aria-hidden />
        <div className="relative mx-auto max-w-6xl px-4 pb-16 pt-20 sm:px-6 sm:pb-24 sm:pt-28">
          <div className="mx-auto max-w-2xl text-center">
            <span className="inline-flex items-center gap-1.5 rounded-full border border-border bg-card px-3 py-1 text-xs font-medium text-muted-foreground shadow-xs">
              <span className="size-1.5 rounded-full bg-primary" />
              {t("badge")}
            </span>
            <h1 className="mt-6 text-balance text-4xl font-semibold tracking-tight sm:text-6xl">
              {t("heroTitle")}
            </h1>
            <p className="mx-auto mt-5 max-w-xl text-pretty text-lg text-muted-foreground">
              {t("heroSubtitle")}
            </p>
            <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
              <Link
                href="/register"
                className={cn(buttonVariants({ size: "lg" }), "w-full sm:w-auto")}
              >
                {t("heroPrimary")}
                <ArrowRight className="size-4 rtl:rotate-180" />
              </Link>
              <Link
                href="/how-it-works"
                className={cn(
                  buttonVariants({ variant: "secondary", size: "lg" }),
                  "w-full sm:w-auto",
                )}
              >
                {t("heroSecondary")}
              </Link>
            </div>
            <p className="mt-4 text-sm text-muted-foreground">{t("heroNote")}</p>
          </div>

          <div className="mx-auto mt-14 max-w-4xl animate-fade-up">
            <AppPreview locale={locale} />
          </div>
        </div>
      </section>

      {/* Steps */}
      <section id="how" className="border-t border-border bg-muted/30 py-20 sm:py-28">
        <div className="mx-auto max-w-6xl px-4 sm:px-6">
          <div className="mx-auto max-w-2xl text-center">
            <h2 className="text-3xl font-semibold tracking-tight sm:text-4xl">
              {t("stepsTitle")}
            </h2>
            <p className="mt-3 text-lg text-muted-foreground">{t("stepsSubtitle")}</p>
          </div>
          <div className="mt-14 grid gap-8 sm:grid-cols-3">
            {steps.map((s, i) => (
              <div key={i} className="relative text-center sm:text-start">
                <div className="mx-auto grid size-12 place-items-center rounded-xl bg-primary/10 text-primary sm:mx-0">
                  <s.icon className="size-6" />
                </div>
                <div className="mt-4 flex items-center gap-2">
                  <span className="text-sm font-semibold text-primary">
                    {String(i + 1).padStart(2, "0")}
                  </span>
                  <h3 className="text-lg font-semibold">{s.title}</h3>
                </div>
                <p className="mt-1.5 text-muted-foreground">{s.body}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="py-20 sm:py-28">
        <div className="mx-auto max-w-6xl px-4 sm:px-6">
          <div className="mx-auto max-w-2xl text-center">
            <h2 className="text-3xl font-semibold tracking-tight sm:text-4xl">
              {t("featuresTitle")}
            </h2>
          </div>
          <div className="mt-14 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
            {features.map((f, i) => (
              <div key={i}>
                <div className="grid size-11 place-items-center rounded-xl border border-border bg-card text-primary shadow-xs">
                  <f.icon className="size-5" />
                </div>
                <h3 className="mt-4 font-semibold">{f.title}</h3>
                <p className="mt-1.5 text-sm text-muted-foreground">{f.body}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="px-4 pb-24 sm:px-6">
        <div className="relative mx-auto max-w-5xl overflow-hidden rounded-3xl border border-border bg-primary px-6 py-16 text-center text-primary-foreground shadow-lg sm:py-20">
          <h2 className="text-balance text-3xl font-semibold tracking-tight sm:text-4xl">
            {t("ctaTitle")}
          </h2>
          <p className="mx-auto mt-3 max-w-xl text-pretty text-primary-foreground/85">
            {t("ctaSubtitle")}
          </p>
          <div className="mt-8 flex justify-center">
            <Link
              href="/register"
              className="inline-flex h-12 items-center gap-2 rounded-lg bg-background px-7 text-base font-medium text-foreground shadow-sm transition-transform hover:scale-[1.02]"
            >
              {t("ctaButton")}
              <ArrowRight className="size-4 rtl:rotate-180" />
            </Link>
          </div>
          <div className="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-primary-foreground/80">
            <span className="inline-flex items-center gap-1.5">
              <CheckCircle2 className="size-4" /> {tc("tryFree")}
            </span>
          </div>
        </div>
      </section>
    </>
  );
}
