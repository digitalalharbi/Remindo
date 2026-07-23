import type { Metadata } from "next";
import { Inter, IBM_Plex_Sans_Arabic } from "next/font/google";
import { notFound } from "next/navigation";
import { hasLocale, NextIntlClientProvider } from "next-intl";
import { setRequestLocale } from "next-intl/server";
import { routing, isRtl } from "@/i18n/routing";
import { Providers } from "@/components/providers";
import { ToastProvider } from "@/components/ui/toast";
import "../globals.css";

const inter = Inter({
  subsets: ["latin"],
  variable: "--font-inter",
  display: "swap",
});

const plexArabic = IBM_Plex_Sans_Arabic({
  subsets: ["arabic"],
  weight: ["400", "500", "600", "700"],
  variable: "--font-arabic",
  display: "swap",
});

export function generateStaticParams() {
  return routing.locales.map((locale) => ({ locale }));
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const isArabic = locale === "ar";

  return {
    metadataBase: new URL("https://remindo.me"),
    title: {
      default: isArabic
        ? "تذكير بانتهاء الصلاحية | ذكّرني"
        : "Expiration Reminder Software | Remindo",
      template: "%s | Remindo",
    },
    description: isArabic
      ? "كل مواعيد الانتهاء والتجديد في مكان واحد. Remindo يذكّرك قبل انتهاء العقود والتراخيص والتأمينات والاشتراكات والوثائق المهمة."
      : "Track contracts, licenses, insurance, subscriptions, documents, and renewals in one simple place. Never miss an expiration date.",
    alternates: {
      canonical: `/${locale}`,
      languages: {
        ar: "/ar",
        en: "/en",
        es: "/es",
        tr: "/tr",
      },
    },
    openGraph: {
      type: "website",
      siteName: "Remindo",
      locale,
      url: `https://remindo.me/${locale}`,
    },
    twitter: { card: "summary_large_image" },
  };
}

export default async function LocaleLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }
  setRequestLocale(locale);

  const dir = isRtl(locale) ? "rtl" : "ltr";

  return (
    <html
      lang={locale}
      dir={dir}
      suppressHydrationWarning
      className={`${inter.variable} ${plexArabic.variable}`}
    >
      <body className="min-h-dvh antialiased">
        <NextIntlClientProvider>
          <Providers locale={locale}>
            <ToastProvider>{children}</ToastProvider>
          </Providers>
        </NextIntlClientProvider>
      </body>
    </html>
  );
}
