// Konfigurasi grafik dasbor untuk Chart.js. Semua fungsi murni: menerima data dari atribut data-grafik
// (diisi komponen x-ui.chart.*) dan objek tema, lalu mengembalikan config Chart.js.
// Warna, huruf, dan sudut tidak ditulis di sini: semuanya dibaca dari token DESIGN.md lewat variabel CSS.

const TEBAL_BATANG = 36;
const TINGGI_RINTISAN = 3;
const CELAH_DONAT = 3;
const format = new Intl.NumberFormat('id-ID');

/** Tema dari variabel CSS pada akar dokumen. Variabel ada di runtime karena design-tokens.css memakai @theme static. */
export function bacaTema(akar = document.documentElement) {
    const gaya = getComputedStyle(akar);
    const nilai = (nama) => gaya.getPropertyValue(nama).trim();
    const keluarga = nilai('--font-sans');

    return {
        keluarga,
        warna(nama) {
            const hasil = nilai(`--color-${nama}`);
            if (hasil === '') {
                throw new Error(`Token warna tidak ada: ${nama}`);
            }

            return hasil;
        },
        sudut(nama) {
            return parseFloat(nilai(`--radius-${nama}`));
        },
        teks(nama, bobot) {
            return {
                family: keluarga,
                size: parseFloat(nilai(`--text-${nama}`)),
                lineHeight: nilai(`--text-${nama}--line-height`),
                weight: bobot ?? (Number(nilai(`--text-${nama}--font-weight`)) || 400),
            };
        },
    };
}

/** String font untuk kanvas 2D dari spesifikasi huruf Chart.js. */
const fontKanvas = (huruf) => `${huruf.weight} ${huruf.size}px ${huruf.family}`;

/** Tooltip bergaya Material: inverse-surface, teks inverse-on-surface, sudut xs, tanpa penanda warna. */
function tooltipMaterial(tema, panggilan) {
    const huruf = tema.teks('body-s');

    return {
        backgroundColor: tema.warna('inverse-surface'),
        titleColor: tema.warna('inverse-on-surface'),
        bodyColor: tema.warna('inverse-on-surface'),
        titleFont: { ...huruf, weight: 500 },
        bodyFont: huruf,
        cornerRadius: tema.sudut('xs'),
        padding: { x: 12, y: 8 },
        caretSize: 0,
        displayColors: false,
        callbacks: panggilan,
    };
}

/** Label nilai di atas batang dan batang rintisan setinggi 3px untuk nilai 0 (tanpa paket tambahan). */
export const labelNilai = {
    id: 'labelNilai',
    afterDatasetsDraw(chart, _argumen, opsi) {
        const { ctx } = chart;
        const dasar = chart.scales.y.getPixelForValue(0);

        ctx.save();
        ctx.font = fontKanvas(opsi.huruf);
        ctx.textAlign = 'center';
        ctx.textBaseline = 'alphabetic';

        chart.getDatasetMeta(0).data.forEach((batang, i) => {
            const nilai = chart.data.datasets[0].data[i];
            if (nilai === 0) {
                ctx.fillStyle = opsi.warnaRintisan;
                const kiri = batang.x - batang.width / 2;
                if (typeof ctx.roundRect === 'function') {
                    ctx.beginPath();
                    ctx.roundRect(kiri, dasar - TINGGI_RINTISAN, batang.width, TINGGI_RINTISAN, TINGGI_RINTISAN / 2);
                    ctx.fill();
                } else {
                    ctx.fillRect(kiri, dasar - TINGGI_RINTISAN, batang.width, TINGGI_RINTISAN);
                }
            }

            ctx.fillStyle = opsi.warnaTeks;
            ctx.fillText(format.format(nilai), batang.x, nilai === 0 ? dasar - 11 : batang.y - 10);
        });

        ctx.restore();
    },
};

/** Grafik batang per hari. data: { labels: [[hari, tanggal]], values, highlight, max, unit }. */
export function konfigBatang(data, tema) {
    const { labels, values, highlight = null, max, unit = null } = data;
    const primary = tema.warna('primary');
    const sky = tema.warna('chart-sky');
    const warnaTeks = tema.warna('on-surface-variant');
    const warnaBatang = values.map((_, i) => (i === highlight ? primary : sky));
    const kecil = tema.teks('body-s');
    const satuan = unit ? ` ${unit}` : '';

    return {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: warnaBatang,
                hoverBackgroundColor: warnaBatang,
                borderWidth: 0,
                borderSkipped: false,
                borderRadius: { topLeft: tema.sudut('sm'), topRight: tema.sudut('sm') },
                barThickness: TEBAL_BATANG,
            }],
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            layout: { padding: { top: 24 } },
            scales: {
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: {
                        autoSkip: false,
                        maxRotation: 0,
                        padding: 8,
                        color: (c) => (c.index === highlight ? primary : warnaTeks),
                        font: (c) => ({ ...kecil, weight: c.index === highlight ? 500 : kecil.weight }),
                    },
                },
                y: {
                    min: 0,
                    max,
                    border: { display: false },
                    grid: {
                        drawTicks: false,
                        lineWidth: 1,
                        color: (c) => tema.warna(c.tick.value === 0 ? 'outline-variant' : 'surface-container-high'),
                    },
                    ticks: { stepSize: max / 2, precision: 0, padding: 10, color: warnaTeks, font: kecil },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: tooltipMaterial(tema, {
                    title: (butir) => [].concat(butir[0].label).join(' '),
                    label: (butir) => `${format.format(butir.parsed.y)}${satuan}`,
                }),
                labelNilai: {
                    huruf: tema.teks('label-l'),
                    warnaTeks: tema.warna('on-surface'),
                    warnaRintisan: tema.warna('outline-variant'),
                },
            },
        },
        plugins: [labelNilai],
    };
}

/** Grafik donat status. data: { segments: [{ label, value, color, percent }], unit }. Total di tengah ada di HTML. */
export function konfigDonat(data, tema) {
    const { segments, unit = null } = data;
    const kosong = segments.length === 0;
    const warna = kosong ? [tema.warna('surface-container-highest')] : segments.map((s) => tema.warna(s.color));
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
                // Satu segmen penuh tidak diberi celah.
                spacing: segments.length > 1 ? CELAH_DONAT : 0,
            }],
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            // Cincin berjari-jari 56 dan tebal 18 pada kanvas 160: radius luar 81%, lubang 72% dari radius luar.
            radius: '81%',
            cutout: '72%',
            rotation: 0,
            ...(kosong ? { events: [] } : {}),
            plugins: {
                legend: { display: false },
                tooltip: kosong
                    ? { enabled: false }
                    : tooltipMaterial(tema, {
                        title: (butir) => segments[butir[0].dataIndex].label,
                        label: (butir) => {
                            const s = segments[butir.dataIndex];

                            return `${format.format(s.value)}${satuan}, ${s.percent}%`;
                        },
                    }),
            },
        },
    };
}
