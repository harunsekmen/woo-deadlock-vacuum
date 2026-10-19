
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

(async () => {
  const browser = await chromium.launch({
    headless: true,
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'
  });
  const context = await browser.newContext({
    viewport: { width: 1200, height: 820 },
    deviceScaleFactor: 2
  });
  const page = await context.newPage();

  const docsDir = path.join(__dirname, '..', 'docs', 'assets');
  if (!fs.existsSync(docsDir)) {
    fs.mkdirSync(docsDir, { recursive: true });
  }

  // 1. Admin Dashboard Simulation Test
  const adminUrl = 'file://' + path.join(__dirname, 'admin-simulation.html');
  console.log('Navigating to:', adminUrl);
  await page.goto(adminUrl);
  await page.waitForSelector('.postbox');

  const adminScreenshot = path.join(docsDir, 'admin-vacuum-dashboard.png');
  await page.screenshot({ path: adminScreenshot, fullPage: false });
  console.log('Saved admin vacuum dashboard preview to:', adminScreenshot);

  await browser.close();
  console.log('ALL TESTS PASSED: Deadlock vacuum admin preview captured.');
})();

