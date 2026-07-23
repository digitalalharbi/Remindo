import { getTranslations } from "next-intl/server";
import { ArrowRight, Check } from "lucide-react";
import { Link } from "@/i18n/navigation";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import type { PageContent, UseCaseContent, LegalContent } from "@/content/marketing/types";

export function PageHero({ title, subtitle }: { title: string; subtitle: string }) {
  return (
    <section className="relative overflow-hidden border-b border-border">
      <div className="absolute inset-0 bg-grid" aria-hidden />
      <div className="relative mx-auto max-w-3xl px-4 py-20 text-center sm:px-6 sm:py-28">
        <h1 className="text-balance text-4xl font-semibold tracking-tight sm:text-5xl">{title}</h1>
        <p className="mx-auto mt-5 max-w-2xl text-pretty text-lg text-muted-foreground">{subtitle}</p>
      </div>
    </section>
  );
}

export function SectionList({ sections }: { sections: PageContent["sections"] }) {
  if (!sections.length) return null;
  return (
    <section className="mx-auto max-w-5xl px-4 py-16 sm:px-6 sm:py-20">
      <div className="grid gap-x-10 gap-y-10 sm:grid-cols-2">
        {sections.map((s, i) => (
          <div key={i}>
            <h2 className="text-lg font-semibold">{s.title}</h2>
            <p className="mt-2 text-muted-foreground">{s.body}</p>
          </div>
        ))}
      </div>
    </section>
  );
}

export async function CTASection() {
  const t = await getTranslations("home");
  return (
    <section className="px-4 pb-24 sm:px-6">
      <div className="mx-auto max-w-4xl rounded-3xl border border-border bg-primary px-6 py-14 text-center text-primary-foreground shadow-lg">
        <h2 className="text-balance text-2xl font-semibold tracking-tight sm:text-3xl">{t("ctaTitle")}</h2>
        <p className="mx-auto mt-3 max-w-lg text-primary-foreground/85">{t("ctaSubtitle")}</p>
        <div className="mt-7 flex justify-center">
          <Link
            href="/register"
            className="inline-flex h-12 items-center gap-2 rounded-lg bg-background px-7 text-base font-medium text-foreground shadow-sm transition-transform hover:scale-[1.02]"
          >
            {t("ctaButton")}
            <ArrowRight className="size-4 rtl:rotate-180" />
          </Link>
        </div>
      </div>
    </section>
  );
}

/** Full page for a content page (features, security, etc.). */
export async function ContentPage({ content }: { content: PageContent }) {
  return (
    <>
      <PageHero title={content.heroTitle} subtitle={content.heroSubtitle} />
      <SectionList sections={content.sections} />
      <CTASection />
    </>
  );
}

/** Full page for a use-case (contracts, insurance, etc.) with a "what you can track" list. */
export async function UseCasePage({ content }: { content: UseCaseContent }) {
  const t = await getTranslations("home");
  return (
    <>
      <PageHero title={content.heroTitle} subtitle={content.heroSubtitle} />
      <section className="mx-auto max-w-5xl px-4 py-12 sm:px-6">
        <div className="rounded-2xl border border-border bg-card p-6 sm:p-8">
          <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {content.tracks.map((item) => (
              <div key={item} className="flex items-center gap-2.5">
                <Check className="size-4 shrink-0 text-success" />
                <span className="text-sm">{item}</span>
              </div>
            ))}
          </div>
        </div>
      </section>
      <SectionList sections={content.sections} />
      <div className="flex justify-center pb-4">
        <Link href="/register" className={cn(buttonVariants({ size: "lg" }))}>
          {t("ctaButton")}
          <ArrowRight className="size-4 rtl:rotate-180" />
        </Link>
      </div>
      <CTASection />
    </>
  );
}

/** Legal / prose page (terms, privacy, cookies). */
export function LegalPage({ content }: { content: LegalContent }) {
  return (
    <div className="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-24">
      <h1 className="text-3xl font-semibold tracking-tight">{content.title}</h1>
      <p className="mt-2 text-sm text-muted-foreground">{content.updated}</p>
      <div className="mt-10 space-y-8">
        {content.sections.map((s, i) => (
          <section key={i}>
            <h2 className="text-lg font-semibold">{s.title}</h2>
            <p className="mt-2 leading-relaxed text-muted-foreground">{s.body}</p>
          </section>
        ))}
      </div>
    </div>
  );
}
