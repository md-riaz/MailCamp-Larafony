const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

(async() => {
  const base = 'https://mailcamp.opc.mdriaz.com.bd';
  const outDir = path.join(process.cwd(), 'playwright-shots');
  fs.mkdirSync(outDir, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 2560, height: 1440 } });

  const targets = [
    { name: 'home', url: base + '/' },
    { name: 'login', url: base + '/login' },
    { name: 'register', url: base + '/register' },
  ];

  for (const target of targets) {
    try {
      await page.goto(target.url, { waitUntil: 'networkidle', timeout: 30000 });
      await page.screenshot({ path: path.join(outDir, `${target.name}.png`), fullPage: true });
      console.log(`saved:${target.name}`);
    } catch (error) {
      console.error(`failed:${target.name}:${error.message}`);
    }
  }

  await browser.close();
})();
