const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

(async() => {
  const base = 'https://mailcamp.opc.mdriaz.com.bd';
  const recipientEmail = 'mdriaz@alpha.net.bd';
  const campaignName = `Playwright Test ${Date.now()}`;
  const subject = `Playwright test ${Date.now()}`;
  const html = `<!DOCTYPE html><html><body><h1>Playwright Test</h1><p>Hello Riaz, this is a test campaign sent at ${new Date().toISOString()}.</p></body></html>`;
  const csvPath = path.join(process.cwd(), 'tmp_recipients.csv');
  fs.writeFileSync(csvPath, `email,name\n${recipientEmail},Riaz\n`);

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
    log({ step: 'login', url: page.url(), title: await page.title() });

    await page.goto(base + '/campaigns/create', { waitUntil: 'networkidle', timeout: 30000 });
    log({ step: 'create_page', url: page.url(), title: await page.title() });

    await page.locator('input[name="name"]').fill(campaignName);
    const smtpSelect = page.locator('select[name="smtp_setting_id"]');
    const smtpOptions = await smtpSelect.locator('option').allTextContents();
    log({ step: 'smtp_options', options: smtpOptions });

    const smtpValue = await smtpSelect.locator('option').nth(1).getAttribute('value');
    if (!smtpValue) throw new Error('No SMTP account available');
    await smtpSelect.selectOption(smtpValue);

    await page.locator('input[name="subject"]').fill(subject);
    await page.locator('textarea[name="html_content"]').evaluate((el, value) => {
      el.value = value;
      el.dispatchEvent(new Event('input', { bubbles: true }));
      el.dispatchEvent(new Event('change', { bubbles: true }));
    }, html);

    const createButton = page.locator('button[type="submit"]').filter({ hasText: 'Create campaign' }).first();
    await Promise.all([
      page.waitForLoadState('networkidle'),
      createButton.click()
    ]);
    log({ step: 'created', url: page.url(), title: await page.title() });

    const fileInput = page.locator('input[type="file"][name="recipients_file"]');
    await fileInput.setInputFiles(csvPath);
    await Promise.all([
      page.waitForLoadState('networkidle'),
      page.locator('form#recipient-import button[type="submit"]').click()
    ]);
    log({ step: 'recipients_imported', url: page.url(), title: await page.title() });

    const launchButton = page.locator('button', { hasText: 'Launch campaign' }).first();
    if (await launchButton.count()) {
      page.once('dialog', async dialog => await dialog.accept());
      await Promise.all([
        page.waitForLoadState('networkidle'),
        launchButton.click()
      ]);
      log({ step: 'launched', url: page.url(), title: await page.title(), body: (await page.locator('body').innerText()).replace(/\s+/g,' ').slice(0, 1200) });
    } else {
      const launchNow = page.locator('button', { hasText: 'Launch now' }).first();
      if (await launchNow.count()) {
        page.once('dialog', async dialog => await dialog.accept());
        await Promise.all([
          page.waitForLoadState('networkidle'),
          launchNow.click()
        ]);
        log({ step: 'launched', url: page.url(), title: await page.title(), body: (await page.locator('body').innerText()).replace(/\s+/g,' ').slice(0, 1200) });
      } else {
        throw new Error('No launch button found after recipient import');
      }
    }

    await page.screenshot({ path: path.join(process.cwd(), 'send_test_campaign_result.png'), fullPage: true });
    log({ step: 'done', screenshot: 'send_test_campaign_result.png' });
  } catch (error) {
    await page.screenshot({ path: path.join(process.cwd(), 'send_test_campaign_error.png'), fullPage: true }).catch(() => {});
    log({ step: 'error', message: error.message, url: page.url(), title: await page.title().catch(() => null) });
    process.exitCode = 1;
  } finally {
    await browser.close();
  }
})();
