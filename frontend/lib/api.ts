export const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api";
const API_ORIGIN = API_URL.replace(/\/api\/?$/, "");

function cookie(name: string): string {
  if (typeof document === "undefined") return "";
  const value = document.cookie.split("; ").find((item) => item.startsWith(`${name}=`));
  return value ? decodeURIComponent(value.split("=").slice(1).join("=")) : "";
}

export async function csrf(): Promise<void> {
  await fetch(`${API_ORIGIN}/sanctum/csrf-cookie`, {
    credentials: "include",
    headers: { Accept: "application/json" },
  });
}

export async function api<T>(path: string, options: RequestInit = {}): Promise<T> {
  const method = (options.method ?? "GET").toUpperCase();
  if (!["GET", "HEAD", "OPTIONS"].includes(method) && !cookie("XSRF-TOKEN")) {
    await csrf();
  }

  const response = await fetch(`${API_URL}/v1${path}`, {
    ...options,
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(cookie("XSRF-TOKEN") ? { "X-XSRF-TOKEN": cookie("XSRF-TOKEN") } : {}),
      ...options.headers,
    },
  });

  const payload = await response.json().catch(() => ({}));
  if (!response.ok) {
    const errors = payload.errors ? Object.values(payload.errors).flat().join(" ") : payload.message;
    throw new Error(errors || "Request failed.");
  }
  return payload as T;
}
