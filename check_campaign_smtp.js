const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

(async() => {
  const base = 'https://mailcamp.opc.mdriaz.com.bd';
  const outDir = path.join(process.cwd(), 'playwright-check');
  fs.mkdirSync(outDir, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 2560, height: 1440 } });

  await page.goto(base + '/login', { waitUntil: 'networkidle', timeout: 30000 });
  await page.locator('input[name="email"]').fill('admin@example.com');
  await page.locator('input[name="password"]').fill('password');

  await Promise.all([
    page.waitForLoadState('networkidle'),
    page.locator('button[type="submit"], input[type="submit"]').first().click()
  ]);

  for (const route of ['/campaigns', '/smtp-settings']) {
    await page.goto(base + route, { waitUntil: 'networkidle', timeout: 30000 });
    const safe = route.replace(/[^a-z0-9]+/gi, '_').replace(/^_+|_+$/g, '');
    await page.screenshot({ path: path.join(outDir, `${safe}.png`), fullPage: true });
    console.log(JSON.stringify({
      route,
      url: page.url(),
      title: await page.title(),
      h1: await page.locator('h1').allTextContents().catch(() => []),
      body: ((await page.locator('body').innerText()).replace(/\s+/g, ' ').trim()).slice(0, 1500)
    }));
  }

  await browser.close();
})();
