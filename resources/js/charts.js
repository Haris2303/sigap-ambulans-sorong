// Grafik dasbor arah B (docs/RETEMA_B.md bagian 7.1): garis dan donat Chart.js. Dimuat app.js sebagai bundel terpisah,
// hanya di halaman yang punya canvas[data-chart]. Hanya bagian Chart.js yang dipakai yang didaftarkan; warna dan huruf
// datang dari token CSS (bacaTema), tidak ada hex di skrip ini kecuali suffix alfa gradien (dua digit heksa opasitas).
import {
    ArcElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { bacaTema } from './grafik-opsi';

Chart.register(LineController, DoughnutController, LineElement, PointElement, ArcElement, LinearScale, CategoryScale, Tooltip, Filler);

const format = new Intl.NumberFormat('id-ID');
const kurangGerak = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

/** Gradien vertikal primary 20% di atas ke 0% di dasar, dibuat dari chartArea kanvas (docs/RETEMA_B.md 7.1). */
function gradienIsi(konteks, warna) {
    const { chart } = konteks;
    const { ctx, chartArea } = chart;
    if (!chartArea) {
        return 'transparent';
    }

    const gradien = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
    gradien.addColorStop(0, warna + '33');
    gradien.addColorStop(1, warna + '00');

    return gradien;
}

function tooltipGelap(tema, panggilan) {
    return {
        enabled: true,
        backgroundColor: tema.warna('inverse-surface'),
        titleColor: tema.warna('inverse-on-surface'),
        bodyColor: tema.warna('inverse-on-surface'),
        titleFont: { ...tema.teks('body-s'), size: 11 },
        bodyFont: { ...tema.teks('body-s'), size: 13, weight: 700 },
        cornerRadius: 12,
        padding: { x: 14, y: 10 },
        caretSize: 6,
        displayColors: false,
        callbacks: panggilan,
    };
}

/** Grafik garis "Pesanan {periode}". data: { labels, tickStep, datasets: [{ label, data, color, width, fill }], tooltipTitles, unit }. */
function konfigGaris(data, tema) {
    const { labels, tickStep = 1, datasets, tooltipTitles = labels, unit = '' } = data;
    const satuan = unit ? ` ${unit}` : '';
    const kecil = tema.teks('body-s');

    return {
        type: 'line',
        data: {
            labels,
            datasets: datasets.map((d) => {
                const warna = tema.warna(d.color);

                return {
                    label: d.label,
                    data: d.data,
                    borderColor: warna,
                    borderWidth: d.width ?? 3,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: tema.warna('surface-container-lowest'),
                    pointHoverBorderColor: warna,
                    pointHoverBorderWidth: 3,
                    fill: d.fill ? 'origin' : false,
                    backgroundColor: d.fill ? (ctx) => gradienIsi(ctx, warna) : 'transparent',
                };
            }),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: kurangGerak() ? false : { duration: 700, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: {
                        autoSkip: false,
                        maxRotation: 0,
                        color: tema.warna('on-surface-variant'),
                        font: kecil,
                        callback(nilai, indeks) {
                            return indeks % tickStep === 0 ? this.getLabelForValue(nilai) : '';
                        },
                    },
                },
                y: {
                    min: 0,
                    border: { display: false },
                    // 3 sampai 5 garis kisi (docs/RETEMA_B.md 7.1), bukan satu per nilai bulat.
                    ticks: { precision: 0, maxTicksLimit: 5, color: tema.warna('on-surface-variant'), font: kecil },
                    grid: { color: tema.warna('surface-container-highest'), dash: [4, 6] },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: tooltipGelap(tema, {
                    title: (butir) => tooltipTitles[butir[0].dataIndex],
                    label: (butir) => `${format.format(butir.parsed.y)}${satuan} ${butir.dataset.label.toLowerCase()}`,
                }),
            },
        },
    };
}

/** Grafik donat "Status pesanan". data: { segments: [{ label, value, color }], unit }. */
function konfigDonat(data, tema) {
    const { segments, unit = '' } = data;
    const kosong = segments.length === 0;
    const warna = kosong ? [tema.warna('surface-container-highest')] : segments.map((s) => tema.warna(s.color));
    const total = segments.reduce((jumlah, s) => jumlah + s.value, 0);
    const satuan = unit ? ` ${unit}` : '';

    return {
        type: 'doughnut',
        data: {
            labels: segments.map((s) => s.label),
            datasets: [{
                data: kosong ? [1] : segments.map((s) => s.value),
                backgroundColor: warna,
                hoverBackgroundColor: warna,
                borderWidth: 0,
                hoverBorderWidth: 0,
                hoverOffset: 0,
                spacing: segments.length > 1 ? 3 : 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '76%',
            rotation: 0,
            animation: kurangGerak() ? false : { duration: 700, easing: 'easeOutQuart' },
            ...(kosong ? { events: [] } : {}),
            plugins: {
                legend: { display: false },
                tooltip: kosong ? { enabled: false } : tooltipGelap(tema, {
                    title: (butir) => segments[butir[0].dataIndex].label,
                    label: (butir) => {
                        const s = segments[butir.dataIndex];
                        const persen = total > 0 ? Math.round((s.value / total) * 100) : 0;

                        return `${format.format(s.value)}${satuan}, ${persen}%`;
                    },
                }),
            },
        },
    };
}

const pembangun = { garis: konfigGaris, donat: konfigDonat };

function pasang() {
    const tema = bacaTema();
    Chart.defaults.font.family = tema.keluarga;
    Chart.defaults.color = tema.warna('on-surface-variant');

    document.querySelectorAll('canvas[data-chart]').forEach((kanvas) => {
        if (Chart.getChart(kanvas)) {
            return;
        }

        let data;
        try {
            data = JSON.parse(kanvas.dataset.chart);
        } catch {
            return;
        }

        const bangun = pembangun[data.tipe];
        if (bangun) {
            new Chart(kanvas, bangun(data, tema));
        }
    });
}

const huruf = bacaTema().teks('body-s');
if (document.fonts?.load) {
    document.fonts.load(`400 ${huruf.size}px ${huruf.family}`).then(pasang, pasang);
} else {
    pasang();
}
