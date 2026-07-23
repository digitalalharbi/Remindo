import { getTranslations, setRequestLocale } from "next-intl/server";
import { pageMetadata, jsonLd } from "@/lib/seo";
import { PageHero } from "@/components/marketing/page-sections";

type Faq = {
  id: string;
  question: Record<string, string>;
  answer: Record<string, string>;
  category: string;
};

const API = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api";

async function loadFaqs(): Promise<Faq[]> {
  try {
    const res = await fetch(`${API}/faqs`, { cache: "no-store" });
    if (!res.ok) return [];
    const json = await res.json();
    return json.data ?? [];
  } catch {
    return [];
  }
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "nav" });
  return pageMetadata({
    locale,
    path: "/faq",
    title: `${t("faq")} — Remindo`,
    description:
      locale === "ar"
        ? "أجوبة عن الأسئلة الشائعة حول Remindo — الخطط والخصوصية والتذكيرات."
        : "Answers to common questions about Remindo — plans, privacy, and reminders.",
  });
}

export default async function FaqPage({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const t = await getTranslations("nav");
  const faqs = await loadFaqs();

  const pick = (m: Record<string, string>) => m[locale] ?? m.en ?? Object.values(m)[0] ?? "";

  // FAQPage structured data (only for questions actually shown).
  const schema = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    mainEntity: faqs.map((f) => ({
      "@type": "Question",
      name: pick(f.question),
      acceptedAnswer: { "@type": "Answer", text: pick(f.answer) },
    })),
  };

  return (
    <>
      {faqs.length > 0 && (
        <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: jsonLd(schema) }} />
      )}
      <PageHero
        title={t("faq")}
        subtitle={locale === "ar" ? "أجوبة مباشرة عن أكثر ما يُسأل." : "Straight answers to the questions we hear most."}
      />
      <section className="mx-auto max-w-3xl px-4 py-16 sm:px-6">
        {faqs.length === 0 ? (
          <p className="text-center text-muted-foreground">—</p>
        ) : (
          <div className="divide-y divide-border">
            {faqs.map((f) => (
              <div key={f.id} className="py-6">
                <h2 className="text-lg font-semibold">{pick(f.question)}</h2>
                <p className="mt-2 leading-relaxed text-muted-foreground">{pick(f.answer)}</p>
              </div>
            ))}
          </div>
        )}
      </section>
    </>
  );
}
