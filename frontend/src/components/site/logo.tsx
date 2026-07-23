import { BellRing } from "lucide-react";
import { Link } from "@/i18n/navigation";
import { cn } from "@/lib/utils";

export function Logo({
  className,
  href = "/",
}: {
  className?: string;
  href?: string;
}) {
  return (
    <Link
      href={href}
      className={cn("inline-flex items-center gap-2 font-semibold", className)}
      aria-label="Remindo"
    >
      <span className="grid size-8 place-items-center rounded-lg bg-primary text-primary-foreground shadow-xs">
        <BellRing className="size-4.5" strokeWidth={2.25} />
      </span>
      <span className="text-lg tracking-tight">Remindo</span>
    </Link>
  );
}
