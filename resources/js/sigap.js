// Helper bersama SIGAP. Halaman hanya memanggil helper ini; URL selalu dioper dari route() lewat atribut data-*.
import { pasangPenjagaKirim } from './formulir';

pasangPenjagaKirim();

const token = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const Sigap = {
    /** Panggil url tiap `detik` detik (berhenti saat tab tersembunyi). Mengembalikan fungsi penghenti. */
    polling(url, detik, fn) {
        const ambil = async () => {
            if (document.hidden) {
                return;
            }
            try {
                const respons = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (respons.ok) {
                    fn(await respons.json());
                }
            } catch (e) {
                // Jaringan terputus: coba lagi pada putaran berikutnya.
            }
        };
        ambil();
        const id = setInterval(ambil, detik * 1000);

        return () => clearInterval(id);
    },

    /** Kirim JSON (POST/PUT) dengan token CSRF; mengembalikan { status, data }. */
    async kirim(url, data = {}, metode = 'POST') {
        const respons = await fetch(url, {
            method: metode,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(data),
        });

        return { status: respons.status, data: await respons.json().catch(() => ({})) };
    },

    /** Notifikasi peramban dan getar bila diizinkan. */
    notifikasi(judul, isi) {
        if (navigator.vibrate) {
            navigator.vibrate([200, 100, 200]);
        }
        if (!('Notification' in window)) {
            return;
        }
        const tampil = () => new Notification(judul, { body: isi });
        if (Notification.permission === 'granted') {
            tampil();
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then((izin) => izin === 'granted' && tampil());
        }
    },

    /** Posisi GPS saat ini: fn({ lat, lng }), galat(pesan). */
    lokasiSaya(fn, galat = () => {}) {
        if (!navigator.geolocation) {
            galat('Peramban tidak mendukung GPS.');

            return;
        }
        navigator.geolocation.getCurrentPosition(
            (posisi) => fn({ lat: posisi.coords.latitude, lng: posisi.coords.longitude }),
            () => galat('Lokasi tidak dapat dibaca. Izinkan akses lokasi atau isi alamat secara manual.'),
            { enableHighAccuracy: true, timeout: 15000 },
        );
    },
};

window.Sigap = Sigap;

// Angka hidup Dinkes: JSON { data: { kunci: angka } } mengisi [data-ringkasan=kunci], [data-lencana=kunci], [data-angka=kunci], dan label [data-lonceng=kunci].
document.addEventListener('DOMContentLoaded', () => {
    const sumber = document.querySelector('[data-ringkasan-url]');
    if (!sumber) {
        return;
    }
    Sigap.polling(sumber.dataset.ringkasanUrl, Number(sumber.dataset.detik || 10), (hasil) => {
        Object.entries(hasil.data || {}).forEach(([kunci, angka]) => {
            document.querySelectorAll(`[data-ringkasan="${kunci}"] [data-nilai]`).forEach((el) => {
                el.textContent = angka;
            });
            document.querySelectorAll(`[data-lencana="${kunci}"]`).forEach((el) => {
                el.textContent = angka;
                el.hidden = angka < 1;
            });
            document.querySelectorAll(`[data-lonceng="${kunci}"]`).forEach((el) => {
                el.setAttribute('aria-label', `${el.dataset.label}, ${angka}`);
            });
            // Angka mandiri (tanpa kartu pembungkus data-ringkasan) dan penanda yang disembunyikan saat nol, mis. lencana Gawat.
            document.querySelectorAll(`[data-angka="${kunci}"]`).forEach((el) => {
                el.textContent = angka;
            });
            document.querySelectorAll(`[data-sembunyi-nol="${kunci}"]`).forEach((el) => {
                el.hidden = angka < 1;
            });
        });
    });
});

// Dasbor Sopir: polling penugasan (notifikasi saat penugasan baru) dan kirim GPS selama online.
document.addEventListener('DOMContentLoaded', () => {
    const akar = document.querySelector('[data-sopir]');
    if (!akar) {
        return;
    }
    Sigap.polling(akar.dataset.urlPenugasan, Number(akar.dataset.detik || 8), (hasil) => {
        const p = hasil.data?.penugasan;
        const sekarang = p ? `${p.id}:${p.status}` : '';
        if (sekarang === akar.dataset.penugasan) {
            return;
        }
        if (p && akar.dataset.penugasan === '') {
            Sigap.notifikasi('Penugasan baru', `${p.kode}: ${p.lokasi_jemput}`);
        }
        akar.dataset.penugasan = sekarang;
        setTimeout(() => window.location.reload(), 1500);
    });
    if (akar.dataset.online === '1') {
        const kirimPosisi = () => Sigap.lokasiSaya(({ lat, lng }) => Sigap.kirim(akar.dataset.urlLokasi, { lat, lng }));
        kirimPosisi();
        setInterval(kirimPosisi, Number(akar.dataset.detikLokasi || 15) * 1000);
    }
});

// Minta izin notifikasi peramban dari tindakan pengguna (mis. tombol Online).
document.addEventListener('click', (e) => {
    if (e.target.closest('[data-izin-notifikasi]') && 'Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
});

// Dialog konfirmasi bersama untuk semua elemen [data-konfirmasi].
document.addEventListener('click', (e) => {
    const pemicu = e.target.closest('[data-konfirmasi]');
    if (!pemicu) {
        return;
    }
    const dialog = document.getElementById('dialog-konfirmasi');
    if (!dialog || typeof dialog.showModal !== 'function') {
        if (!window.confirm(pemicu.dataset.konfirmasi)) {
            e.preventDefault();
        }

        return;
    }
    e.preventDefault();
    dialog.querySelector('[data-dialog-judul]').textContent = pemicu.dataset.judul || 'Konfirmasi';
    dialog.querySelector('[data-dialog-pesan]').textContent = pemicu.dataset.konfirmasi;
    const aksi = dialog.querySelector('[data-dialog-aksi]');
    aksi.textContent = pemicu.dataset.aksi || pemicu.textContent.trim();
    // Nada tombol aksi: bahaya (error, bawaan) atau teks biasa (data-nada="utama").
    const biasa = pemicu.dataset.nada === 'utama';
    ['bg-error', 'text-on-error', 'px-6'].forEach((kelas) => aksi.classList.toggle(kelas, !biasa));
    ['text-primary', 'px-3'].forEach((kelas) => aksi.classList.toggle(kelas, biasa));
    dialog.pemicu = pemicu;
    dialog.showModal();
});

document.addEventListener('click', (e) => {
    const dialog = e.target.closest('#dialog-konfirmasi');
    if (!dialog) {
        return;
    }
    if (e.target.closest('[data-dialog-batal]')) {
        dialog.close();
    } else if (e.target.closest('[data-dialog-aksi]')) {
        const pemicu = dialog.pemicu;
        dialog.close();
        const form = pemicu?.form ?? pemicu?.closest('form');
        if (form) {
            form.requestSubmit(pemicu.form ? pemicu : undefined);
        }
    }
});

// Snackbar: tombol Tutup menghapus pesan.
document.addEventListener('click', (e) => {
    if (e.target.closest('[data-snackbar-tutup]')) {
        e.target.closest('[data-snackbar]')?.remove();
    }
});

// Laci navigasi modal di bawah lg: buka dengan tombol menu, tutup dengan tombol, scrim, atau Escape.
const aturLaci = (buka) => {
    const rel = document.querySelector('[data-menu-rel]');
    const scrim = document.querySelector('[data-menu-scrim]');
    if (!rel) {
        return;
    }
    rel.classList.toggle('hidden', !buka);
    rel.classList.toggle('flex', buka);
    scrim?.classList.toggle('hidden', !buka);
    (buka ? rel.querySelector('[data-menu-tutup]') : document.querySelector('[data-menu-buka]'))?.focus();
};

document.addEventListener('click', (e) => {
    if (e.target.closest('[data-menu-buka]')) {
        aturLaci(true);
    } else if (e.target.closest('[data-menu-tutup]') || e.target.closest('[data-menu-scrim]')) {
        aturLaci(false);
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.querySelector('[data-menu-scrim]:not(.hidden)')) {
        aturLaci(false);
    }
});
