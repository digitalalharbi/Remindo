export type Section = { title: string; body: string };

export type PageContent = {
  seoTitle: string;
  seoDescription: string;
  heroTitle: string;
  heroSubtitle: string;
  sections: Section[];
};

export type UseCaseContent = PageContent & {
  tracks: string[]; // "what you can track"
};

export type LegalContent = {
  seoTitle: string;
  seoDescription: string;
  title: string;
  updated: string;
  sections: Section[];
};

export type BlogPost = {
  slug: string;
  title: string;
  excerpt: string;
  author: string;
  date: string;
  body: string[];
};

export type MarketingContent = {
  features: PageContent;
  howItWorks: PageContent;
  personal: PageContent;
  business: PageContent;
  integrations: PageContent;
  security: PageContent;
  help: PageContent;
  contact: PageContent & { emailLabel: string; messageLabel: string; nameLabel: string; send: string; sent: string };
  useCases: Record<
    "contracts" | "licenses" | "insurance" | "subscriptions" | "documents" | "maintenance",
    UseCaseContent
  >;
  legal: Record<"terms" | "privacy" | "cookies", LegalContent>;
  blog: {
    seoTitle: string;
    seoDescription: string;
    title: string;
    subtitle: string;
    posts: BlogPost[];
  };
};
