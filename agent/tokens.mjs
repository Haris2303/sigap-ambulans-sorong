// Menghasilkan resources/css/design-tokens.css dari front matter DESIGN.md (peran warna Material 3, arah B docs/RETEMA_B.md).
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

// Level 1 sampai 3 (M3 lama) dan tingkat arah B memakai warna elevation.shadow-color; lapisan ambient opsional.
// Tingkat dasbor lama memakai warna per tingkat (`color`). Tingkat lama dihapus di R9 setelah tidak dipakai.
const TINGKAT_ELEVASI = ['level-1', 'level-2', 'level-3'];
const TINGKAT_B = ['card', 'control', 'chip', 'overlay'];
const TINGKAT_DASBOR = ['card-raised', 'nav-active'];
const BAGIAN_ELEVASI = ['key', 'key-opacity', 'ambient', 'ambient-opacity'];
const BAGIAN_B = ['key', 'key-opacity'];
const BAGIAN_DASBOR = ['color', 'key', 'key-opacity'];
const KEADAAN = ['hover', 'focus', 'pressed', 'dragged', 'disabled-content', 'disabled-container'];
const BAGIAN_HURUF = ['fontSize', 'lineHeight', 'fontWeight', 'letterSpacing'];

const wajibAda = (nama, peta, daftar) => {
  for (const wajib of daftar) {
    if (!(wajib in peta)) galat('token ' + nama + ' wajib tidak ada: ' + wajib);
  }
};

wajibAda('warna', warna, [
  'primary', 'on-primary', 'primary-container', 'on-primary-container',
  'secondary', 'on-secondary', 'secondary-container', 'on-secondary-container',
  'tertiary', 'on-tertiary', 'tertiary-container', 'on-tertiary-container',
  'success', 'on-success', 'success-container', 'on-success-container',
  'warning', 'on-warning', 'warning-container', 'on-warning-container',
  'error', 'on-error', 'error-container', 'on-error-container',
  'surface', 'surface-container-lowest', 'surface-container-low', 'surface-container',
  'surface-container-high', 'surface-container-highest', 'on-surface', 'on-surface-variant',
  'outline', 'outline-variant', 'inverse-surface', 'inverse-on-surface', 'inverse-primary', 'selected', 'scrim',
  'chart-mint', 'chart-coral', 'chart-amber', 'chart-slate',
  'canvas', 'chart-sky',
]);
wajibAda('tipografi', huruf, [
  'stat-hero', 'stat-l', 'page-title', 'page-title-s', 'title-xl', 'title-l', 'title-m', 'brand', 'ring-value',
  'body-l', 'body-m', 'body-s', 'label-l', 'label-m', 'chip',
  'display-l', 'display-m', 'display-s', 'headline-l', 'headline-m', 'headline-s', 'title-s', 'label-s',
  'card-title', 'stat-m',
]);
wajibAda('rounded', sudut, ['sm', 'md', 'lg', 'xl', '2xl', 'full', 'xs']);
wajibAda('spacing', jarak, ['touch', 'drawer', 'content-max']);
wajibAda('elevation', elevasi, ['shadow-color', ...TINGKAT_ELEVASI, ...TINGKAT_B, ...TINGKAT_DASBOR]);
for (const tingkat of TINGKAT_ELEVASI) wajibAda('elevation.' + tingkat, elevasi[tingkat], BAGIAN_ELEVASI);
for (const tingkat of TINGKAT_B) wajibAda('elevation.' + tingkat, elevasi[tingkat], BAGIAN_B);
for (const tingkat of TINGKAT_DASBOR) wajibAda('elevation.' + tingkat, elevasi[tingkat], BAGIAN_DASBOR);
wajibAda('state', keadaan, KEADAAN);
wajibAda('motion', gerak, ['duration-short-ms', 'duration-medium-ms', 'easing-standard']);

// Satu kelas text-<gaya> memuat ukuran, tinggi baris, bobot, dan jarak huruf; satu keluarga huruf untuk semua gaya.
for (const [k, v] of Object.entries(huruf)) wajibAda('typography.' + k, v ?? {}, BAGIAN_HURUF);
const keluargaSemua = [...new Set(Object.values(huruf).map((v) => String(v.fontFamily ?? '')))];
if (keluargaSemua.length !== 1 || keluargaSemua[0] === '') galat('semua gaya tipografi harus memakai satu fontFamily: ' + keluargaSemua.join(', '));

const heks = /^#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/;
for (const [k, v] of Object.entries(warna)) {
  if (!heks.test(String(v))) galat('warna ' + k + ' harus heksadesimal #RRGGBB atau #RRGGBBAA: ' + v);
}
for (const [k, v] of Object.entries(keadaan)) {
  if (typeof v !== 'number' || v < 0 || v > 1) galat('state ' + k + ' harus angka 0 sampai 1: ' + v);
}

// Warna bayangan: rujukan {colors.<token>} (hanya komponen RGB-nya) atau rgb(R G B) apa adanya.
const rgbDari = (nama, nilai) => {
  const rujukan = String(nilai).match(/^\{colors\.([\w-]+)\}$/);
  if (rujukan && rujukan[1] in warna) {
    return [0, 2, 4].map((i) => parseInt(String(warna[rujukan[1]]).slice(1).slice(i, i + 2), 16)).join(' ');
  }
  const langsung = String(nilai).match(/^rgb\((\d{1,3}) (\d{1,3}) (\d{1,3})\)$/);
  if (langsung && langsung.slice(1).every((n) => Number(n) <= 255)) return langsung.slice(1).join(' ');
  return galat(nama + ' harus rujukan {colors.<token>} yang ada atau rgb(R G B): ' + nilai);
};
const rgbBayangan = rgbDari('elevation.shadow-color', elevasi['shadow-color']);

// Dua lapis (kunci dan sekitar) atau satu lapis bila `ambient` tidak ada; warna per tingkat bila `color` ada.
const bayangan = (nama, b) => {
  const rgb = b.color === undefined ? rgbBayangan : rgbDari('elevation.' + nama + '.color', b.color);
  const lapis = [b.key + ' rgb(' + rgb + ' / ' + b['key-opacity'] + ')'];
  if (b.ambient !== undefined) lapis.push(b.ambient + ' rgb(' + rgb + ' / ' + b['ambient-opacity'] + ')');
  return lapis.join(', ');
};

const baris = [];
baris.push('/* DIHASILKAN dari DESIGN.md oleh agent/tokens.mjs. Jangan diubah dengan tangan. */');
// "static": semua variabel (termasuk --color-chart-* yang hanya dibaca Chart.js) selalu keluar di :root, dipakai kelas atau tidak.
baris.push('@theme static {');
baris.push('  /* Palet dan skala bawaan Tailwind dimatikan: hanya token DESIGN.md yang ada. */');
for (const ns of ['color', 'font', 'text', 'font-weight', 'tracking', 'radius', 'shadow', 'inset-shadow', 'drop-shadow', 'blur', 'ease']) {
  baris.push('  --' + ns + '-*: initial;');
}
const keluarga = keluargaSemua[0];
baris.push('  --font-sans: "' + keluarga + ' Variable", "' + keluarga + '", ui-sans-serif, system-ui, sans-serif;');
baris.push('  --font-weight-normal: 400;');
baris.push('  --font-weight-medium: 500;');
for (const [k, v] of Object.entries(warna)) baris.push('  --color-' + k + ': ' + String(v).toLowerCase() + ';');
for (const [k, v] of Object.entries(sudut)) baris.push('  --radius-' + k + ': ' + v + ';');
for (const [k, v] of Object.entries(huruf)) {
  baris.push('  --text-' + k + ': ' + v.fontSize + ';');
  baris.push('  --text-' + k + '--line-height: ' + v.lineHeight + ';');
  baris.push('  --text-' + k + '--letter-spacing: ' + v.letterSpacing + ';');
  baris.push('  --text-' + k + '--font-weight: ' + v.fontWeight + ';');
}
for (const tingkat of [...TINGKAT_B, ...TINGKAT_ELEVASI, ...TINGKAT_DASBOR]) baris.push('  --shadow-' + tingkat + ': ' + bayangan(tingkat, elevasi[tingkat]) + ';');
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
console.log('design-tokens.css dihasilkan: ' + Object.keys(warna).length + ' warna, ' + Object.keys(huruf).length + ' gaya teks, ' + (TINGKAT_B.length + TINGKAT_ELEVASI.length + TINGKAT_DASBOR.length) + ' tingkat bayangan.');
