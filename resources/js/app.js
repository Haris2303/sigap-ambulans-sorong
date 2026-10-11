import './sigap';

// Chart.js menjadi bundel terpisah yang hanya diambil di halaman yang punya grafik.
if (document.querySelector('canvas[data-grafik]')) {
    import('./grafik');
}
