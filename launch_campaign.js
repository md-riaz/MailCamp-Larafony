const { chromium } = require('playwright');
(async() => {
  const base = 'https://mailcamp.opc.mdriaz.com.bd';
  const campaignId = 3;
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 2560, height: 1440 } });
  const log = (obj) => console.log(JSON.stringify(obj));

  try {
    await page.goto(base + '/login', { waitUntil: 'networkidle', timeout: 30000 });
    await page.locator('input[name="email"]').fill('admin@example.com');
    await page.locator('input[name="password"]').fill('password');
    await Promise.all([
      page.waitForLoadState('networkidle'),
      page.locator('button[type="submit"], input[type="submit"]').first().click()
    ]);

    await page.goto(base + `/campaigns/${campaignId}`, { waitUntil: 'networkidle', timeout: 30000 });
    log({ step: 'campaign_page', url: page.url(), title: await page.title() });

    const bodyText = (await page.locator('body').innerText()).replace(/\s+/g, ' ');
    log({ step: 'campaign_body', body: bodyText.slice(0, 2000) });

    const launch = page.locator('button', { hasText: 'Launch campaign' }).first();
    if (!(await launch.count())) {
      throw new Error('Launch campaign button not found');
    }

    page.once('dialog', async dialog => await dialog.accept());
    await Promise.all([
      page.waitForLoadState('networkidle'),
      launch.click()
    ]);

    const after = (await page.locator('body').innerText()).replace(/\s+/g, ' ');
    log({ step: 'after_launch', url: page.url(), title: await page.title(), body: after.slice(0, 2500) });
  } catch (error) {
    log({ step: 'error', message: error.message, url: page.url(), title: await page.title().catch(() => null) });
    process.exitCode = 1;
  } finally {
    await browser.close();
  }
})();
