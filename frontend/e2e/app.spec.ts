import { test, expect, Page } from "@playwright/test";

// Unique email per run so re-runs don't collide (no Date.now import restriction here).
function uniqueEmail() {
  return `e2e_${Math.random().toString(36).slice(2, 10)}@example.com`;
}

async function register(page: Page, email: string) {
  await page.goto("/en/register");
  await page.getByLabel("Full name").fill("E2E User");
  await page.getByLabel("Email").fill(email);
  await page.getByLabel("Password", { exact: true }).fill("password123");
  await page.getByLabel("Confirm password").fill("password123");
  await page.getByRole("button", { name: /create account/i }).click();
  await expect(page).toHaveURL(/\/en\/dashboard/, { timeout: 15000 });
}

test.describe("Authenticated app", () => {
  test("register → dashboard → create a reminder", async ({ page }) => {
    const email = uniqueEmail();
    await register(page, email);

    // Dashboard greets the user.
    await expect(page.getByRole("heading", { name: /hi e2e/i })).toBeVisible();

    // Open the create dialog from the top bar and create a reminder.
    await page.getByRole("button", { name: /add reminder/i }).first().click();
    await page.getByPlaceholder(/car insurance/i).fill("E2E Passport");
    await page.locator('input[type="date"]').first().fill("2027-01-15");
    await page.getByRole("button", { name: /^save$/i }).click();

    // It appears in the reminders list.
    await page.goto("/en/reminders");
    await expect(page.getByText("E2E Passport")).toBeVisible({ timeout: 10000 });
  });

  test("unauthenticated dashboard access redirects to login", async ({ page }) => {
    await page.context().clearCookies();
    await page.goto("/en/dashboard");
    await expect(page).toHaveURL(/\/en\/login/, { timeout: 10000 });
  });

  test("billing page shows plans and current plan", async ({ page }) => {
    await register(page, uniqueEmail());
    await page.goto("/en/billing");
    await expect(page.getByText(/current plan/i)).toBeVisible();
    await expect(page.getByText(/free/i).first()).toBeVisible();
  });
});
