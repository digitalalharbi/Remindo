import axios, { type AxiosInstance } from "axios";

/**
 * Axios client for the Remindo API. Uses cookie-based (Sanctum) auth, so
 * `withCredentials` is on and tokens are NEVER stored in localStorage.
 * Before any mutating request the app first hits /sanctum/csrf-cookie.
 */
const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api";

const API_ROOT = API_URL.replace(/\/api\/?$/, "");

export const api: AxiosInstance = axios.create({
  baseURL: API_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
  },
});

let csrfReady = false;

/** Fetch the CSRF cookie once before the first mutating request. */
export async function ensureCsrf() {
  if (csrfReady) return;
  await axios.get(`${API_ROOT}/sanctum/csrf-cookie`, {
    withCredentials: true,
  });
  csrfReady = true;
}

/** Attach the active locale so the API returns localized messages. */
export function setApiLocale(locale: string) {
  api.defaults.headers.common["Accept-Language"] = locale;
}

export type ApiEnvelope<T> = {
  data: T;
  message?: string;
  meta?: Record<string, unknown>;
};
