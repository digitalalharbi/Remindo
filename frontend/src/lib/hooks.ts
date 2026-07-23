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
