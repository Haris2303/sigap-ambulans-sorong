// Waktu tunggu pesanan (x-ui.waktu-tunggu): teks dan nada dihitung ulang supaya tetap benar pada halaman yang lama terbuka.
// Server mengirim detik tunggu saat halaman dibuat (data-tunggu-detik); selisih waktu sejak halaman dimuat dihitung dari jam
// perangkat, jadi jam perangkat yang salah tidak memengaruhi angkanya. Aturan sama dengan App\Support\WaktuTunggu; ubah bersamaan.

/** "Baru dibuat", "Menunggu 12 mnt", "Menunggu 1 j 5 mnt", "Menunggu 2 hari". */
export function durasi(menit) {
    if (menit < 1) {
        return 'Baru dibuat';
    }
    if (menit < 60) {
        return `Menunggu ${menit} mnt`;
    }
    if (menit < 1440) {
        const sisa = menit % 60;

        return `Menunggu ${Math.floor(menit / 60)} j${sisa > 0 ? ` ${sisa} mnt` : ''}`;
    }

    return `Menunggu ${Math.floor(menit / 1440)} hari`;
}

/** netral di bawah ambang peringatan, peringatan mulai ambang peringatan, bahaya mulai ambang bahaya. */
export function nadaTunggu(menit, peringatan, bahaya) {
    if (menit >= bahaya) {
        return 'bahaya';
    }

    return menit >= peringatan ? 'peringatan' : 'netral';
}

/** Hitung ulang semua lencana; `berlalu` = milidetik sejak halaman dimuat. */
export function perbaruiWaktuTunggu(dokumen, berlalu) {
    dokumen.querySelectorAll('[data-waktu-tunggu]').forEach((el) => {
        const awal = Number(el.dataset.tungguDetik);
        if (!Number.isFinite(awal)) {
            return;
        }
        const menit = Math.floor((awal + berlalu / 1000) / 60);
        el.querySelector('[data-teks]').textContent = durasi(menit);
        el.dataset.nada = nadaTunggu(menit, Number(el.dataset.ambangPeringatan), Number(el.dataset.ambangBahaya));
    });
}

export function pasangWaktuTunggu(dokumen = document, sekarang = () => Date.now(), jadwal = (fn, ms) => setInterval(fn, ms)) {
    dokumen.addEventListener('DOMContentLoaded', () => {
        if (!dokumen.querySelector('[data-waktu-tunggu]')) {
            return;
        }
        const mulai = sekarang();
        const jalan = () => perbaruiWaktuTunggu(dokumen, sekarang() - mulai);
        jadwal(jalan, 30000);
        // Tab yang kembali tampil (atau halaman yang dipulihkan dari riwayat) langsung dihitung ulang.
        dokumen.addEventListener('visibilitychange', jalan);
    });
}
