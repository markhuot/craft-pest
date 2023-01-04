import { test, expect, type Page } from '@playwright/test';

test('loads homepage', async ({ page }) => {
  await page.goto('/');

  await page.isVisible('h6');
});
