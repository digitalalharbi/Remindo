import { setRequestLocale } from "next-intl/server";
import { marketing } from "@/content/marketing";
import { pageMetadata } from "@/lib/seo";
import { UseCasePage } from "@/components/marketing/page-sections";

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  const c = marketing(locale).useCases.maintenance;
  return pageMetadata({ locale, path: "/use-cases/maintenance", title: c.seoTitle, description: c.seoDescription });
}

export default async function Page({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  return <UseCasePage content={marketing(locale).useCases.maintenance} />;
}
