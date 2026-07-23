import { setRequestLocale } from "next-intl/server";
import { marketing } from "@/content/marketing";
import { pageMetadata } from "@/lib/seo";
import { PageHero } from "@/components/marketing/page-sections";
import { Link } from "@/i18n/navigation";

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  const b = marketing(locale).blog;
  return pageMetadata({ locale, path: "/blog", title: b.seoTitle, description: b.seoDescription });
}

export default async function BlogIndex({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const b = marketing(locale).blog;
  const dateFmt = new Intl.DateTimeFormat(locale, { dateStyle: "long" });

  return (
    <>
      <PageHero title={b.title} subtitle={b.subtitle} />
      <section className="mx-auto max-w-3xl px-4 py-16 sm:px-6">
        <div className="space-y-8">
          {b.posts.map((post) => (
            <article key={post.slug} className="border-b border-border pb-8 last:border-0">
              <p className="text-xs text-muted-foreground">
                {dateFmt.format(new Date(post.date))} · {post.author}
              </p>
              <h2 className="mt-2 text-xl font-semibold tracking-tight">
                <Link href={`/blog/${post.slug}`} className="hover:text-primary">
                  {post.title}
                </Link>
              </h2>
              <p className="mt-2 text-muted-foreground">{post.excerpt}</p>
            </article>
          ))}
        </div>
      </section>
    </>
  );
}
