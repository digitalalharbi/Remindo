import { create } from "zustand";

/** Small UI state shared across the dashboard (e.g. the global create dialog). */
interface UiState {
  createReminderOpen: boolean;
  openCreateReminder: () => void;
  closeCreateReminder: () => void;
}

export const useUiStore = create<UiState>((set) => ({
  createReminderOpen: false,
  openCreateReminder: () => set({ createReminderOpen: true }),
  closeCreateReminder: () => set({ createReminderOpen: false }),
}));
