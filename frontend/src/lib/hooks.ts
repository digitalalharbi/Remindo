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
    onSuccess: (user) => qc.setQueryData(["me"], user),
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
