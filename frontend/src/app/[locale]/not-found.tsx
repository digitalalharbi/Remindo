import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import { buttonVariants } from "@/components/ui/button";
import { Logo } from "@/components/site/logo";

export default async function NotFound() {
  const t = await getTranslations("common");

  return (
    <div className="flex min-h-dvh flex-col items-center justify-center px-4 text-center">
      <Logo />
      <p className="mt-8 text-6xl font-semibold tracking-tight text-primary">404</p>
      <h1 className="mt-3 text-2xl font-semibold tracking-tight">
        Page not found
      </h1>
      <p className="mt-2 max-w-sm text-muted-foreground">
        The page you’re looking for doesn’t exist or has moved.
      </p>
      <Link href="/" className={`${buttonVariants()} mt-6`}>
        {t("back")}
      </Link>
    </div>
  );
}
