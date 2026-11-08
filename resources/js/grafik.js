// Grafik dasbor (Chart.js). Dimuat app.js sebagai bundel terpisah, hanya di halaman yang punya canvas[data-grafik].
// Hanya bagian Chart.js yang dipakai yang didaftarkan; data dan warna datang dari atribut dan token CSS.
import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { bacaTema, konfigBatang, konfigDonat } from './grafik-opsi';

Chart.register(BarController, DoughnutController, BarElement, ArcElement, CategoryScale, LinearScale, Tooltip, Legend);

const pembangun = { batang: konfigBatang, donat: konfigDonat };

function pasang() {
    const tema = bacaTema();
    Chart.defaults.font.family = tema.keluarga;
    Chart.defaults.color = tema.warna('on-surface-variant');
    Chart.defaults.borderColor = tema.warna('surface-container-high');

    document.querySelectorAll('canvas[data-grafik]').forEach((kanvas) => {
        if (Chart.getChart(kanvas)) {
            return;
        }

        let data;
        try {
            data = JSON.parse(kanvas.dataset.grafik);
        } catch {
            return;
        }

        const bangun = pembangun[data.tipe];
        if (bangun) {
            new Chart(kanvas, bangun(data, tema));
        }
    });
}

// Kanvas tidak menggambar ulang saat huruf selesai dimuat, jadi tunggu Roboto sebelum menggambar.
const huruf = bacaTema().teks('body-s');
if (document.fonts?.load) {
    Promise.all([
        document.fonts.load(`400 ${huruf.size}px ${huruf.family}`),
        document.fonts.load(`500 ${huruf.size}px ${huruf.family}`),
    ]).then(pasang, pasang);
} else {
    pasang();
}
