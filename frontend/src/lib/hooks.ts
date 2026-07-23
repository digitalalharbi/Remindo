import {
  useMutation,
  useQuery,
  useQueryClient,
} from "@tanstack/react-query";
import { api, ensureCsrf, type ApiEnvelope } from "./api";
import type {
  Category,
  CreateReminderInput,
  DashboardData,
  Plan,
  Reminder,
  User,
} from "./types";

/* ── Auth ─────────────────────────────────────────────── */

export function useMe(enabled = true) {
  return useQuery({
    queryKey: ["me"],
    enabled,
    retry: false,
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<User>>("/auth/me");
      return data.data;
    },
  });
}

export function useLogin() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: { email: string; password: string; remember?: boolean }) => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<User>>("/auth/login", input);
      return data.data;
    },
    // A 2FA-enabled account returns { two_factor: true } (no id) — don't treat it as signed in.
    onSuccess: (user) => {
      if ((user as unknown as { two_factor?: boolean })?.two_factor) return;
      qc.setQueryData(["me"], user);
    },
  });
}

export function useRegister() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: {
      name: string;
      email: string;
      password: string;
      password_confirmation: string;
      locale?: string;
      country?: string;
      timezone?: string;
    }) => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<User>>("/auth/register", input);
      return data.data;
    },
    onSuccess: (user) => qc.setQueryData(["me"], user),
  });
}

export function useLogout() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async () => {
      await ensureCsrf();
      await api.post("/auth/logout");
    },
    onSuccess: () => qc.setQueryData(["me"], null),
  });
}

export function useTwoFactorChallenge() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: { code?: string; recovery_code?: string }) => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<User>>("/auth/two-factor-challenge", input);
      return data.data;
    },
    onSuccess: (user) => qc.setQueryData(["me"], user),
  });
}

export function useOAuthStatus() {
  return useQuery({
    queryKey: ["oauth-status"],
    retry: false,
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<{ google: boolean; microsoft: boolean }>>(
        "/auth/oauth/status",
      );
      return data.data;
    },
  });
}

/* ── Two-factor + sessions (account security) ─────────── */

export function useEnableTwoFactor() {
  return useMutation({
    mutationFn: async () => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<{ secret: string; otpauth_url: string; qr_svg: string }>>(
        "/auth/two-factor/enable",
      );
      return data.data;
    },
  });
}

export function useConfirmTwoFactor() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (code: string) => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<{ recovery_codes: string[] }>>(
        "/auth/two-factor/confirm",
        { code },
      );
      return data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ["me"] }),
  });
}

export function useDisableTwoFactor() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async () => {
      await ensureCsrf();
      await api.delete("/auth/two-factor");
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ["me"] }),
  });
}

export interface SessionInfo {
  id: string;
  ip_address?: string;
  user_agent: string;
  last_active: number;
  current: boolean;
}

export function useSessions() {
  return useQuery({
    queryKey: ["sessions"],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<SessionInfo[]>>("/auth/sessions");
      return data.data;
    },
  });
}

export function useRevokeSessions() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id?: string) => {
      await ensureCsrf();
      await api.delete(id ? `/auth/sessions/${id}` : "/auth/sessions/others");
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ["sessions"] }),
  });
}

/* ── Plans (public) ───────────────────────────────────── */

export function usePlans() {
  return useQuery({
    queryKey: ["plans"],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<Plan[]>>("/plans");
      return data.data;
    },
  });
}

/* ── Dashboard ────────────────────────────────────────── */

export function useDashboard() {
  return useQuery({
    queryKey: ["dashboard"],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<DashboardData>>("/dashboard");
      return data.data;
    },
  });
}

/* ── Reminders ────────────────────────────────────────── */

export function useReminders(params: Record<string, string | number> = {}) {
  return useQuery({
    queryKey: ["reminders", params],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<Reminder[]>>("/reminders", {
        params,
      });
      return data;
    },
  });
}

export function useCreateReminder() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: CreateReminderInput) => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<Reminder>>("/reminders", input);
      return data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["reminders"] });
      qc.invalidateQueries({ queryKey: ["dashboard"] });
    },
  });
}

function useReminderAction(path: (id: string) => string) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async ({ id, body }: { id: string; body?: unknown }) => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<Reminder>>(path(id), body ?? {});
      return data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["reminders"] });
      qc.invalidateQueries({ queryKey: ["dashboard"] });
    },
  });
}

export const useCompleteReminder = () =>
  useReminderAction((id) => `/reminders/${id}/complete`);
export const useRenewReminder = () =>
  useReminderAction((id) => `/reminders/${id}/renew`);
export const useArchiveReminder = () =>
  useReminderAction((id) => `/reminders/${id}/archive`);

export function useDeleteReminder() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: string) => {
      await ensureCsrf();
      await api.delete(`/reminders/${id}`);
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["reminders"] });
      qc.invalidateQueries({ queryKey: ["dashboard"] });
    },
  });
}

/* ── Documents & AI (extraction is reviewed before save) ── */

export interface ExtractionResult {
  title?: string;
  reference_number?: string;
  issuer?: string;
  issue_date?: string;
  expiry_date?: string;
  summary?: string;
  suggested_offsets: number[];
  confidence: number;
  provider: string;
  requires_review: boolean;
}

export function useUploadDocument() {
  return useMutation({
    mutationFn: async (file: File) => {
      await ensureCsrf();
      const form = new FormData();
      form.append("file", file);
      const { data } = await api.post<ApiEnvelope<{ id: string; original_name: string }>>(
        "/documents",
        form,
        { headers: { "Content-Type": "multipart/form-data" } },
      );
      return data.data;
    },
  });
}

export function useExtractDocument() {
  return useMutation({
    mutationFn: async (documentId: string) => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<ExtractionResult>>(
        `/documents/${documentId}/extract`,
      );
      return data.data;
    },
  });
}

export function useParseInstruction() {
  return useMutation({
    mutationFn: async (text: string) => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<ExtractionResult>>("/ai/parse", {
        text,
      });
      return data.data;
    },
  });
}

/* ── Subscription / billing ───────────────────────────── */

export interface SubscriptionData {
  plan: Plan;
  currency: string;
  subscription: {
    interval: "monthly" | "yearly";
    status: string;
    current_period_end: string;
  } | null;
  invoices: {
    id: string;
    number: string;
    status: string;
    total: number;
    currency: string;
    issued_at: string;
  }[];
}

export function useSubscription() {
  return useQuery({
    queryKey: ["subscription"],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<SubscriptionData>>("/subscription");
      return data.data;
    },
  });
}

export function useSubscribe() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: { plan_key: string; interval: "monthly" | "yearly" }) => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<Plan>>("/subscription", input);
      return data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["subscription"] });
      qc.invalidateQueries({ queryKey: ["me"] });
    },
  });
}

/* ── Profile / team / notifications / documents ───────── */

export function useUpdateProfile() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: Partial<Pick<User, "name" | "phone" | "locale" | "country" | "timezone">>) => {
      await ensureCsrf();
      const { data } = await api.patch<ApiEnvelope<User>>("/profile", input);
      return data.data;
    },
    onSuccess: (user) => {
      qc.setQueryData(["me"], user);
    },
  });
}

export interface TeamMember {
  id: string;
  name: string;
  email: string;
  role: string;
}

export function useTeam() {
  return useQuery({
    queryKey: ["team"],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<TeamMember[]>>("/team");
      return data;
    },
  });
}

export interface AppNotification {
  id: string;
  data: { title?: string; reminder_id?: string; expiry_date?: string; days_until_expiry?: number };
  read: boolean;
  created_at: string;
}

export function useNotifications() {
  return useQuery({
    queryKey: ["notifications"],
    refetchInterval: 60_000,
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<AppNotification[]>>("/notifications");
      return data;
    },
  });
}

export function useMarkAllRead() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async () => {
      await ensureCsrf();
      await api.post("/notifications/read-all");
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ["notifications"] }),
  });
}

export interface DocumentItem {
  id: string;
  original_name: string;
  mime: string;
  size: number;
  extraction_status: string;
  created_at: string;
}

export function useDocuments() {
  return useQuery({
    queryKey: ["documents"],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<DocumentItem[]>>("/documents");
      return data.data;
    },
  });
}

/* ── Super Admin ──────────────────────────────────────── */

export function useAdminStats() {
  return useQuery({
    queryKey: ["admin", "stats"],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<Record<string, number | string>>>("/admin/stats");
      return data.data;
    },
  });
}

export function useAdminList<T = Record<string, unknown>>(resource: string) {
  return useQuery({
    queryKey: ["admin", resource],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<T[]>>(`/admin/${resource}`);
      return data;
    },
  });
}

type AdminWrite = {
  method: "post" | "patch" | "delete";
  path: string;
  body?: unknown;
};

/** Generic admin write with automatic invalidation of the affected resource lists. */
export function useAdminAction(invalidate: string[] = []) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async ({ method, path, body }: AdminWrite) => {
      await ensureCsrf();
      const { data } = await api.request<ApiEnvelope<unknown>>({
        method,
        url: `/admin/${path}`,
        data: body,
      });
      return data.data;
    },
    onSuccess: () => {
      invalidate.forEach((key) => qc.invalidateQueries({ queryKey: ["admin", key] }));
      qc.invalidateQueries({ queryKey: ["admin", "stats"] });
    },
  });
}

/* ── Notification channels (preferences, credits, webhooks) ── */

export interface ChannelPreferences {
  channels: string[] | null;
  quiet_hours_enabled: boolean;
  quiet_start: number | null;
  quiet_end: number | null;
}

export function useChannelPreferences() {
  return useQuery({
    queryKey: ["channel-preferences"],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<ChannelPreferences>>("/channels/preferences");
      return data.data;
    },
  });
}

export function useUpdateChannelPreferences() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: Partial<ChannelPreferences>) => {
      await ensureCsrf();
      const { data } = await api.patch<ApiEnvelope<ChannelPreferences>>("/channels/preferences", input);
      return data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ["channel-preferences"] }),
  });
}

export interface CreditsData {
  balances: { sms: number; whatsapp: number; ai: number };
  packs: { id: string; channel: string; name: string; credits: number; price: number; currency: string }[];
}

export function useCredits() {
  return useQuery({
    queryKey: ["credits"],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<CreditsData>>("/channels/credits");
      return data.data;
    },
  });
}

export function useBuyCredits() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (packId: string) => {
      await ensureCsrf();
      const { data } = await api.post<ApiEnvelope<{ channel: string; balance: number }>>(
        "/channels/credits/buy",
        { pack_id: packId },
      );
      return data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ["credits"] }),
  });
}

/* ── Categories ───────────────────────────────────────── */

export function useCategories() {
  return useQuery({
    queryKey: ["categories"],
    queryFn: async () => {
      const { data } = await api.get<ApiEnvelope<Category[]>>("/categories");
      return data.data;
    },
  });
}
