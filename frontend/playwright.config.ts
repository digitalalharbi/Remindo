import { defineConfig, devices } from "@playwright/test";

/**
 * E2E config. Uses the pre-installed Chromium in this environment.
 * Starts the production build and exercises the marketing + auth flows.
 */
export default defineConfig({
  testDir: "./e2e",
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? "list" : "html",
  use: {
    baseURL: "http://localhost:3000",
    trace: "on-first-retry",
  },
  projects: [
    {
      name: "chromium",
      use: {
        ...devices["Desktop Chrome"],
        launchOptions: {
          executablePath:
            process.env.PLAYWRIGHT_CHROMIUM_PATH || undefined,
        },
      },
    },
  ],
  webServer: {
    command: "npm run start",
    url: "http://localhost:3000/en",
    reuseExistingServer: !process.env.CI,
    timeout: 120_000,
  },
});
