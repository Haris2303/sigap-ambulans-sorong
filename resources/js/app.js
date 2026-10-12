import './sigap';

// Chart.js menjadi bundel terpisah yang hanya diambil di halaman yang punya grafik.
if (document.querySelector('canvas[data-grafik]')) {
    import('./grafik');
}

// Grafik arah B (docs/RETEMA_B.md 7.1): bundel terpisah lain, hanya di halaman yang punya canvas[data-chart].
if (document.querySelector('canvas[data-chart]')) {
    import('./charts');
}
