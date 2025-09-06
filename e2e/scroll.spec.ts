// e2e/scroll.spec.ts
import { test, expect } from '@playwright/test';

test('desktop scroll confines to main only', async ({ page }) => {
  await page.goto('http://localhost:8000');
  const main = page.locator('.ims-main');
  await main.evaluate((el)=>{ el.scrollTo({top: 2000, behavior: 'instant'}); });
  const scrollTop = await main.evaluate(el => el.scrollTop);
  expect(scrollTop).toBeGreaterThan(1000);
  const bodyScroll = await page.evaluate(()=>document.scrollingElement?.scrollTop || 0);
  expect(bodyScroll).toBe(0);
});

test('mobile drawer locks body scroll', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('http://localhost:8000');
  await page.locator('[data-drawer-toggle]').click();
  const hasOverlay = await page.locator('.ims-overlay.active').isVisible();
  expect(hasOverlay).toBeTruthy();
  const bodyOverflow = await page.evaluate(()=>document.body.style.overflow);
  expect(bodyOverflow).toContain('hidden');
});
