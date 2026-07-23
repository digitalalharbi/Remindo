import { setRequestLocale } from "next-intl/server";
import { SiteHeader } from "@/components/site/site-header";
import { SiteFooter } from "@/components/site/site-footer";
import {
  jsonLd,
  organizationSchema,
  websiteSchema,
  softwareApplicationSchema,
} from "@/lib/seo";

export default async function MarketingLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);

  return (
    <div className="flex min-h-dvh flex-col">
      {/* Site-wide structured data (Organization / WebSite / SoftwareApplication). */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: jsonLd({
            "@context": "https://schema.org",
            "@graph": [organizationSchema, websiteSchema, softwareApplicationSchema],
          }),
        }}
      />
      <SiteHeader />
      <main className="flex-1">{children}</main>
      <SiteFooter />
    </div>
  );
}
