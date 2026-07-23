import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  metadataBase: new URL("https://remindo.me"),
  title: "Expiration Reminder Software | Remindo",
  description: "Track contracts, licenses, insurance, subscriptions, documents, and renewals in one simple place.",
  applicationName: "Remindo",
  alternates: {
    canonical: "/",
    languages: { "ar": "/ar", "en": "/en", "es": "/es", "tr": "/tr" },
  },
  openGraph: {
    title: "Never miss an expiration date | Remindo",
    description: "Every important expiration and renewal in one simple place.",
    url: "https://remindo.me",
    siteName: "Remindo",
    locale: "ar_SA",
    type: "website",
  },
  robots: { index: true, follow: true },
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="ar">
      <body>{children}</body>
    </html>
  );
}
