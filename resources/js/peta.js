import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-routing-machine';
import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

L.Icon.Default.mergeOptions({ iconUrl, iconRetinaUrl, shadowUrl });

const angka = (nilai) => {
    const n = parseFloat(nilai);

    return Number.isFinite(n) ? n : null;
};

/** Peta dasar dari atribut data-* pada wadah. */
function buatPeta(wadah) {
    const d = wadah.dataset;
    const peta = L.map(wadah, { scrollWheelZoom: false }).setView(
        [angka(d.pusatLat) ?? -0.8762, angka(d.pusatLng) ?? 131.2558],
        parseInt(d.zoom || '13', 10),
    );
    L.tileLayer(d.ubin, { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(peta);

    return peta;
}

function tanya(url) {
    return fetch(url, { headers: { Accept: 'application/json' } }).then((r) => (r.ok ? r.json() : Promise.reject(r)));
}

function pemilih(akar) {
    const wadah = akar.querySelector('[data-peta-kanvas]');
    const peta = buatPeta(wadah);
    const inputLat = document.getElementById(akar.dataset.inputLat);
    const inputLng = document.getElementById(akar.dataset.inputLng);
    const inputAlamat = document.getElementById(akar.dataset.inputAlamat);
    const status = akar.querySelector('[data-peta-status]');
    const nominatim = akar.dataset.nominatim;
    let penanda = null;

    const pesan = (teks) => {
        status.textContent = teks || '';
    };

    const taruh = (lat, lng, cariAlamat) => {
        if (penanda) {
            penanda.setLatLng([lat, lng]);
        } else {
            penanda = L.marker([lat, lng], { draggable: true }).addTo(peta);
            penanda.on('dragend', () => {
                const p = penanda.getLatLng();
                taruh(p.lat, p.lng, true);
            });
        }
        peta.setView([lat, lng], Math.max(peta.getZoom(), 16));
        inputLat.value = lat.toFixed(7);
        inputLng.value = lng.toFixed(7);
        if (cariAlamat && inputAlamat) {
            tanya(`${nominatim}/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then((hasil) => {
                    if (hasil.display_name) {
                        inputAlamat.value = hasil.display_name;
                    }
                })
                .catch(() => pesan('Alamat tidak dapat dicari otomatis. Isi kolom alamat secara manual.'));
        }
    };

    const awalLat = angka(inputLat.value);
    const awalLng = angka(inputLng.value);
    if (awalLat !== null && awalLng !== null) {
        taruh(awalLat, awalLng, false);
    }

    peta.on('click', (e) => taruh(e.latlng.lat, e.latlng.lng, true));

    akar.querySelector('[data-peta-lokasi]')?.addEventListener('click', () => {
        pesan('Membaca lokasi...');
        window.Sigap.lokasiSaya(
            ({ lat, lng }) => {
                pesan('');
                taruh(lat, lng, true);
            },
            (teks) => pesan(teks),
        );
    });

    const hasil = akar.querySelector('[data-peta-hasil]');
    const cari = () => {
        const kata = akar.querySelector('[data-peta-kata]').value.trim();
        if (kata === '') {
            return;
        }
        pesan('Mencari...');
        tanya(`${nominatim}/search?format=json&countrycodes=id&limit=5&q=${encodeURIComponent(kata)}`)
            .then((daftar) => {
                hasil.replaceChildren();
                pesan(daftar.length ? '' : 'Alamat tidak ditemukan.');
                daftar.forEach((item) => {
                    const tombol = document.createElement('button');
                    tombol.type = 'button';
                    tombol.className = 'block w-full min-h-touch px-3 text-left text-body-sm hover:bg-canvas';
                    tombol.textContent = item.display_name;
                    tombol.addEventListener('click', () => {
                        hasil.replaceChildren();
                        if (inputAlamat) {
                            inputAlamat.value = item.display_name;
                        }
                        taruh(parseFloat(item.lat), parseFloat(item.lon), false);
                    });
                    hasil.appendChild(tombol);
                });
            })
            .catch(() => pesan('Pencarian alamat gagal. Coba lagi atau isi alamat secara manual.'));
    };
    akar.querySelector('[data-peta-cari]')?.addEventListener('click', cari);
    akar.querySelector('[data-peta-kata]')?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            cari();
        }
    });
}

function pelacak(akar) {
    const wadah = akar.querySelector('[data-peta-kanvas]');
    const peta = buatPeta(wadah);
    const jemput = [angka(akar.dataset.lat), angka(akar.dataset.lng)];
    if (jemput[0] === null || jemput[1] === null) {
        return;
    }
    L.marker(jemput).addTo(peta).bindTooltip('Lokasi jemput');
    peta.setView(jemput, 15);

    let sopir = null;
    let rute = null;

    const perbarui = (isi) => {
        const posisi = isi?.data?.sopir;
        if (!posisi || angka(posisi.lat) === null || angka(posisi.lng) === null) {
            return;
        }
        const titik = L.latLng(angka(posisi.lat), angka(posisi.lng));
        if (sopir) {
            sopir.setLatLng(titik);
        } else {
            sopir = L.circleMarker(titik, { radius: 8 }).addTo(peta).bindTooltip('Sopir');
        }
        if (!rute && akar.dataset.osrm) {
            rute = L.Routing.control({
                waypoints: [titik, L.latLng(jemput)],
                router: L.Routing.osrmv1({ serviceUrl: akar.dataset.osrm }),
                createMarker: () => null,
                addWaypoints: false,
                show: false,
                fitSelectedRoutes: true,
            }).addTo(peta);
        } else if (rute) {
            rute.setWaypoints([titik, L.latLng(jemput)]);
        }
    };

    if (akar.dataset.urlPosisi) {
        window.Sigap.polling(akar.dataset.urlPosisi, parseInt(akar.dataset.detik || '10', 10), perbarui);
    }
}

function faskes(akar) {
    const peta = buatPeta(akar.querySelector('[data-peta-kanvas]'));
    let daftar = [];
    try {
        daftar = JSON.parse(akar.dataset.daftar || '[]');
    } catch (e) {
        daftar = [];
    }
    const batas = [];
    daftar.forEach((f) => {
        const lat = angka(f.lat);
        const lng = angka(f.lng);
        if (lat === null || lng === null) {
            return;
        }
        const isi = document.createElement('div');
        const nama = document.createElement('strong');
        nama.textContent = f.nama;
        isi.appendChild(nama);
        if (f.jenis) {
            isi.appendChild(document.createElement('br'));
            isi.appendChild(document.createTextNode(f.jenis));
        }
        L.marker([lat, lng]).addTo(peta).bindPopup(isi);
        batas.push([lat, lng]);
    });
    if (batas.length) {
        peta.fitBounds(batas, { padding: [24, 24], maxZoom: 15 });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-peta="pemilih"]').forEach(pemilih);
    document.querySelectorAll('[data-peta="pelacak"]').forEach(pelacak);
    document.querySelectorAll('[data-peta="faskes"]').forEach(faskes);
});
