import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";
import test from "node:test";

async function render() {
  const workerUrl = new URL("../dist/server/index.js", import.meta.url);
  workerUrl.searchParams.set("test", `${process.pid}-${Date.now()}`);
  const { default: worker } = await import(workerUrl.href);

  return worker.fetch(
    new Request("http://localhost/", { headers: { accept: "text/html" } }),
    { ASSETS: { fetch: async () => new Response("Not found", { status: 404 }) } },
    { waitUntil() {}, passThroughOnException() {} },
  );
}

test("server-renders the Remindo marketing experience", async () => {
  const response = await render();
  assert.equal(response.status, 200);
  assert.match(response.headers.get("content-type") ?? "", /^text\/html\b/i);

  const html = await response.text();
  assert.match(html, /<title>Expiration Reminder Software \| Remindo<\/title>/i);
  assert.match(html, /لا تفوّت موعد انتهاء مهم/);
  assert.match(html, /Remindo/);
  assert.match(html, /hrefLang="ar"/);
  assert.match(html, /hrefLang="en"/);
  assert.match(html, /dir="rtl"/);
  assert.doesNotMatch(html, /codex-preview|Your site is taking shape/);
});

test("ships an interactive, accessible reminder flow", async () => {
  const [page, layout, css, packageJson] = await Promise.all([
    readFile(new URL("../app/page.tsx", import.meta.url), "utf8"),
    readFile(new URL("../app/layout.tsx", import.meta.url), "utf8"),
    readFile(new URL("../app/globals.css", import.meta.url), "utf8"),
    readFile(new URL("../package.json", import.meta.url), "utf8"),
  ]);

  assert.match(page, /saveReminder/);
  assert.match(page, /type="date"/);
  assert.match(page, /role="status"/);
  assert.match(page, /aria-label="Toggle color theme"/);
  assert.match(page, /setLanguage/);
  assert.match(layout, /metadataBase: new URL\("https:\/\/remindo\.me"\)/);
  assert.match(css, /prefers-reduced-motion:\s*reduce/);
  assert.match(css, /@media \(max-width: 620px\)/);
  assert.doesNotMatch(packageJson, /react-loading-skeleton/);
});

test("authenticated app uses the real API without persistent token storage", async () => {
  const [appPage, apiClient] = await Promise.all([
    readFile(new URL("../app/app/page.tsx", import.meta.url), "utf8"),
    readFile(new URL("../lib/api.ts", import.meta.url), "utf8"),
  ]);

  assert.match(appPage, /api<AuthPayload>/);
  assert.match(appPage, /await loadData/);
  assert.match(appPage, /\/dashboard/);
  assert.match(appPage, /schedules:/);
  assert.match(apiClient, /NEXT_PUBLIC_API_URL/);
  assert.match(apiClient, /credentials: "include"/);
  assert.doesNotMatch(appPage + apiClient, /localStorage|sessionStorage/);
  assert.doesNotMatch(appPage, /const reminders = \[/);
});
