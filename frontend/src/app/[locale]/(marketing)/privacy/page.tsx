import { setRequestLocale } from "next-intl/server";
import { marketing } from "@/content/marketing";
import { pageMetadata } from "@/lib/seo";
import { LegalPage } from "@/components/marketing/page-sections";

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  const c = marketing(locale).legal.privacy;
  return pageMetadata({ locale, path: "/privacy", title: c.seoTitle, description: c.seoDescription });
}

export default async function Page({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  return <LegalPage content={marketing(locale).legal.privacy} />;
}
