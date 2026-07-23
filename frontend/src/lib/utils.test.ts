import { describe, it, expect } from "vitest";
import { cn, formatPrice } from "./utils";

describe("cn", () => {
  it("merges and dedupes tailwind classes", () => {
    expect(cn("px-2", "px-4")).toBe("px-4");
    expect(cn("text-sm", false && "hidden", "font-medium")).toBe(
      "text-sm font-medium",
    );
  });
});

describe("formatPrice", () => {
  it("formats minor units to a whole-number currency string", () => {
    // 900 minor SAR = 9 SAR; no fraction digits when evenly divisible.
    const out = formatPrice(900, "SAR", "en");
    expect(out).toMatch(/9/);
    expect(out).not.toMatch(/\.00/);
  });

  it("keeps fraction digits when not a whole number", () => {
    const out = formatPrice(1950, "USD", "en");
    expect(out).toMatch(/19\.50/);
  });
});
