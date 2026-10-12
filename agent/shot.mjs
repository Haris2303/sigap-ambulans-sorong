// Alat banding visual (docs/RETEMA_B.md, bagian 10 "Banding visual"). Dijalankan lewat `npm run shot`.
// Menyalakan server khusus mode shot (APP_ENV=shot, database/shot.sqlite, waktu dibekukan 2026-10-11 21:26 WIT),
// masuk sebagai tiap peran, menangkap halaman di agent/shot.json pada lebar 1440 dan 390 ke storage/shots/,
// lalu membandingkan padanan yang ada di docs/referensi-b/ dengan pixelmatch. Tidak mengubah tampilan aplikasi.
import { execFileSync, spawn } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, unlinkSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { chromium } from 'playwright';
import { PNG } from 'pngjs';
import pixelmatch from 'pixelmatch';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const envPath = resolve(root, '.env.shot');
const dbPath = resolve(root, 'database', 'shot.sqlite');
const shotsDir = resolve(root, 'storage', 'shots');
const refDir = resolve(root, 'docs', 'referensi-b');
const serverScript = resolve(root, 'vendor', 'laravel', 'framework', 'src', 'Illuminate', 'Foundation', 'resources', 'server.php');

const cfg = JSON.parse(readFileSync(resolve(root, 'agent', 'shot.json'), 'utf8'));
const porta = cfg.port;
const baseUrl = 'http://127.0.0.1:' + porta;

const galat = (pesan) => { console.error('shot.mjs: ' + pesan); process.exit(1); };

function php(args) {
  return execFileSync('php', args, { cwd: root, stdio: ['ignore', 'pipe', 'inherit'], encoding: 'utf8' });
}

function pastikanEnvShot() {
  if (!existsSync(envPath)) {
    const dbUntukEnv = dbPath.replace(/\\/g, '/');
    const isi = [
      'APP_NAME="SIGAP Ambulans"',
      'APP_ENV=shot',
      'APP_KEY=',
      'APP_DEBUG=false',
      'APP_URL=' + baseUrl,
      'APP_LOCALE=id',
      'APP_FALLBACK_LOCALE=id',
      'APP_FAKER_LOCALE=id_ID',
      'APP_MAINTENANCE_DRIVER=file',
      'BCRYPT_ROUNDS=4',
      'LOG_CHANNEL=stack',
      'LOG_STACK=single',
      'LOG_LEVEL=error',
      'DB_CONNECTION=sqlite',
      'DB_DATABASE=' + dbUntukEnv,
      'SESSION_DRIVER=database',
      'SESSION_LIFETIME=120',
      // Waktu dibekukan ke 2026-10-11 (lihat AppServiceProvider), yang bisa jadi "di masa lalu" dibanding jam asli
      // komputer. Cookie sesi biasa memakai tanggal kedaluwarsa mutlak dari jam beku itu dan langsung dianggap basi
      // oleh browser (Max-Age negatif). expire_on_close membuat cookie sesi tanpa tanggal kedaluwarsa mutlak.
      'SESSION_EXPIRE_ON_CLOSE=true',
      'SESSION_ENCRYPT=false',
      'SESSION_PATH=/',
      'SESSION_DOMAIN=null',
      'BROADCAST_CONNECTION=log',
      'FILESYSTEM_DISK=local',
      'QUEUE_CONNECTION=sync',
      'CACHE_STORE=database',
      'MAIL_MAILER=log',
      'SEED_PASSWORD=password',
      '',
    ].join('\n');
    writeFileSync(envPath, isi, 'utf8');
  }

  const isiSekarang = readFileSync(envPath, 'utf8');
  if (/^APP_KEY=\s*$/m.test(isiSekarang)) {
    php(['artisan', 'key:generate', '--env=shot', '--force']);
  }
}

function pastikanDbBaru() {
  if (existsSync(dbPath)) unlinkSync(dbPath);
  mkdirSync(dirname(dbPath), { recursive: true });
  writeFileSync(dbPath, '');

  try {
    php(['artisan', 'shot:pastikan-db', '--env=shot']);
  } catch {
    galat('DB aktif bukan database/shot.sqlite, dibatalkan demi keamanan.');
  }
}

async function tungguServer() {
  for (let i = 0; i < 40; i++) {
    try {
      const r = await fetch(baseUrl + '/login');
      if (r.status > 0) return;
    } catch {
      // belum siap
    }
    await new Promise((r) => setTimeout(r, 300));
  }
  galat('Server shot tidak merespons di ' + baseUrl);
}

function matikanServer(proc) {
  if (!proc || proc.killed || proc.pid == null) return;
  if (process.platform === 'win32') {
    try {
      execFileSync('taskkill', ['/PID', String(proc.pid), '/T', '/F'], { stdio: 'ignore' });
    } catch {
      // sudah berhenti
    }
  } else {
    try {
      proc.kill('SIGTERM');
    } catch {
      // sudah berhenti
    }
  }
}

/** Memuat PNG, memotong ke ukuran terkecil bersama, lalu pixelmatch. Mengembalikan {persen, tinggiA, tinggiB}. */
function bandingkanPng(pathHasil, pathRujukan, pathDiff) {
  const a = PNG.sync.read(readFileSync(pathHasil));
  const b = PNG.sync.read(readFileSync(pathRujukan));
  const lebar = Math.min(a.width, b.width);
  const tinggi = Math.min(a.height, b.height);

  const potong = (img) => {
    const out = new PNG({ width: lebar, height: tinggi });
    PNG.bitblt(img, out, 0, 0, lebar, tinggi, 0, 0);
    return out;
  };
  const ca = potong(a);
  const cb = potong(b);
  const diff = new PNG({ width: lebar, height: tinggi });

  const jumlahBeda = pixelmatch(ca.data, cb.data, diff.data, lebar, tinggi, { threshold: 0.1 });
  writeFileSync(pathDiff, PNG.sync.write(diff));

  return {
    persen: (jumlahBeda / (lebar * tinggi)) * 100,
    tinggiHasil: a.height,
    tinggiRujukan: b.height,
  };
}

async function masuk(context, email, password) {
  const page = await context.newPage();
  await page.goto(baseUrl + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await Promise.all([
    page.waitForURL((url) => !url.pathname.startsWith('/login'), { timeout: 15000 }),
    page.click('button[type="submit"]'),
  ]);
  await page.close();
}

async function tangkapHalaman(context, h) {
  const hasil = [];
  for (const lebar of cfg.lebar) {
    const page = await context.newPage();
    await page.setViewportSize({ width: lebar, height: 900 });
    await page.goto(baseUrl + h.path, { waitUntil: 'networkidle' });
    await page.waitForTimeout(150);
    const tujuan = resolve(shotsDir, h.nama + '-' + lebar + '.png');
    await page.screenshot({ path: tujuan, fullPage: true });
    await page.close();
    hasil.push({ lebar, tujuan });
  }

  return hasil;
}

async function utama() {
  mkdirSync(shotsDir, { recursive: true });

  console.log('shot.mjs: menyiapkan .env.shot dan database/shot.sqlite ...');
  pastikanEnvShot();
  pastikanDbBaru();

  console.log('shot.mjs: migrate ...');
  php(['artisan', 'migrate', '--env=shot', '--force']);

  console.log('shot.mjs: menabur ShotSeeder ...');
  php(['artisan', 'db:seed', '--env=shot', '--class=ShotSeeder', '--force']);

  console.log('shot.mjs: menyalakan server shot di ' + baseUrl + ' ...');
  const server = spawn('php', ['-S', '127.0.0.1:' + porta, serverScript], {
    cwd: resolve(root, 'public'),
    env: { ...process.env, APP_ENV: 'shot' },
    stdio: 'ignore',
  });

  const ringkasan = [];
  try {
    await tungguServer();

    const browser = await chromium.launch();
    try {
      // Satu context (cookie sesi) per peran, supaya masuk sebagai peran lain tidak kena pengalihan tamu di /login.
      const contextTamu = await browser.newContext();
      const contextPeran = new Map();

      for (const h of cfg.halaman) {
        let context = contextTamu;
        if (h.peran) {
          if (!contextPeran.has(h.peran)) {
            const akun = cfg.akun[h.peran];
            if (!akun) galat('Akun untuk peran "' + h.peran + '" tidak ada di agent/shot.json.');
            const ctx = await browser.newContext();
            await masuk(ctx, akun.email, akun.password);
            contextPeran.set(h.peran, ctx);
          }
          context = contextPeran.get(h.peran);
        }

        const tangkapan = await tangkapHalaman(context, h);
        console.log('shot.mjs: menangkap ' + h.nama + ' (' + tangkapan.map((t) => t.lebar).join('x, ') + 'x)');

        if (h.banding) {
          for (const [lebar, berkasRujukan] of Object.entries(h.banding)) {
            const t = tangkapan.find((x) => String(x.lebar) === String(lebar));
            if (!t) continue;
            const pathRujukan = resolve(refDir, berkasRujukan);
            const pathDiff = resolve(shotsDir, 'diff-' + h.nama + '-' + lebar + '.png');
            const r = bandingkanPng(t.tujuan, pathRujukan, pathDiff);
            ringkasan.push({ nama: h.nama, lebar, rujukan: berkasRujukan, ...r });
          }
        }
      }
    } finally {
      await browser.close();
    }
  } finally {
    matikanServer(server);
  }

  console.log('\nshot.mjs: ringkasan banding piksel (garis dasar sebelum R3, selisih besar adalah wajar):');
  for (const r of ringkasan) {
    console.log(
      '  ' + r.nama + ' @' + r.lebar + ' vs ' + r.rujukan + ': ' + r.persen.toFixed(2) + '% piksel berbeda'
      + ' (tinggi hasil ' + r.tinggiHasil + 'px, tinggi rujukan ' + r.tinggiRujukan + 'px)',
    );
  }
  console.log('\nshot.mjs: tangkapan layar ada di storage/shots/. Server shot sudah dimatikan.');
}

utama().catch((e) => {
  console.error(e);
  process.exit(1);
});
