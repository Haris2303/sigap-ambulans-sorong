// Penjaga kirim formulir: saat formulir dikirim, tombol kirimnya dinonaktifkan dan labelnya diganti kata kerja proses
// ("Mengirim pesanan..."), supaya ketukan kedua tidak mengirim dua kali dan pengguna tahu ada yang berjalan.
// Sengaja tanpa spinner berputar (DESIGN.md, Motion: tidak ada animasi berulang).

const TEKS_BAWAAN = 'Memproses...';
const SIMPUL_TEKS = 3;

/** Semua tombol kirim milik formulir, termasuk yang berada di luar formulir lewat atribut form="id". */
export function tombolKirim(form, dokumen = document) {
    const dalam = [...form.querySelectorAll('button[type="submit"]')];
    const luar = form.id ? [...dokumen.querySelectorAll(`button[type="submit"][form="${form.id}"]`)] : [];

    return [...new Set([...dalam, ...luar])];
}

/** Simpul teks pertama yang berisi label; tombol hanya-ikon tidak punya, labelnya dibiarkan. */
const simpulLabel = (tombol) => [...tombol.childNodes].find((n) => n.nodeType === SIMPUL_TEKS && n.textContent.trim() !== '');

export function tandaiSibuk(form, dokumen = document) {
    form.dataset.sibuk = '1';
    form.setAttribute('aria-busy', 'true');

    tombolKirim(form, dokumen).forEach((tombol) => {
        const simpul = simpulLabel(tombol);
        if (simpul) {
            tombol.dataset.labelAsli = simpul.textContent;
            simpul.textContent = ` ${tombol.dataset.memuat || TEKS_BAWAAN} `;
        }
        tombol.disabled = true;
    });
}

export function pulihkan(form, dokumen = document) {
    delete form.dataset.sibuk;
    form.removeAttribute('aria-busy');

    tombolKirim(form, dokumen).forEach((tombol) => {
        const simpul = simpulLabel(tombol);
        if (simpul && tombol.dataset.labelAsli !== undefined) {
            simpul.textContent = tombol.dataset.labelAsli;
        }
        delete tombol.dataset.labelAsli;
        tombol.disabled = false;
    });
}

export function pasangPenjagaKirim(dokumen = document, jendela = window) {
    dokumen.addEventListener('submit', (e) => {
        const form = e.target;
        if (e.defaultPrevented || !form || form.dataset === undefined) {
            return;
        }
        if (form.dataset.sibuk === '1') {
            // Kiriman kedua dari formulir yang sama sebelum halaman berpindah.
            e.preventDefault();

            return;
        }
        form.dataset.sibuk = '1';
        // Ditunda satu putaran supaya pengiriman berjalan lebih dulu sebelum tombol dinonaktifkan.
        setTimeout(() => tandaiSibuk(form, dokumen), 0);
    });

    // Kembali lewat riwayat peramban (bfcache) memulihkan halaman apa adanya, jadi tombol harus dipulihkan.
    jendela.addEventListener('pageshow', (e) => {
        if (e.persisted) {
            dokumen.querySelectorAll('form[data-sibuk="1"]').forEach((form) => pulihkan(form, dokumen));
        }
    });
}
