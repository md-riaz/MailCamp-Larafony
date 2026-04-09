const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
(async() => {
  const base = 'https://mailcamp.opc.mdriaz.com.bd';
  const recipientEmail = 'mdriaz@alpha.net.bd';
  const stamp = Date.now();
  const campaignName = `Playwright Live Send ${stamp}`;
  const subject = `Playwright live send ${stamp}`;
  const html = `<!DOCTYPE html><html><body><h1>Playwright Test</h1><p>Hello {{name}}.</p><p>This is a Playwright live test campaign sent at ${new Date().toISOString()}.</p><p><a href="{{unsubscribe_url}}">Unsubscribe</a></p></body></html>`;
  const csvPath = path.join(process.cwd(), 'tmp_recipients.csv');
  fs.writeFileSync(csvPath, `email,name\n${recipientEmail},Riaz\n`);
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1600, height: 1200 } });
  const log = (obj) => console.log(JSON.stringify(obj));
  try {
    await page.goto(base + '/login', { waitUntil: 'networkidle', timeout: 30000 });
    await page.locator('input[name="email"]').fill('admin@example.com');
    await page.locator('input[name="password"]').fill('password');
    await Promise.all([
      page.waitForURL(/dashboard|campaigns|smtp|templates/, { timeout: 30000 }),
      page.locator('button[type="submit"], input[type="submit"]').first().click()
    ]);
    log({ step: 'login', url: page.url(), title: await page.title() });
    await page.goto(base + '/campaigns/create', { waitUntil: 'networkidle', timeout: 30000 });
    log({ step: 'create_page', url: page.url(), title: await page.title() });
    await page.locator('#name').fill(campaignName);
    const smtpSelect = page.locator('#smtp_setting_id');
    const smtpValue = await smtpSelect.locator('option').nth(1).getAttribute('value');
    if (!smtpValue) throw new Error('No SMTP account available');
    await smtpSelect.selectOption(smtpValue);
    await page.locator('#subject').fill(subject);
    await page.locator('#html_content').evaluate((el, value) => {
      el.value = value;
      el.dispatchEvent(new Event('input', { bubbles: true }));
      el.dispatchEvent(new Event('change', { bubbles: true }));
    }, html);
    const saveTemplate = page.locator('#save_as_template');
    if (await saveTemplate.count()) await saveTemplate.uncheck().catch(() => {});
    await Promise.all([
      page.waitForURL(/\/campaigns\/\d+/, { timeout: 30000 }),
      page.locator('button[type="submit"]').filter({ hasText: 'Create campaign' }).click()
    ]);
    const createdUrl = page.url();
    const campaignIdMatch = createdUrl.match(/\/campaigns\/(\d+)/);
    if (!campaignIdMatch) throw new Error('Campaign id not found in URL: ' + createdUrl);
    const campaignId = campaignIdMatch[1];
    log({ step: 'created', url: createdUrl, campaignId, title: await page.title() });
    await page.locator('input[type="file"][name="recipients_file"]').setInputFiles(csvPath);
    await Promise.all([
      page.waitForLoadState('networkidle'),
      page.locator('form#recipient-import button[type="submit"]').click()
    ]);
    const afterImportText = (await page.locator('body').innerText()).replace(/\s+/g, ' ');
    log({ step: 'recipients_imported', url: page.url(), campaignId, title: await page.title(), body: afterImportText.slice(0, 1000) });
    const launch = page.locator('button').filter({ hasText: /^Launch campaign$|^Launch now$/ }).first();
    if (!(await launch.count())) throw new Error('Launch button not found');
    page.once('dialog', async d => await d.accept());
    await Promise.all([
      page.waitForLoadState('networkidle'),
      launch.click()
    ]);
    const launchedText = (await page.locator('body').innerText()).replace(/\s+/g, ' ');
    log({ step: 'launched', url: page.url(), campaignId, title: await page.title(), body: launchedText.slice(0, 1200) });
    await page.screenshot({ path: path.join(process.cwd(), 'send_test_campaign_result.png'), fullPage: true });
    log({ step: 'done', campaignId, screenshot: 'send_test_campaign_result.png' });
  } catch (error) {
    await page.screenshot({ path: path.join(process.cwd(), 'send_test_campaign_error.png'), fullPage: true }).catch(() => {});
    log({ step: 'error', message: error.message, url: page.url(), title: await page.title().catch(() => null) });
    process.exitCode = 1;
  } finally {
    await browser.close();
  }
})();
