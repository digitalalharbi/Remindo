import { setRequestLocale } from "next-intl/server";
import { PricingTable } from "@/components/marketing/pricing-table";

export default async function PricingPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);

  return (
    <section className="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
      <PricingTable locale={locale} />
    </section>
  );
}
