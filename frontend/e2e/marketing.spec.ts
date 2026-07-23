import { test, expect } from "@playwright/test";

test.describe("Marketing site", () => {
  test("English homepage renders the hero and CTA", async ({ page }) => {
    await page.goto("/en");
    await expect(
      page.getByRole("heading", { name: /never miss an expiration date/i }),
    ).toBeVisible();
    await expect(page.getByRole("link", { name: /start free/i }).first()).toBeVisible();
  });

  test("Arabic homepage renders RTL", async ({ page }) => {
    await page.goto("/ar");
    const html = page.locator("html");
    await expect(html).toHaveAttribute("dir", "rtl");
    await expect(html).toHaveAttribute("lang", "ar");
  });

  test("pricing page lists plans", async ({ page }) => {
    await page.goto("/en/pricing");
    await expect(
      page.getByRole("heading", { name: /simple pricing/i }),
    ).toBeVisible();
  });

  test("can navigate to register", async ({ page }) => {
    await page.goto("/en");
    await page.getByRole("link", { name: /start free|get started/i }).first().click();
    await expect(page).toHaveURL(/\/en\/register/);
    await expect(
      page.getByRole("heading", { name: /create your remindo account/i }),
    ).toBeVisible();
  });
});
