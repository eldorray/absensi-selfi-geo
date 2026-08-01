# Antrean Absen Offline — Desain

**Tanggal:** 2026-08-01
**Lingkup:** `backend/` (API mobile), `frontend(ios)/`, `frontend(android)/`
**Di luar lingkup:** PWA guru, sinkronisasi latar belakang, izin/cuti offline

## Masalah

Sinyal di lokasi sekolah sering jelek. Saat guru menekan Masuk dan jaringan gagal, absennya hilang — tidak tersimpan di mana pun, dan guru baru menyadarinya belakangan. Fitur ini menyimpan absen di perangkat lalu mengirimkannya begitu koneksi kembali, dengan **jam kejadian yang sebenarnya**, bukan jam sinkronisasi.

## Mengapa backend harus ikut berubah

Server menurunkan seluruh keputusan waktu dari `now()` pada saat request tiba:

| Perilaku sekarang | Akibat bila absen diantre |
| :--- | :--- |
| `statusNow($user)` — `AttendanceService:108` | Absen 07:00 yang tersinkron 10:00 tercatat **Terlambat** |
| `check_in_time` = `created_at` | Jam masuk yang ditampilkan salah |
| `checkInWindowError($user)` — `AttendanceService:68` | Antrean di luar jendela absen **ditolak**, datanya hilang |
| `check_out_at = now()` | Jam pulang salah |
| Dedupe hanya `hasCheckedInToday()` | Kiriman ulang setelah respons hilang tak terdeteksi |

Tanpa perubahan server, antrean offline menghasilkan data absensi yang **salah**, bukan sekadar tertunda.

## Keputusan desain

| Keputusan | Pilihan | Alasan |
| :--- | :--- | :--- |
| Kepercayaan pada jam perangkat | Terima, tandai, batasi | Jejak audit ada; batas hari-sama membatasi penyalahgunaan tanpa merusak kasus nyata |
| Cakupan antrean | Absen masuk + pulang | Keduanya terikat waktu & lokasi; izin tidak mendesak dalam hitungan menit |
| Pemicu sinkronisasi | App aktif + jaringan pulih | Tanpa infrastruktur latar belakang; perilaku sama di kedua platform |
| Penyimpanan lokal | JSON + berkas JPEG | Maksimal 2 entri/hari — database lokal berlebihan |
| PWA | Di luar lingkup | Background Sync API tak didukung Safari iOS; penggunanya bukan yang mengeluh sinyal |

### Risiko yang diterima

Guru dapat memundurkan jam perangkat, "absen offline", lalu sinkron sehingga tercatat tepat waktu. Mitigasinya bukan pencegahan mutlak melainkan **keterlihatan**: setiap entri offline menyimpan jam tangkap dan jam sinkron, diberi badge di panel admin, dan ditolak bila lebih tua dari hari berjalan. Manipulasi tetap mungkin, tapi meninggalkan jejak yang bisa disengketakan.

---

## Backend

### Prinsip: tulis `created_at` sebagai waktu tangkap

Untuk entri offline, server menyimpan `created_at` = `captured_at` dari perangkat, dan mencatat `synced_at` terpisah. Akibatnya **seluruh jalur baca yang sudah ada** — jam masuk di dashboard, riwayat, rekap bulanan — otomatis menampilkan waktu yang benar tanpa satu baris pun diubah. Hanya jalur tulis yang berubah.

### Migrasi `attendances`

Semua kolom nullable, sehingga baris lama dan jalur web tetap sah.

| Kolom | Tipe | Guna |
| :--- | :--- | :--- |
| `client_uuid` | string(36), nullable | Idempotensi absen masuk |
| `synced_at` | timestamp, nullable | Kapan server menerima. **Non-null = entri offline** |
| `check_out_client_uuid` | string(36), nullable | Idempotensi absen pulang |
| `check_out_synced_at` | timestamp, nullable | Penanda offline untuk absen pulang |

Unique index: `(user_id, client_uuid)` dan `(user_id, check_out_client_uuid)`.

Tidak ada kolom boolean `is_offline` — `synced_at IS NOT NULL` sudah menjadi penandanya, dan satu sumber kebenaran lebih baik daripada dua yang bisa berselisih.

### Kontrak API

`POST /api/attendance` dan `POST /api/attendance/checkout` menerima dua field **opsional**:

| Field | Aturan |
| :--- | :--- |
| `captured_at` | ISO 8601. Tak boleh sebelum awal hari berjalan; tak boleh lebih dari 2 menit di masa depan |
| `client_uuid` | UUID v4 |

"Hari berjalan" dan "masa depan" dinilai terhadap **jam server** (`config('app.timezone')`) saat request diterima, bukan terhadap jam perangkat — jam perangkat justru yang sedang diverifikasi.

Tanpa keduanya, perilaku identik dengan sekarang — klien lama tidak rusak.

### Aturan waktu

`AttendanceService` menerima parameter waktu dengan **nilai bawaan `now()`**:

```php
public function checkInWindowError(User $user, ?Carbon $at = null): ?string
public function checkOutWindowError(User $user, ?Carbon $at = null): ?string
public function statusAt(User $user, ?Carbon $at = null): AttendanceStatus  // menggantikan statusNow()
```

Nilai bawaan itu yang menjaga `Employee\AttendanceController` (jalur web/PWA) berperilaku persis seperti sekarang. `statusNow()` menjadi alias tipis agar pemanggil lama tidak ikut diubah.

`Api\AttendanceController` menghitung `$capturedAt = $request->capturedAt() ?? now()`, lalu meneruskannya ke ketiga pemeriksaan di atas dan menyetel `created_at`/`check_out_at`.

### Idempotensi

`client_uuid` yang sudah tercatat untuk user tersebut **tidak** membuat baris kedua. Server mengembalikan **200 berisi data absen yang tersimpan**, bukan 422.

Ini bukan detail kenyamanan: kasus paling umum adalah request sampai ke server tapi responsnya hilang di jaringan buruk. Tanpa aturan ini, kiriman ulang menghasilkan "Anda sudah melakukan absensi hari ini" — pesan yang membuat guru mengira absennya gagal padahal berhasil.

### Panel admin

Baris absensi dengan `synced_at` terisi diberi badge "Offline" beserta jam sinkronnya, agar sengketa dapat ditelusuri.

---

## Klien (iOS & Android)

Struktur identik di kedua platform; hanya bahasa dan API sistemnya berbeda.

### Unit

| Unit | Tanggung jawab | Bergantung pada |
| :--- | :--- | :--- |
| `QueuedAttendance` | Satu entri: `uuid`, jenis, `capturedAt`, lat/lon, nama berkas foto, alasan gagal terakhir | — |
| `AttendanceQueue` | Muat/simpan JSON + JPEG di app storage; enqueue, hapus, daftar | Sistem berkas |
| `AttendanceSync` | Kirim entri terlama dulu; tentukan nasib entri dari hasilnya | `AttendanceQueue`, klien API |

### Klasifikasi kegagalan

Inti keandalannya:

| Hasil | Tindakan |
| :--- | :--- |
| Sukses (200/201) | Hapus entri + berkas foto |
| Gagal transport (tak ada koneksi, timeout) | **Antre**; coba lagi pada pemicu berikutnya |
| 422 dari server | **Buang** entri, tampilkan alasannya sekali |
| 401 | Hentikan sinkronisasi; alur sesi berakhir yang menangani |

Tanpa pemisahan ini, antrean akan mengulang selamanya kiriman yang pasti ditolak. Lapisan HTTP kedua klien saat ini meratakan transport dan HTTP menjadi satu pesan "gagal terhubung", jadi `APIError` (iOS) dan `ApiException` (Android) perlu penanda `isTransportFailure`.

Urutan pengiriman **terlama dulu** bukan kosmetik: absen pulang yang dikirim sebelum absen masuknya tersimpan akan ditolak server ("Anda belum melakukan absensi masuk hari ini"). Bila absen masuk memang ditolak permanen, absen pulang yang menyusul ikut ditolak 422 dan dibuang beserta alasannya — hasil yang benar, karena tak ada catatan masuk untuk ditutup.

### Pencegahan absen ganda

Status di Beranda berasal dari server, jadi setelah absen masuk masuk antrean, layar masih menampilkan "Belum Absen" — guru bisa menekan Masuk lagi dan membuat entri kedua. Karena itu isi antrean ikut menentukan tampilan: ada entri masuk tertunda → tombol **Masuk** nonaktif berlabel "Menunggu terkirim", begitu pula Pulang. Praktisnya antrean berisi maksimal dua entri.

### Pemicu sinkronisasi

- App kembali ke depan — `scenePhase == .active` (iOS) / `ON_START` (Android)
- Jaringan pulih — `NWPathMonitor` (iOS) / `ConnectivityManager.NetworkCallback` (Android)
- Manual — tombol "Kirim sekarang" pada kartu antrean

### Antarmuka

- Setelah foto dikirim tanpa sinyal, sheet absen tertutup dengan pesan: "Absen disimpan. Akan terkirim otomatis saat ada sinyal."
- Beranda menampilkan kartu "1 absen menunggu terkirim · Kirim sekarang".
- Entri yang ditolak server menampilkan alasannya sekali, lalu dibuang.

`captured_at` diambil **saat foto dijepret**, bukan saat sinkronisasi — itu seluruh alasan fitur ini ada. Foto sudah dikompres oleh jalur unggah yang ada (`jpegForUpload` di iOS, `compressJpeg` di Android) sebelum ditulis ke antrean.

---

## Pengujian

### Backend (Pest)

1. `captured_at` menentukan status — absen 07:00 disinkron 10:00 → `on_time`, `check_in_time` = `07:00`
2. `captured_at` kemarin → 422, tak ada baris tertulis
3. `captured_at` lebih dari 2 menit di masa depan → 422
4. `client_uuid` diulang → tetap satu baris, respons 200 berisi data yang sama
5. Request tanpa `captured_at` berperilaku seperti sekarang — penjaga regresi klien lama
6. Checkout: idempoten, dan `check_out_at` mengikuti `captured_at`
7. `synced_at` hanya terisi untuk request yang mengirim `captured_at`
8. Jalur web (`Employee\AttendanceController`) tetap memakai `now()` — jaminan PWA tak berubah

### Android (unit test JVM, `app/src/test/`)

- Serialisasi/deserialisasi antrean, termasuk berkas rusak → antrean kosong, bukan crash
- Klasifikasi kegagalan: transport → antre, 422 → buang

### iOS

Proyek iOS **tidak punya test target**, dan membuatnya berada di luar pekerjaan ini (target baru, skema, CI — proyek tersendiri). Sebagai gantinya logika antrean ditulis sebagai fungsi murni yang sepadan dengan versi Android yang teruji, lalu diverifikasi lewat build dan uji manual mode pesawat. Ini kesenjangan yang disebut terbuka, bukan yang ditutupi.

---

## Urutan kerja

1. **Backend** — migrasi → parameter waktu di `AttendanceService` → controller API → test. Perlu **deploy** sebelum klien dapat diuji end-to-end.
2. **iOS** — queue → sync → penanda tombol di Beranda → uji mode pesawat.
3. **Android** — cerminan iOS + unit test.

## Yang sengaja tidak dibangun

- Sinkronisasi latar belakang (WorkManager / BGTaskScheduler)
- Antrean di PWA guru
- Izin/cuti offline
- Enkripsi foto antrean — sandbox aplikasi sudah privat
- Jadwal backoff di luar tiga pemicu di atas
- UI admin di luar badge "Offline"
