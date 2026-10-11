// Menghasilkan resources/css/design-tokens.css dari front matter DESIGN.md (Material Design 3).
// Dijalankan oleh runner (node agent/tokens.mjs). Berkas ini dan hasilnya tidak diubah dengan tangan:
// ubah DESIGN.md, lalu jalankan ulang skrip ini.
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
const elevasi = t.elevation ?? {};
const keadaan = t.state ?? {};
const gerak = t.motion ?? {};

const TINGKAT_ELEVASI = ['level-1', 'level-2', 'level-3'];
const BAGIAN_ELEVASI = ['key', 'key-opacity', 'ambient', 'ambient-opacity'];
const KEADAAN = ['hover', 'focus', 'pressed', 'dragged', 'disabled-content', 'disabled-container'];

const wajibAda = (nama, peta, daftar) => {
  for (const wajib of daftar) {
    if (!(wajib in peta)) galat('token ' + nama + ' wajib tidak ada: ' + wajib);
  }
};

wajibAda('warna', warna, [
  'primary', 'on-primary', 'primary-container', 'on-primary-container',
  'secondary-container', 'on-secondary-container',
  'error', 'on-error', 'error-container', 'on-error-container',
  'success', 'success-container', 'on-success-container',
  'warning', 'warning-container', 'on-warning-container',
  'surface', 'surface-container-lowest', 'surface-container-low', 'surface-container',
  'surface-container-high', 'surface-container-highest', 'on-surface', 'on-surface-variant',
  'outline', 'outline-variant', 'inverse-surface', 'inverse-on-surface', 'inverse-primary', 'scrim',
]);
wajibAda('tipografi', huruf, [
  'display-l', 'display-m', 'display-s', 'headline-l', 'headline-m', 'headline-s',
  'title-l', 'title-m', 'title-s', 'body-l', 'body-m', 'body-s', 'label-l', 'label-m', 'label-s',
]);
wajibAda('rounded', sudut, ['xs', 'sm', 'md', 'lg', 'xl', 'full']);
wajibAda('spacing', jarak, ['touch', 'drawer', 'content-max']);
wajibAda('elevation', elevasi, ['shadow-color', ...TINGKAT_ELEVASI]);
for (const tingkat of TINGKAT_ELEVASI) wajibAda('elevation.' + tingkat, elevasi[tingkat], BAGIAN_ELEVASI);
wajibAda('state', keadaan, KEADAAN);
wajibAda('motion', gerak, ['duration-short-ms', 'duration-medium-ms', 'easing-standard']);

const heks = /^#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/;
for (const [k, v] of Object.entries(warna)) {
  if (!heks.test(String(v))) galat('warna ' + k + ' harus heksadesimal #RRGGBB atau #RRGGBBAA: ' + v);
}
for (const [k, v] of Object.entries(keadaan)) {
  if (typeof v !== 'number' || v < 0 || v > 1) galat('state ' + k + ' harus angka 0 sampai 1: ' + v);
}

// Warna bayangan diambil dari token warna yang dirujuk (hanya komponen RGB-nya).
const rujukan = String(elevasi['shadow-color']).match(/^\{colors\.([\w-]+)\}$/);
if (!rujukan || !(rujukan[1] in warna)) galat('elevation.shadow-color harus rujukan {colors.<token>} yang ada: ' + elevasi['shadow-color']);
const rgbBayangan = [0, 2, 4].map((i) => parseInt(String(warna[rujukan[1]]).slice(1).slice(i, i + 2), 16)).join(' ');

const bayangan = (b) =>
  b.key + ' rgb(' + rgbBayangan + ' / ' + b['key-opacity'] + '), ' + b.ambient + ' rgb(' + rgbBayangan + ' / ' + b['ambient-opacity'] + ')';

const baris = [];
baris.push('/* DIHASILKAN dari DESIGN.md oleh agent/tokens.mjs. Jangan diubah dengan tangan. */');
baris.push('@theme {');
baris.push('  /* Palet dan skala bawaan Tailwind dimatikan: hanya token DESIGN.md yang ada. */');
for (const ns of ['color', 'font', 'text', 'font-weight', 'tracking', 'radius', 'shadow', 'inset-shadow', 'drop-shadow', 'blur', 'ease']) {
  baris.push('  --' + ns + '-*: initial;');
}
const keluarga = String(huruf['body-l'].fontFamily ?? 'Roboto');
baris.push('  --font-sans: "' + keluarga + ' Variable", "' + keluarga + '", ui-sans-serif, system-ui, sans-serif;');
baris.push('  --font-weight-normal: 400;');
baris.push('  --font-weight-medium: 500;');
for (const [k, v] of Object.entries(warna)) baris.push('  --color-' + k + ': ' + String(v).toLowerCase() + ';');
for (const [k, v] of Object.entries(sudut)) baris.push('  --radius-' + k + ': ' + v + ';');
for (const [k, v] of Object.entries(huruf)) {
  baris.push('  --text-' + k + ': ' + v.fontSize + ';');
  if (v.lineHeight !== undefined) baris.push('  --text-' + k + '--line-height: ' + v.lineHeight + ';');
  if (v.letterSpacing !== undefined) baris.push('  --text-' + k + '--letter-spacing: ' + v.letterSpacing + ';');
  if (v.fontWeight !== undefined) baris.push('  --text-' + k + '--font-weight: ' + v.fontWeight + ';');
}
for (const tingkat of TINGKAT_ELEVASI) baris.push('  --shadow-' + tingkat + ': ' + bayangan(elevasi[tingkat]) + ';');
baris.push('  --spacing-touch: ' + jarak.touch + ';');
baris.push('  --spacing-drawer: ' + jarak.drawer + ';');
baris.push('  --container-content: ' + jarak['content-max'] + ';');
baris.push('  --ease-standard: ' + gerak['easing-standard'] + ';');
baris.push('  --default-transition-duration: ' + gerak['duration-short-ms'] + 'ms;');
baris.push('  --default-transition-timing-function: ' + gerak['easing-standard'] + ';');
baris.push('}');
baris.push('');
baris.push(':root {');
for (const k of KEADAAN) baris.push('  --state-' + k + ': ' + keadaan[k] + ';');
baris.push('  --motion-duration-short: ' + gerak['duration-short-ms'] + 'ms;');
baris.push('  --motion-duration-medium: ' + gerak['duration-medium-ms'] + 'ms;');
baris.push('}');

const tujuan = resolve(root, 'resources/css/design-tokens.css');
mkdirSync(dirname(tujuan), { recursive: true });
writeFileSync(tujuan, baris.join('\n') + '\n', 'utf8');
console.log('design-tokens.css dihasilkan: ' + Object.keys(warna).length + ' warna, ' + Object.keys(huruf).length + ' gaya teks, ' + TINGKAT_ELEVASI.length + ' tingkat bayangan.');
