# Class diagram

Diambil dari `app/Models`, `app/Services`, `app/Enums`, `app/Policies`.

```mermaid
classDiagram
    class User {
        +faskes()
        +ambulansTetap()
        +pesananDipesan()
        +pesananTugas()
        +scopeSopir()
        +scopePetugas()
        +scopeAktif()
        +punyaPeran()
        +sedangBertugas()
    }
    class Faskes {
        +ambulans()
        +pengguna()
        +scopeAktif()
        +perbaruiKetersediaan()
        +punyaKoordinat()
        +belumDiperbarui()
        +dataLama()
    }
    class Ambulans {
        +faskes()
        +sopirTetap()
        +pesanan()
        +scopeTersedia()
    }
    class Pesanan {
        +pemohon()
        +faskesTujuan()
        +ambulans()
        +sopir()
        +diverifikasiOleh()
        +log()
        +scopeAktif()
        +scopeBerstatus()
        +scopePrioritas()
    }
    class PesananLog {
        +pesanan()
        +pelaku()
    }
    class PesananWorkflow {
        +buat()
        +verifikasi()
        +tolak()
        +batalkanOlehWarga()
        +tugaskan()
        +terima()
        +tolakPenugasan()
        +lepaskanPenugasan()
        +mulaiPerjalanan()
        +selesaikan()
        +batalkanOlehAdmin()
    }
    class PesananPolicy
    class LaporanPesanan {
        +baris()
        +ringkas()
        +responsDetik()
        +durasiDetik()
        +formatDurasi()
    }
    class RingkasanDinkes {
        +pesanan()
        +ketersediaan()
    }
    class RingkasanDasbor {
        +ambil(TabPesanan)
    }
    class PesananStatus {
        <<enumeration>>
        +label()
        +nada()
        +final()
        +memegangSumberDaya()
        +transisiTerbuka()
    }
    class Role {
        <<enumeration>>
        +label()
        +ruteBeranda()
    }
    class TransisiTidakValidException
    class PenugasanTidakValidException

    Faskes "1" --> "*" Ambulans
    Faskes "1" --> "*" User
    User "1" --> "*" Pesanan : pemohon, sopir
    Ambulans "1" --> "*" Pesanan
    Pesanan "1" --> "*" PesananLog
    PesananWorkflow ..> Pesanan : mengubah
    PesananWorkflow ..> PesananLog : menulis
    PesananWorkflow ..> PesananStatus
    PesananWorkflow ..> TransisiTidakValidException
    PesananWorkflow ..> PenugasanTidakValidException
    PesananPolicy ..> Pesanan
    User ..> Role
    Pesanan ..> PesananStatus
    LaporanPesanan ..> Pesanan
    RingkasanDinkes ..> Pesanan
