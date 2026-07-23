import { create } from "zustand";
import type { User } from "@/lib/types";

/**
 * Small client-side auth state. The source of truth is the httpOnly session
 * cookie on the API; this store only mirrors the current user for the UI.
 */
interface AuthState {
  user: User | null;
  status: "idle" | "loading" | "authenticated" | "unauthenticated";
  setUser: (user: User | null) => void;
  setStatus: (status: AuthState["status"]) => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  status: "idle",
  setUser: (user) =>
    set({ user, status: user ? "authenticated" : "unauthenticated" }),
  setStatus: (status) => set({ status }),
}));
