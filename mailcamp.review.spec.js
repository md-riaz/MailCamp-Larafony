const { test } = require('@playwright/test');

test('MailCamp review flow', async ({ page }) => {
  const base = 'https://mailcamp.opc.mdriaz.com.bd';

  await page.goto(base + '/login');
  console.log('LOGIN_TITLE=' + await page.title());
  await page.screenshot({ path: '/tmp/mailcamp-login.png', fullPage: true });

  await page.goto(base + '/register');
  console.log('REGISTER_TITLE=' + await page.title());
  await page.screenshot({ path: '/tmp/mailcamp-register.png', fullPage: true });

  const email = `review_${Date.now()}@example.com`;
  if (await page.locator('[name="name"]').count()) await page.locator('[name="name"]').fill('Playwright Reviewer');
  if (await page.locator('[name="email"]').count()) await page.locator('[name="email"]').fill(email);
  if (await page.locator('[name="organization_name"]').count()) await page.locator('[name="organization_name"]').fill('Playwright Org');
  if (await page.locator('[name="password"]').count()) await page.locator('[name="password"]').fill('password');
  if (await page.locator('[name="password_confirmation"]').count()) await page.locator('[name="password_confirmation"]').fill('password');

  const button = page.locator('form button[type="submit"], form input[type="submit"], form button').first();
  await button.click();
  await page.waitForLoadState('networkidle');
  console.log('POST_REGISTER_URL=' + page.url());
  console.log('POST_REGISTER_TITLE=' + await page.title());
  console.log('POST_REGISTER_BODY=' + JSON.stringify(((await page.locator('body').innerText()).replace(/\s+/g,' ').trim()).slice(0,1200)));
  await page.screenshot({ path: '/tmp/mailcamp-after-register.png', fullPage: true });

  for (const path of ['/dashboard', '/campaigns', '/templates', '/smtp-settings']) {
    await page.goto(base + path);
    await page.waitForLoadState('networkidle');
    console.log('PAGE=' + path + ' URL=' + page.url() + ' TITLE=' + await page.title());
    const h1 = await page.locator('h1').allTextContents().catch(() => []);
    console.log('H1_' + path.replace(/\W/g,'_') + '=' + JSON.stringify(h1));
    console.log('BODY_' + path.replace(/\W/g,'_') + '=' + JSON.stringify(((await page.locator('body').innerText()).replace(/\s+/g,' ').trim()).slice(0,1200)));
  }
});
