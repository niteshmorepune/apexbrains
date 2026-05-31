// Responsive QA screenshot capture.
// Usage: php artisan serve --port=8000   then   node docs/screenshots.mjs
// Logs in ONCE per portal (reused across viewports) to stay under the login throttle:6,1.
import { chromium } from 'playwright';
import fs from 'fs';

const OUT = 'docs/screenshots';
fs.mkdirSync(OUT, { recursive: true });
const BASE = 'http://127.0.0.1:8000';

const viewports = {
  desktop: { width: 1440, height: 900 },
  tablet:  { width: 768,  height: 1024 },
  mobile:  { width: 390,  height: 844 },
};

const portals = {
  admin: {
    login: '/admin/login', email: 'admin@apexbrains.in', pass: 'password', drawer: true,
    pages: [
      ['dashboard',   '/admin'],
      ['leaderboard', '/admin/leaderboard'],
      ['performance', '/admin/franchises/performance'],
      ['resources',   '/admin/resources'],
      ['commissions', '/admin/commissions'],
    ],
  },
  franchise: {
    login: '/franchise/login', email: 'kothrud@apexbrains.in', pass: 'password', drawer: true,
    pages: [
      ['dashboard', '/franchise'],
      ['students',  '/franchise/students'],
      ['fees',      '/franchise/fees'],
    ],
  },
  student:  { login: '/login', email: 'arjun@student.in',  pass: 'password', drawer: false, pages: [['home', '/student']] },
  external: { login: '/login', email: 'external@test.in',  pass: 'password', drawer: false, pages: [['home', '/external']] },
};

const browser = await chromium.launch();
let n = 0;

for (const [portal, cfg] of Object.entries(portals)) {
  const ctx = await browser.newContext({ viewport: viewports.desktop });
  const page = await ctx.newPage();

  await page.goto(BASE + cfg.login, { waitUntil: 'load' });
  await page.fill('input[name="email"]', cfg.email);
  await page.fill('input[name="password"]', cfg.pass);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('load');
  await page.waitForTimeout(400);

  for (const [vpName, vp] of Object.entries(viewports)) {
    await page.setViewportSize(vp);
    for (const [name, path] of cfg.pages) {
      await page.goto(BASE + path, { waitUntil: 'load' });
      await page.waitForTimeout(800); // let Alpine / Chart.js settle
      await page.screenshot({ path: `${OUT}/${portal}-${name}-${vpName}.png`, fullPage: true });
      n++; console.log(`saved ${portal}-${name}-${vpName}.png`);
    }
    if (cfg.drawer && vpName === 'mobile') {
      await page.goto(BASE + cfg.pages[0][1], { waitUntil: 'load' });
      await page.click('button[aria-label="Open menu"]');
      await page.waitForTimeout(500);
      await page.screenshot({ path: `${OUT}/${portal}-drawer-mobile.png`, fullPage: false });
      n++; console.log(`saved ${portal}-drawer-mobile.png`);
    }
  }
  await ctx.close();
}

await browser.close();
console.log(`\nDONE — ${n} screenshots in ${OUT}/`);
