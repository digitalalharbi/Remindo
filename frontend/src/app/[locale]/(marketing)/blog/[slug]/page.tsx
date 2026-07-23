import { notFound } from "next/navigation";
import { setRequestLocale } from "next-intl/server";
import { marketing } from "@/content/marketing";
import { pageMetadata, jsonLd } from "@/lib/seo";
import { Link } from "@/i18n/navigation";
import { ArrowLeft } from "lucide-react";

export function generateStaticParams() {
  // Slugs are shared across locales; generate for the English set.
  return marketing("en").blog.posts.map((p) => ({ slug: p.slug }));
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  const post = marketing(locale).blog.posts.find((p) => p.slug === slug);
  if (!post) return {};
  return pageMetadata({
    locale,
    path: `/blog/${slug}`,
    title: `${post.title} — Remindo`,
    description: post.excerpt,
  });
}

export default async function BlogPost({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  setRequestLocale(locale);
  const post = marketing(locale).blog.posts.find((p) => p.slug === slug);
  if (!post) notFound();

  const dateFmt = new Intl.DateTimeFormat(locale, { dateStyle: "long" });

  const schema = {
    "@context": "https://schema.org",
    "@type": "Article",
    headline: post.title,
    datePublished: post.date,
    author: { "@type": "Organization", name: post.author },
    inLanguage: locale,
  };

  return (
    <article className="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-20">
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: jsonLd(schema) }} />
      <Link href="/blog" className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground">
        <ArrowLeft className="size-4 rtl:rotate-180" />
        Blog
      </Link>
      <p className="mt-6 text-xs text-muted-foreground">
        {dateFmt.format(new Date(post.date))} · {post.author}
      </p>
      <h1 className="mt-2 text-3xl font-semibold tracking-tight">{post.title}</h1>
      <div className="mt-8 space-y-4 leading-relaxed text-muted-foreground">
        {post.body.map((p, i) => (
          <p key={i}>{p}</p>
        ))}
      </div>
    </article>
  );
}

export const dynamicParams = false;
