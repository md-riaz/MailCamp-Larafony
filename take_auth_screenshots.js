const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

(async() => {
  const base = 'https://mailcamp.opc.mdriaz.com.bd';
  const outDir = path.join(process.cwd(), 'playwright-auth-shots');
  fs.mkdirSync(outDir, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 2560, height: 1440 } });

  async function save(name) {
    await page.screenshot({ path: path.join(outDir, `${name}.png`), fullPage: true });
    console.log(`saved:${name}`);
  }

  try {
    await page.goto(base + '/login', { waitUntil: 'networkidle', timeout: 30000 });
    await save('01-login');

    const email = page.locator('input[name="email"]');
    const password = page.locator('input[name="password"]');
    await email.fill('admin@example.com');
    await password.fill('password');

    const submit = page.locator('button[type="submit"], input[type="submit"]').first();
    await Promise.all([
      page.waitForLoadState('networkidle'),
      submit.click()
    ]);

    await save('02-after-login');

    const targets = [
      ['03-dashboard', '/dashboard'],
      ['04-campaigns', '/campaigns'],
      ['05-templates', '/templates'],
      ['06-smtp-settings', '/smtp-settings'],
    ];

    for (const [name, route] of targets) {
      try {
        await page.goto(base + route, { waitUntil: 'networkidle', timeout: 30000 });
        await save(name);
      } catch (err) {
        console.error(`failed:${name}:${err.message}`);
      }
    }
  } catch (error) {
    console.error('fatal:' + error.message);
    await page.screenshot({ path: path.join(outDir, 'fatal-state.png'), fullPage: true }).catch(() => {});
  }

  await browser.close();
})();
