// Convert a local HTML file to PDF using Playwright's Chromium.
// Usage: node docs/html2pdf.mjs <input.html> <output.pdf>
import { chromium } from 'playwright';
import { pathToFileURL } from 'url';
import { resolve } from 'path';

const input = process.argv[2];
const output = process.argv[3];
if (!input || !output) {
  console.error('Usage: node docs/html2pdf.mjs <input.html> <output.pdf>');
  process.exit(1);
}

const browser = await chromium.launch();
const page = await browser.newPage();
await page.goto(pathToFileURL(resolve(input)).href, { waitUntil: 'networkidle' });
await page.pdf({
  path: output,
  printBackground: true,
  preferCSSPageSize: true, // honor the @page size + margins in the HTML's <style>
});
await browser.close();
console.log('PDF written to ' + output);
