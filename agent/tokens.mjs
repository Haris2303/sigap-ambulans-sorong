// Menghasilkan resources/css/design-tokens.css dari front matter DESIGN.md.
// Dijalankan oleh runner (node agent/tokens.mjs). Berkas ini dan hasilnya dikunci: agen tidak boleh mengubahnya.
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import YAML from 'yaml';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const galat = (pesan) => { console.error('tokens.mjs: ' + pesan); process.exit(1); };

const sumber = readFileSync(resolve(root, 'DESIGN.md'), 'utf8').replace(/\r\n/g, '\n');
const cocok = sumber.match(/^---\n([\s\S]*?)\n---\n/);
if (!cocok) galat('front matter DESIGN.md tidak ditemukan');
const t = YAML.parse(cocok[1]) ?? {};

const warna = t.colors ?? {};
const huruf = t.typography ?? {};
const sudut = t.rounded ?? {};
const jarak = t.spacing ?? {};

for (const wajib of ['canvas', 'surface', 'on-surface', 'on-surface-muted', 'line', 'control-border', 'primary', 'primary-hover', 'primary-soft', 'on-primary', 'danger', 'danger-soft', 'warning', 'warning-soft', 'success', 'success-soft', 'neutral-soft', 'scrim']) {
  if (!(wajib in warna)) galat('token warna wajib tidak ada: ' + wajib);
}
for (const wajib of ['display', 'h1', 'h2', 'h3', 'body', 'body-sm', 'label', 'caption']) {
  if (!(wajib in huruf)) galat('token tipografi wajib tidak ada: ' + wajib);
}
for (const wajib of ['sm', 'md', 'lg', 'full']) {
  if (!(wajib in sudut)) galat('token rounded wajib tidak ada: ' + wajib);
}
for (const wajib of ['touch', 'rail', 'content-max']) {
  if (!(wajib in jarak)) galat('token spacing wajib tidak ada: ' + wajib);
}

const heks = /^#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/;
for (const [k, v] of Object.entries(warna)) {
  if (!heks.test(String(v))) galat('warna ' + k + ' harus heksadesimal #RRGGBB atau #RRGGBBAA: ' + v);
}

const rgbBayangan = (hex) => {
  const h = hex.replace('#', '');
  return [0, 2, 4].map((i) => parseInt(h.slice(i, i + 2), 16)).join(' ');
};

const baris = [];
baris.push('/* DIHASILKAN dari DESIGN.md oleh agent/tokens.mjs. Jangan diubah dengan tangan. */');
baris.push('@theme {');
baris.push('  /* Palet dan skala bawaan Tailwind dimatikan: hanya token DESIGN.md yang ada. */');
for (const ns of ['color', 'font', 'text', 'font-weight', 'tracking', 'radius', 'shadow', 'inset-shadow', 'drop-shadow', 'blur']) {
  baris.push('  --' + ns + '-*: initial;');
}
const keluarga = String(huruf.body.fontFamily ?? 'Public Sans Variable');
baris.push('  --font-sans: "' + keluarga + '", ui-sans-serif, system-ui, sans-serif;');
baris.push('  --font-weight-normal: 400;');
baris.push('  --font-weight-medium: 500;');
baris.push('  --font-weight-semibold: 600;');
for (const [k, v] of Object.entries(warna)) baris.push('  --color-' + k + ': ' + String(v).toLowerCase() + ';');
for (const [k, v] of Object.entries(sudut)) baris.push('  --radius-' + k + ': ' + v + ';');
for (const [k, v] of Object.entries(huruf)) {
  baris.push('  --text-' + k + ': ' + v.fontSize + ';');
  if (v.lineHeight !== undefined) baris.push('  --text-' + k + '--line-height: ' + v.lineHeight + ';');
  if (v.letterSpacing !== undefined) baris.push('  --text-' + k + '--letter-spacing: ' + v.letterSpacing + ';');
  if (v.fontWeight !== undefined) baris.push('  --text-' + k + '--font-weight: ' + v.fontWeight + ';');
}
baris.push('  --shadow-overlay: 0 8px 24px rgb(' + rgbBayangan(String(warna['on-surface'])) + ' / 0.12);');
baris.push('  --spacing-touch: ' + jarak.touch + ';');
baris.push('  --spacing-rail: ' + jarak.rail + ';');
baris.push('  --container-content: ' + jarak['content-max'] + ';');
baris.push('}');

const tujuan = resolve(root, 'resources/css/design-tokens.css');
mkdirSync(dirname(tujuan), { recursive: true });
writeFileSync(tujuan, baris.join('\n') + '\n', 'utf8');
console.log('design-tokens.css dihasilkan: ' + Object.keys(warna).length + ' warna, ' + Object.keys(huruf).length + ' gaya teks.');
