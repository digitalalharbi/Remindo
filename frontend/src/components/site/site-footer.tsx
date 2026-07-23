import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { Logo } from "./logo";

export function SiteFooter() {
  const t = useTranslations("footer");
  const tn = useTranslations("nav");
  const tcat = useTranslations("categories");
  const year = 2026;

  const columns = [
    {
      title: t("product"),
      links: [
        { href: "/features", label: tn("features") },
        { href: "/how-it-works", label: tn("howItWorks") },
        { href: "/pricing", label: tn("pricing") },
        { href: "/integrations", label: tn("integrations") },
        { href: "/security", label: tn("security") },
      ],
    },
    {
      title: t("useCases"),
      links: [
        { href: "/use-cases/contracts", label: tcat("contracts") },
        { href: "/use-cases/licenses", label: tcat("licenses") },
        { href: "/use-cases/insurance", label: tcat("insurance") },
        { href: "/use-cases/subscriptions", label: tcat("subscriptions") },
        { href: "/use-cases/documents", label: tcat("documents") },
        { href: "/use-cases/maintenance", label: tcat("maintenance") },
      ],
    },
    {
      title: t("company"),
      links: [
        { href: "/personal", label: tn("forIndividuals") },
        { href: "/business", label: tn("forBusinesses") },
        { href: "/blog", label: tn("blog") },
        { href: "/contact", label: tn("contact") },
        { href: "/help", label: t("help") },
      ],
    },
    {
      title: t("legal"),
      links: [
        { href: "/terms", label: t("terms") },
        { href: "/privacy", label: t("privacy") },
        { href: "/cookies", label: t("cookies") },
      ],
    },
  ];

  return (
    <footer className="border-t border-border bg-muted/30">
      <div className="mx-auto max-w-6xl px-4 py-14 sm:px-6">
        <div className="grid gap-10 sm:grid-cols-2 md:grid-cols-[1.4fr_1fr_1fr_1fr_1fr]">
          <div className="max-w-xs">
            <Logo />
            <p className="mt-3 text-sm text-muted-foreground">{t("tagline")}</p>
          </div>
          {columns.map((col) => (
            <div key={col.title}>
              <h4 className="text-sm font-semibold">{col.title}</h4>
              <ul className="mt-3 space-y-2.5">
                {col.links.map((l) => (
                  <li key={l.href}>
                    <Link
                      href={l.href}
                      className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                      {l.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
        <div className="mt-12 border-t border-border pt-6 text-sm text-muted-foreground">
          © {year} Remindo. {t("rights")}
        </div>
      </div>
    </footer>
  );
}
