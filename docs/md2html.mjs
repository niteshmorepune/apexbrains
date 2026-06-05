import { readFileSync, writeFileSync } from 'fs';

const md = readFileSync(process.argv[2], 'utf8');

// --- tiny, purpose-built markdown -> HTML (handles what this doc uses) ---
const esc = s => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const inline = s => esc(s)
  .replace(/`([^`]+)`/g, '<code>$1</code>')
  .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
  .replace(/\b(≈|·)\b/g, '$1');

const lines = md.split(/\r?\n/);
let html = '', i = 0;
const flushTable = (rows) => {
  // rows: array of arrays of cells; row[1] is the --- separator
  const head = rows[0];
  const body = rows.slice(2);
  let t = '<table><thead><tr>' + head.map(c => `<th>${inline(c)}</th>`).join('') + '</tr></thead><tbody>';
  for (const r of body) t += '<tr>' + r.map(c => `<td>${inline(c)}</td>`).join('') + '</tr>';
  return t + '</tbody></table>';
};

while (i < lines.length) {
  let line = lines[i];

  if (/^\s*$/.test(line)) { i++; continue; }

  // horizontal rule
  if (/^---+\s*$/.test(line)) { html += '<hr>'; i++; continue; }

  // headings
  let m = line.match(/^(#{1,6})\s+(.*)$/);
  if (m) { const lvl = m[1].length; html += `<h${lvl}>${inline(m[2])}</h${lvl}>`; i++; continue; }

  // tables (line starts with | )
  if (/^\s*\|/.test(line)) {
    const rows = [];
    while (i < lines.length && /^\s*\|/.test(lines[i])) {
      const cells = lines[i].trim().replace(/^\|/, '').replace(/\|\s*$/, '').split('|').map(c => c.trim());
      rows.push(cells);
      i++;
    }
    html += flushTable(rows);
    continue;
  }

  // ordered list
  if (/^\s*\d+\.\s+/.test(line)) {
    html += '<ol>';
    while (i < lines.length && /^\s*\d+\.\s+/.test(lines[i])) {
      html += `<li>${inline(lines[i].replace(/^\s*\d+\.\s+/, ''))}</li>`;
      i++;
    }
    html += '</ol>';
    continue;
  }

  // unordered list
  if (/^\s*[-*]\s+/.test(line)) {
    html += '<ul>';
    while (i < lines.length && /^\s*[-*]\s+/.test(lines[i])) {
      html += `<li>${inline(lines[i].replace(/^\s*[-*]\s+/, ''))}</li>`;
      i++;
    }
    html += '</ul>';
    continue;
  }

  // emphasis-only italic line (e.g. footer in *...*)
  if (/^\*[^*].*\*$/.test(line.trim())) {
    html += `<p class="muted">${inline(line.trim().replace(/^\*/, '').replace(/\*$/, ''))}</p>`;
    i++; continue;
  }

  // paragraph
  html += `<p>${inline(line)}</p>`;
  i++;
}

const doc = `<!doctype html><html><head><meta charset="utf-8">
<style>
  @page { size: A4; margin: 18mm 16mm; }
  * { box-sizing: border-box; }
  body { font-family: "Segoe UI", Arial, sans-serif; color: #1A2332; font-size: 11.5px; line-height: 1.55; }
  h1 { font-size: 24px; color: #1A2332; border-bottom: 3px solid #1A73E8; padding-bottom: 8px; margin: 0 0 4px; }
  h2 { font-size: 16px; color: #1A73E8; margin: 22px 0 8px; border-bottom: 1px solid #D0D7E2; padding-bottom: 4px; }
  h3 { font-size: 13px; color: #1A2332; margin: 16px 0 4px; }
  p { margin: 6px 0; }
  ul, ol { margin: 6px 0 6px 18px; padding: 0; }
  li { margin: 2px 0; }
  code { background: #F0F3F8; color: #D42B2B; padding: 1px 5px; border-radius: 4px; font-family: "Cascadia Code", Consolas, monospace; font-size: 10.5px; }
  strong { color: #1A2332; }
  hr { border: none; border-top: 1px solid #E2E8F0; margin: 16px 0; }
  table { border-collapse: collapse; width: 100%; margin: 10px 0; font-size: 10.5px; }
  th { background: #1A2332; color: #fff; text-align: left; padding: 7px 9px; font-weight: 600; }
  td { border: 1px solid #D0D7E2; padding: 6px 9px; vertical-align: top; }
  tbody tr:nth-child(even) { background: #F7F9FC; }
  .muted { color: #6B7280; font-style: italic; font-size: 10px; }
  h2, h3 { page-break-after: avoid; }
  table, ul, ol { page-break-inside: avoid; }
</style></head><body>${html}</body></html>`;

writeFileSync(process.argv[3], doc, 'utf8');
console.log('HTML written to ' + process.argv[3]);
