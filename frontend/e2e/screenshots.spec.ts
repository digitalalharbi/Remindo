import { test, Page } from "@playwright/test";
import fs from "node:fs";

const DIR = "e2e/__screenshots__";
fs.mkdirSync(DIR, { recursive: true });

async function register(page: Page) {
  const email = `shot_${Math.random().toString(36).slice(2, 10)}@example.com`;
  await page.goto("/en/register");
  await page.getByLabel("Full name").fill("Remindo Demo");
  await page.getByLabel("Email").fill(email);
  await page.getByLabel("Password", { exact: true }).fill("password123");
  await page.getByLabel("Confirm password").fill("password123");
  await page.getByRole("button", { name: /create account/i }).click();
  await page.waitForURL(/\/en\/dashboard/, { timeout: 15000 });
}

test("capture marketing", async ({ page }) => {
  await page.goto("/en");
  await page.waitForTimeout(600);
  await page.screenshot({ path: `${DIR}/1-marketing.png`, fullPage: false });
});

test("capture admin", async ({ page }) => {
  await page.goto("/en/login");
  await page.getByLabel("Email").fill("admin@remindo.me");
  await page.getByLabel("Password", { exact: true }).fill("password123");
  await page.getByRole("button", { name: /sign in/i }).click();
  await page.waitForURL(/\/en\/dashboard/, { timeout: 15000 });
  await page.goto("/en/admin");
  await page.waitForTimeout(800);
  await page.screenshot({ path: `${DIR}/5-admin.png` });
});

test("capture dashboard + reminder creation + billing", async ({ page }) => {
  await register(page);
  await page.waitForTimeout(400);
  await page.screenshot({ path: `${DIR}/2-dashboard.png` });

  await page.getByRole("button", { name: /add reminder/i }).first().click();
  await page.getByPlaceholder(/car insurance/i).fill("Car insurance");
  await page.locator('input[type="date"]').first().fill("2027-03-01");
  await page.waitForTimeout(300);
  await page.screenshot({ path: `${DIR}/3-create-reminder.png` });
  await page.getByRole("button", { name: /^save$/i }).click();

  await page.goto("/en/billing");
  await page.waitForTimeout(600);
  await page.screenshot({ path: `${DIR}/4-billing.png` });
});
