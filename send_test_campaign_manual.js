const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  const base = 'https://mailcamp.opc.mdriaz.com.bd';
  const stamp = Date.now();
  const inbox = `manual-test-${stamp}@example.com`;
  const name = `Playwright Manual Send ${stamp}`;
  const subject = `Playwright manual send ${stamp}`;
  await page.goto(base + '/login', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="email"]', 'admin@example.com');
  await page.fill('input[name="password"]', 'password');
  await Promise.all([
    page.waitForURL('**/dashboard'),
    page.click('button[type="submit"]')
  ]);
  console.log(JSON.stringify({ step: 'login', url: page.url() }));
  await page.goto(base + '/campaigns/create', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="name"]', name);
  await page.fill('input[name="subject"]', subject);
  await page.selectOption('select[name="smtp_setting_id"]', { index: 1 });
  await page.fill('textarea[name="html_content"]', '<html><body><h1>Hello {{email}}</h1><p><a href="{{unsubscribe_url}}">unsubscribe</a></p></body></html>');
  await Promise.all([
    page.waitForURL(/\/campaigns\/\d+$/),
    page.click('button[type="submit"]')
  ]);
  const url = page.url();
  const campaignId = url.match(/\/campaigns\/(\d+)/)?.[1];
  console.log(JSON.stringify({ step: 'created', url, campaignId, inbox }));
  await page.fill('textarea[name="manual_recipients"]', inbox);
  page.once('response', async (response) => {
    if (response.url().includes(`/campaigns/${campaignId}/recipients`)) {
      console.log(JSON.stringify({ step: 'import_response', status: response.status(), url: response.url(), location: response.headers()['location'] || null }));
    }
  });
  await Promise.all([
    page.waitForURL(new RegExp(`/campaigns/${campaignId}\?notice=(recipient_imported|recipient_import_skipped|recipient_import_empty|invalid_recipient_file|missing_recipient_file)`)),
    page.click('#recipient-import button[type="submit"]')
  ]);
  console.log(JSON.stringify({ step: 'recipients_result', url: page.url(), campaignId }));
  page.once('dialog', async dialog => { await dialog.accept(); });
  const launchButton = page.locator('button:has-text("Launch now"), button:has-text("Launch campaign")').first();
  await Promise.all([
    page.waitForURL(new RegExp(`/campaigns/${campaignId}\?notice=(campaign_launched|campaign_queue_empty|campaign_queue_failed|campaign_template_invalid|campaign_autopaused|campaign_high_risk)`)),
    launchButton.click()
  ]);
  console.log(JSON.stringify({ step: 'launch_result', url: page.url(), campaignId }));
  const body = await page.locator('body').innerText();
  console.log(JSON.stringify({ step: 'body_excerpt', body: body.slice(0, 2000) }));
  await browser.close();
})();
