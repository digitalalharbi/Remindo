export const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api";

type ApiOptions = RequestInit & { token?: string };

export async function api<T>(path: string, options: ApiOptions = {}): Promise<T> {
  const { token, ...init } = options;
  const response = await fetch(`${API_URL}/v1${path}`, {
    ...init,
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...init.headers,
    },
  });

  const payload = await response.json().catch(() => ({}));
  if (!response.ok) {
    const errors = payload.errors ? Object.values(payload.errors).flat().join(" ") : payload.message;
    throw new Error(errors || "Request failed.");
  }
  return payload as T;
}
