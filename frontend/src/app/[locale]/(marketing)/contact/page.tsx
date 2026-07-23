import { setRequestLocale } from "next-intl/server";
import { marketing } from "@/content/marketing";
import { pageMetadata } from "@/lib/seo";
import { PageHero } from "@/components/marketing/page-sections";
import { ContactForm } from "./contact-form";

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  const c = marketing(locale).contact;
  return pageMetadata({ locale, path: "/contact", title: c.seoTitle, description: c.seoDescription });
}

export default async function ContactPage({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const c = marketing(locale).contact;

  return (
    <>
      <PageHero title={c.heroTitle} subtitle={c.heroSubtitle} />
      <section className="mx-auto max-w-xl px-4 py-16 sm:px-6">
        <ContactForm content={c} />
      </section>
    </>
  );
}
