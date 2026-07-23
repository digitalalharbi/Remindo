export type LocalizedText = Record<string, string> | string;

export interface Plan {
  id: string;
  key: "free" | "personal" | "professional" | "business";
  name: LocalizedText;
  description?: LocalizedText;
  price_monthly: number;
  price_yearly: number;
  currency: string;
  reminder_limit: number;
  user_limit: number;
  ai_operations_limit: number;
  features: string[];
}

export interface Organization {
  id: string;
  name: string;
  slug: string;
  type: "personal" | "team";
  currency: string;
  role?: string;
  plan?: Plan;
}

export interface User {
  id: string;
  name: string;
  email: string;
  phone?: string;
  locale: string;
  country?: string;
  timezone: string;
  email_verified: boolean;
  two_factor_enabled: boolean;
  is_super_admin: boolean;
  current_organization_id: string;
  organizations?: Organization[];
}

export interface Category {
  id: string;
  name: LocalizedText;
  slug: string;
  color: string;
  icon?: string;
  is_system: boolean;
}

export interface Tag {
  id: string;
  name: string;
}

export type ReminderStatus = "active" | "completed" | "renewed" | "archived";
export type Channel = "email" | "in_app" | "web_push" | "sms" | "whatsapp" | "webhook";

export interface ReminderNotification {
  id: string;
  offset_days: number;
  channel: Channel;
  send_at: string;
  status: "pending" | "sent" | "failed" | "cancelled";
}

export interface Reminder {
  id: string;
  title: string;
  description?: string;
  reference_number?: string;
  issuer?: string;
  expiry_date: string;
  issue_date?: string;
  status: ReminderStatus;
  days_until_expiry: number;
  recurrence: "none" | "monthly" | "yearly" | "custom";
  category?: Category;
  tags?: Tag[];
  notifications?: ReminderNotification[];
  created_at: string;
}

export interface DashboardData {
  counts: {
    active: number;
    overdue: number;
    this_week: number;
    this_month: number;
  };
  overdue: Reminder[];
  this_week: Reminder[];
  upcoming: Reminder[];
  recent_activity: {
    id: string;
    action: string;
    subject_id?: string;
    created_at: string;
  }[];
}

export interface CreateReminderInput {
  title: string;
  expiry_date: string;
  description?: string;
  category_id?: string;
  reference_number?: string;
  issuer?: string;
  issue_date?: string;
  recurrence?: "none" | "monthly" | "yearly" | "custom";
  reminder_offsets?: number[];
  channels?: Channel[];
  remind_at_time?: string;
}
