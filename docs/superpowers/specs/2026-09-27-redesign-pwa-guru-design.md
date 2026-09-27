# Redesign PWA Guru — Spesifikasi Desain

- **Tanggal:** 2026-09-27
- **Branch:** `feat/redesign-guru-pwa`, dibuat dari checkpoint `checkpoint/ui-ux-audit-2026-09-27` (`6382117`)
- **Referensi desain:** `AbsenKU Guru — Alternatif PWA – Beranda.html` di root repo (tidak di-commit; salinan ada di `~/Downloads`). File ini hanya mencakup Beranda, lebar 390px, tema terang.
- **Status:** disetujui secara lisan per bagian; menunggu review spec tertulis.

## 1. Tujuan

Mengganti tampilan seluruh halaman PWA guru dengan bahasa visual dari file desain Beranda. Visualnya krem hangat dan hijau tua, dengan huruf Fraunces dan Plus Jakarta Sans. Hasilnya harus terasa sebagai satu aplikasi utuh.

**Semua fungsi tetap sama.** Absen masuk/pulang dengan kedip dan GPS, izin, riwayat, profil, kelas wali, BK, kesiswaan, rujukan, dan persetujuan izin tetap berjalan seperti sekarang.

**Keputusan yang sudah disetujui:**

| Topik | Keputusan |
|---|---|
| Pendekatan | **A**: sistem desain baru (token + komponen Blade `x-guru.*`), semua halaman guru ditulis ulang di atasnya |
| Cakupan | Semua halaman guru. Beranda mengikuti desain persis; halaman lain diturunkan dari bahasa visual yang sama |
| Nav bawah | Beranda · Riwayat · Izin · Kelas. **Tab Kelas hanya untuk wali kelas.** Tidak ada tombol Masuk/Pulang di nav; absen lewat tombol di kartu hero Beranda |
| Tema | Terang (persis desain) **dan** gelap (turunan). Pengaturan tema dan tombol Keluar pindah ke halaman Profil |

## 2. Sistem visual

### 2.1 Huruf

Keduanya **disimpan di aplikasi sendiri** agar jalan offline. File diambil dari subset latin di bundel desain, keduanya variable font:
- `resources/fonts/fraunces-latin.woff2`: dari `179983a6-…woff2`, ~67 KB.
- `resources/fonts/plus-jakarta-sans-latin.woff2`: dari `39033314-…woff2`, ~27 KB.

| Peran | Huruf | Contoh pemakaian |
|---|---|---|
| Display / angka | Fraunces 500–600 | tanggal "Senin, 28 September" 26px; jam hero 44px; angka rekap 34px; nama kelas 26px |
| UI | Plus Jakarta Sans 400–800 | semua teks lain |

- Angka jam dan rekap memakai `font-variant-numeric: tabular-nums`.
- Format jam mengikuti desain: `06.52` (titik), dan `––.––` bila kosong.
- Admin dan halaman auth tetap memakai Inter; tidak ada perubahan di sana.

### 2.2 Token warna

Token didefinisikan sebagai CSS custom property pada `.guru` (terang) dan `.dark .guru` (gelap). Untuk warna yang dipakai utility, token juga didaftarkan di Tailwind `@theme inline` sebagai `--color-guru-*`.

| Token | Terang (desain) | Gelap (turunan) | Pemakaian |
|---|---|---|---|
| `ground` | `#F4F1EA` | `#111512` | latar halaman |
| `surface` | `#FFFFFF` | `#1A201C` | kartu, nav |
| `surface-2` | `#F4F1EA` | `#222924` | chip netral, baris aktif |
| `ink` | `#1B2420` | `#ECE8DF` | teks utama |
| `muted` | `#5E665F` | `#A3ABA4` | teks sekunder |
| `border` | `#E3DDD0` | `#2E3631` | garis kartu (dekoratif) |
| `divider` | `#EFEAE0` | `#262D28` | pemisah baris daftar |
| `field` | `#8C8579` | `#6F786F` | border input (≥3:1) |
| `primary` | `#1E5A48` | `#8FD1B5` (teks/link) | aksi, link, tab aktif |
| `primary-hover` | `#153F33` | `#A9DEC6` | hover link |
| `primary-soft` | `#DDE9E2` | `#22382F` | pil tab aktif, ikon layanan hijau, avatar |
| `hero` | `#1E5A48` | `#1E5A48` | kartu presensi (sama di kedua tema) |
| `hero-2` | `#2F7560` | `#2F7560` | chip status hero, track progres |
| `hero-line` | `#3C7E69` | `#3C7E69` | pemisah dalam hero |
| `hero-muted` | `#CFE3D8` | `#CFE3D8` | teks sekunder di hero |
| `hero-dim` | `#8FB8A8` | `#8FB8A8` | `––.––` (teks besar) |
| `accent` | `#F2C879` | `#F2C879` | isi progres dan knob |
| `late` / `late-bar` | `#8A5A00` / `#D9A24A` | `#E8B85C` / `#D9A24A` | terlambat |
| `pending-bg` / `pending-ink` | `#F7EBCF` / `#6E4700` | `#3A3020` / `#F2C879` | chip menunggu |
| `izin` / `izin-bar` / `izin-soft` | `#4A5A8C` / `#7E8FC2` / `#E3E7F3` | `#A9B6E6` / `#7E8FC2` / `#262C40` | izin, ikon BK |
| `attn` / `attn-soft` / `attn-ink` | `#A8462A` / `#F5E1D7` / `#8A361E` | `#F0A58B` / `#3A241C` / `#F0A58B` | perhatian, ditolak, tombol pulang, titik notifikasi |
| `empty-bar` | `#ECE7DC` | `#2A312C` | sisa hari kerja di bilah rekap |

**Kontras terukur.** Semua teks memenuhi WCAG AA di kedua tema:

| Pasangan | Rasio |
|---|---|
| ink/ground | 14.1 (terang), 15.1 (gelap) |
| muted/ground | 5.3 (terang), 7.8 (gelap) |
| primary di atas putih | 8.0 |
| hero-muted di atas hero | 6.0 |
| putih di atas terakota | 5.9 |
| chip menunggu | 6.9 |
| chip perhatian | 6.3 |
| `hero-dim` di atas hero | 3.7 — hanya untuk teks ≥24px |

### 2.3 Bentuk dan gerak

**Ukuran:**

| Elemen | Ukuran |
|---|---|
| Kartu | radius 24px, padding 20px |
| Kartu informasi | radius 20px |
| Chip | radius penuh; 12–13px bold |
| Ikon layanan | kotak 40px, radius 12px |
| Tombol ikon header | 44px, radius 14px |
| Tombol utama | tinggi 56px, radius 16px |
| Baris daftar | tinggi minimal 64px |
| Input | tinggi 52px, font 16px |

**Ikon:** stroke 1.8 dengan gaya garis membulat seperti di desain (SVG inline). Tidak memakai emoji atau glyph unicode sebagai ikon.

**Gerak:**
- Umpan balik tekan: `scale(0.98)` 150ms.
- Transisi warna: 160ms.
- View transition antarhalaman: crossfade bawaan browser.
- Knob progres tidak dianimasikan terus-menerus; posisinya dihitung saat halaman dimuat.
- `prefers-reduced-motion`: semua transisi 0ms.

**Tema:** kelas `dark` pada `<html>`, diatur sebelum halaman digambar. Key localStorage `appearance` (`light` | `dark`; kosong berarti ikut OS), dengan fallback key lama `welcome-theme`. Meta `theme-color` mengikuti tema: `#F4F1EA` untuk terang, `#111512` untuk gelap.

## 3. Arsitektur front-end

### 3.1 Berkas baru

**Gaya dan font:**
- `resources/css/guru.css`: diimpor dari `app.css`. Isinya `@font-face`, token, dasar (`.guru`), dan komponen di `@layer components`. Semua selector di-scope ke `.guru`.
- `resources/fonts/fraunces-latin.woff2` dan `resources/fonts/plus-jakarta-sans-latin.woff2`.

**Komponen Blade anonim** di `resources/views/components/guru/`:

| Komponen | Isi |
|---|---|
| `card` | kartu permukaan; varian `hero` |
| `list` + `list-item` | baris daftar 64px: ikon berwarna (tone), judul, keterangan, slot kanan (chip atau hitungan), chevron |
| `chip` | tone `neutral` / `ok` / `late` / `pending` / `izin` / `attn` / `hero` |
| `section` | judul `h2` 15px/800 + tautan kanan opsional |
| `stat` | angka Fraunces + label |
| `field` | label, kontrol (slot), hint, dan error yang terhubung lewat `aria-invalid` + `aria-describedby` |
| `button` | varian `primary` / `secondary` / `hero` (putih di atas hijau) / `pulang` / `danger`; tag `a` atau `button` |
| `icon` | set ikon SVG inline gaya desain (home, calendar, doc, users, swap, bell, pin, login, logout, chevron, send, chat, lock, camera, clock, check, x, alert, info), stroke 1.8, `aria-hidden` |
| `empty` | status kosong yang menjelaskan apa yang akan muncul dan cara mengisinya |
| `notice` | pesan info / peringatan / error / sukses dengan ikon (bukan border kiri berwarna) |

### 3.2 Shell: `resources/views/components/layouts/mobile.blade.php` (ditulis ulang)

- **Props tetap kompatibel:** `title`, `backUrl`, `activeTab`, `showNav`, `isSheet` (diabaikan), ditambah `home` (header Beranda).
- **Slot:** `headerAction`, `scripts`.
- **`<head>`:** meta viewport tanpa `user-scalable=no` dan dengan `viewport-fit=cover`, manifest, ikon branding, judul `"{title} · absenKU"`, script tema, dan `@vite`.
- **Header Beranda (`home`):**
  - avatar inisial atau foto (44px, `primary-soft`), tertaut ke Profil dengan `aria-label="Buka profil {nama}"`;
  - "Selamat pagi/siang/sore/malam," dan nama;
  - kanan: tombol **Ganti akun** (hanya bila ada akun tertaut; menu dan dialog konfirmasi `<dialog>`) dan tombol **Notifikasi** (ke daftar notifikasi, titik terakota bila ada yang belum dibaca, dengan `aria-label` "Notifikasi, N belum dibaca").
- **Header halaman lain:** tombol kembali 44px, `h1` judul, dan slot aksi.
- **Konten:** kolom tunggal, padding 24px 20px, jarak antarbagian 20px. Maksimal lebar 30rem di tengah pada layar lebar, tanpa bingkai HP palsu. Ada `padding-bottom` untuk nav + safe-area.
- **Nav bawah** (`nav[aria-label="Navigasi utama"]`):
  - Beranda · Riwayat · Izin · (Kelas bila `auth()->user()->activeHomeroomAssignment()`);
  - tab aktif memakai `aria-current="page"` dan pil `primary-soft` 60×32;
  - ikon 22px dengan label 12px;
  - `padding-bottom: max(22px, env(safe-area-inset-bottom))`.
  - Nilai `activeTab` baru: `beranda`, `riwayat`, `izin`, `kelas`. Nilai lama (`masuk`, `pulang`, `bk`, dll.) tetap diterima tanpa tab aktif.
- Service worker tetap didaftarkan lewat `partials/pwa-update`.

### 3.3 Dihapus

- `resources/views/partials/pwa-material3.blade.php`: skin override `!important`.
- Semua CSS inline di layout mobile lama: blob, grid, dynamic island, tema hijau lama, `animate-stagger`, dan `sheet-*`.
- Layout duplikat di `attendance/dashboard.blade.php` (1.033 baris). Beranda kini memakai `x-layouts.mobile`.
- Class lama (`glass-card`, `theme-*`, `font-outfit`, `font-display`, `interactive-card`, `solid-panel`) tidak dipakai lagi di halaman guru. Tidak ada lapisan kompatibilitas: semua halaman ditulis ulang.

## 4. Beranda

### 4.1 Data (service)

`App\Services\EmployeeDashboardService` dan `EmployeeDashboardData` mendapat field baru. Controller tetap tipis.

| Field | Definisi |
|---|---|
| `monthlyOnTime` | `monthlyPresent − monthlyLate` |
| `monthlyLeaveDays` | Jumlah hari (tanggal unik) izin milik user berstatus `approved` yang jatuh di bulan ini sampai hari ini |
| `monthlyWorkDays` | Jumlah tanggal dari tanggal 1 sampai hari ini (inklusif) yang harinya punya `WorkSchedule` aktif untuk user |
| `monthlyRecorded` | `min(monthlyPresent + monthlyLeaveDays, monthlyWorkDays)` |
| `pendingLeaves` | Jumlah izin milik user berstatus `pending` |
| `unreadNotifications` | `user->unreadNotifications()->where('type', 'like', '%StudentReferral%')->count()` |
| `workSetting` | `WorkSetting::current()`, dipakai untuk jendela absen masuk |

Keadaan kartu hero (§4.2) dihitung oleh presenter `App\Services\TodayPresence::for(EmployeeDashboardData, WorkSetting, Carbon $now)` sehingga bisa di-unit-test dan view tetap tipis. Controller meneruskannya sebagai `presence`.

Data yang sudah ada tetap dipakai: `todayAttendance`, `todaySchedule`, `checkoutOpensAt`, `checkoutTimeReached`, `announcements`, `linkedAccounts`, `homeroomAssignment`, `homeroomStudentCount`, `homeroomViolationCount`.

### 4.2 Kartu hero "Presensi hari ini"

Kartu berwarna `hero`, radius 24px, `section aria-label="Presensi hari ini"`.

- **Baris atas:** label "PRESENSI HARI INI" dan chip status, yaitu salah satu dari Tepat waktu / Terlambat / Belum absen / Absen ditutup / Libur.
- **Dua kolom:** Masuk dan Pulang dengan jam Fraunces 44px. Kolom yang belum terisi menampilkan `––.––` dengan warna `hero-dim`.
- **Bilah progres** dari jam masuk ke jam pulang jadwal, dengan isi `accent` dan knob pada posisi jam sekarang (dibatasi 0–100%). Label kiri dan kanan adalah jam jadwal. Label tengah berubah menurut keadaan:

  | Keadaan | Label tengah |
  |---|---|
  | Belum masuk, sebelum jendela dibuka | "Absen masuk dibuka HH.MM" |
  | Belum masuk, jendela terbuka | "Tepat waktu s.d. HH.MM", atau "Terlambat · tutup HH.MM" setelah batas tepat waktu |
  | Sudah masuk, sebelum jendela pulang | "Pulang dalam X j Y m" |
  | Jendela pulang terbuka | "Absen pulang sudah dibuka" |
  | Sudah pulang | "Selesai hari ini" |

  Label dihitung di server saat halaman dimuat. Beranda memuat ulang dirinya ketika PWA dibuka kembali setelah lebih dari 5 menit (`visibilitychange`), karena itu lebih cocok untuk PWA yang sering di-background daripada timer.
- **Baris lokasi:** setelah masuk, "Dalam area sekolah · N m dari titik absen" dari `distance_meters`, ditambah "· foto manual, menunggu pemeriksaan" bila `liveness_verified === false`. Sebelum masuk: "Lokasi dicek saat absen".
- **Tombol putih 56px** (`x-guru.button variant="hero"`):

  | Keadaan | Tombol |
  |---|---|
  | Belum masuk | "Absen Masuk" → `attendance.selfie` |
  | Sudah masuk, jendela pulang belum dibuka | nonaktif, "Pulang dibuka HH.MM" |
  | Jendela pulang terbuka | "Absen Pulang" → `attendance.checkout` |
  | Selesai | "Lihat riwayat" → `attendance.index` |
  | Tanpa jadwal | tombol tidak tampil; tampil teks "Tidak ada jadwal kerja hari ini" |

- Banner status lama yang hilang sendiri setelah 10 detik **dihapus**, karena status kini selalu terlihat di kartu hero.

### 4.3 Susunan halaman (urut dari atas)

1. **Header** (§3.2).
2. **Baris tanggal:** tanggal Fraunces 26px ("Senin, 28 September", locale `id`) dan nama kantor di kanan.
3. **Kartu hero** (§4.2).
4. **Kartu "Rekap {Bulan}":**
   - tautan "Lihat riwayat";
   - tiga angka: Tepat waktu (primary), Terlambat (late), Izin (izin);
   - bilah bertumpuk 10px dengan segmen tepat waktu, terlambat, izin, dan sisa (`monthlyWorkDays − monthlyRecorded`, warna `empty-bar`);
   - keterangan "{monthlyRecorded} dari {monthlyWorkDays} hari kerja tercatat". Bila `monthlyWorkDays = 0`: "Belum ada hari kerja bulan ini".
5. **Kartu kelas wali** (hanya bila ada): "KELAS WALI · {tahun ajaran}", nama kelas Fraunces 26px, chip "{n} siswa aktif", dan chip attn "{m} perlu perhatian" bila m > 0. Seluruh kartu tertaut ke Kelas.
6. **Layanan** (`list`), urutannya:
   - Persetujuan Izin (kepala sekolah);
   - Pengajuan Izin (chip pending "{n} menunggu" bila > 0);
   - Rujukan Saya (wali kelas);
   - Bimbingan Konseling + Antrean Rujukan (guru BK);
   - Kesiswaan (petugas kesiswaan);
   - Profil & Kata Sandi.

   Label mengikuti desain: "Bimbingan Konseling" (bukan "BK"). "Rujukan Saya", "Antrean Rujukan", dan "Kesiswaan" tetap sama persis, dengan node teks label berisi hanya teks itu. `BkAccessTest` diubah: alih-alih `>BK<`, test mengecek ada/tidaknya tautan `route('attendance.bk.index')` di Beranda.
7. **Informasi:** carousel horizontal (scroll-snap, kartu 280px) berisi pengumuman aktif. Bagian atas kartu setinggi 96px menampilkan gambar pengumuman bila ada, atau blok warna netral dengan chip "Pengumuman". Di bawahnya judul, ringkasan, dan waktu relatif (`diffForHumans`, locale `id`). Seluruh bagian disembunyikan bila tidak ada pengumuman.
8. **Banner pasang PWA** (logika `beforeinstallprompt` yang sudah ada), bergaya kartu dan duduk di atas nav.

## 5. Halaman lain

Semua halaman memakai `x-layouts.mobile` dan komponen `x-guru.*`. **Data, form, route, validasi, otorisasi, dan JavaScript fungsional tidak berubah**; yang diganti hanya markup dan gaya.

| Halaman | Berkas | Susunan |
|---|---|---|
| Absen Masuk | `attendance/selfie.blade.php` | Header kembali "Absen Masuk"; kartu kamera gelap 3:4 radius 24 (bingkai sudut, garis pindai `transform`, elips panduan, prompt "Kedipkan mata"); status Wajah / Lokasi / Kantor sebagai chip berikon dan berteks; kartu kantor (terkunci / pilihan); kartu lokasi (koordinat, akurasi, jarak vs radius, tombol perbarui 44px); `notice` di luar radius; tombol utama hijau 56px "Kirim Absen Masuk"; layar "sudah absen" berupa kartu hero ringkas |
| Absen Pulang | `attendance/checkout.blade.php` | Sama, dengan aksen terakota dan tombol "Kirim Absen Pulang" |
| Riwayat | `attendance/index.blade.php` | Total di header; dikelompokkan per bulan (judul `section`); tiap baris berisi tanggal Fraunces + hari, jam masuk/pulang, chip status, jarak, dan tautan foto; paginasi |
| Izin (daftar) | `attendance/leaves/index.blade.php` | Tombol "Ajukan Izin"; baris dengan jenis, rentang tanggal, dan chip status (pending / ok / attn). Tanpa filter status: `LeaveController@index` tidak menyediakannya (filter hanya di halaman persetujuan) |
| Izin (form) | `attendance/leaves/create.blade.php` | Pilihan Izin / Cuti / Sakit sebagai kartu radio besar; tanggal mulai/selesai; alasan; lampiran foto (keyboard-able, pratinjau, hapus 44px); tombol kirim dengan anti-dobel |
| Izin (detail) | `attendance/leaves/show.blade.php` | Kartu ringkasan status, rincian, dan lampiran |
| Persetujuan | `attendance/leaves/approval-index.blade.php`, `approval-show.blade.php` | Daftar pengajuan dan detail dengan aksi setuju/tolak (konfirmasi tetap) |
| Profil | `attendance/profile.blade.php` | Foto (unggah), nama, email; daftar ke Ganti Password; **Tema** (Terang / Gelap / Ikut sistem, segmented, menulis `appearance`); tombol **Keluar** |
| Password | `attendance/password.blade.php` | Tiga field password dengan tombol tampil/sembunyi |
| Informasi | `attendance/information/show.blade.php` | Gambar, judul Fraunces, dan isi |
| Kelas (wali) | `attendance/my-class/index.blade.php`, `show.blade.php`, `violation.blade.php` | Kartu ringkasan kelas; daftar siswa (`data-my-class-list`) dengan chip pelanggaran; detail siswa |
| BK | `attendance/bk/index.blade.php`, `create.blade.php`, `form.blade.php`, `show.blade.php` (+ partial combobox) | Daftar catatan, form (combobox siswa tetap dengan atribut ARIA yang sama), detail + tindak lanjut |
| Kesiswaan / Rujukan | `attendance/kesiswaan/index.blade.php`, `show.blade.php`, `my-referrals.blade.php`, `referral-queue.blade.php` | Direktori siswa, profil siswa, daftar rujukan. Atribut `data-kesiswaan-*` yang dicek test dipertahankan |
| Kesiswaan (masih `x-layouts.app`) | `kesiswaan/notifications.blade.php`, `referral-form.blade.php`, `referral.blade.php` | Tetap di layout admin; di luar cakupan (lihat §8) |
| Offline | `public/offline.html` | Palet dan huruf baru (tanpa dependensi) |

`attendance/create.blade.php` adalah kamera lama berbasis `x-layouts.app`, tidak dipakai route mana pun (`attendance.create` hanya redirect), dan masih dicek oleh test kompresi. Berkas ini tidak diubah.

## 6. Perilaku yang wajib tetap

- Deteksi kedip otomatis dan fallback "Ambil Foto Manual", termasuk penandaan `liveness_verified`.
- Penanganan error submit: hanya JSON `success` yang dianggap berhasil; ada pesan untuk sesi habis, 429, dan error server.
- Kompresi selfie 1024px, anti-double-submit, live region status, preview yang di-mirror, dan akurasi GPS.
- Kantor terkunci oleh admin ("Terkunci oleh admin"), geofence, dan jendela waktu.
- Ganti akun: konfirmasi, lalu POST `account.switch`.
- Service worker, halaman offline, dan banner pasang PWA.
- Tema: key `appearance` bersama admin dan auth.
- Semua route, nama route, dan otorisasi per peran.

## 7. Pengujian dan verifikasi

### 7.1 Test

**Tetap hijau tanpa perubahan logika:** `LivenessFallbackTest`, `LeaveFlowTest`, `LockedOfficeTest`, `AccountSwitchTest`, `StudentAffairsAccessTest`, `StudentReferralTest`, `MyClassTest`, `AnnouncementTest`, `ProfileAvatarTest`, dan `PwaStartUrlTest`.

Penyesuaian teks atau markup hanya dilakukan bila test tersebut mengecek markup desain lama, dan setiap perubahan disebutkan di commit.

**Diganti karena mengunci desain lama:**

| Test | Perubahan |
|---|---|
| `TeacherAttendanceVisualSystemTest` | `data-attendance-ui="material-3"`, `data-m3-region`, dan `sheet-*` diganti dengan pengecekan shell baru: `data-teacher-ui="absenku-guru"`, `nav[aria-label="Navigasi utama"]`, `aria-current`, tidak ada `user-scalable=no`/`maximum-scale`, `prefers-reduced-motion`, `@view-transition` |
| `CheckoutTimeWindowTest` | assertion `nav-fab` diganti dengan pengecekan tombol hero kontekstual |
| `StatusBannerAutoHideTest` | banner dihapus; diganti test chip status hero |
| `LeaveCreatePageTest` | `peer-checked:opacity-100` diganti dengan penanda pilihan baru |
| `BkAccessTest` | `>BK<` diganti dengan pengecekan tautan `attendance.bk.index` (label baru "Bimbingan Konseling"); pengecekan combobox tetap |

**Test baru:**
- Rekap: izin `approved` dihitung per hari dalam bulan berjalan (izin yang melewati awal bulan terpotong); izin `pending` dan `rejected` tidak dihitung.
- `monthlyWorkDays` hanya menghitung hari dengan jadwal aktif sampai hari ini.
- Chip "{n} menunggu" dan titik notifikasi mengikuti data.
- Tab Kelas tampil hanya untuk wali kelas.
- Tombol hero benar pada kondisi: belum masuk, menunggu jendela pulang, jendela pulang terbuka, selesai, dan tanpa jadwal.
- Profil menampilkan pengaturan tema dan tombol Keluar.

### 7.2 Verifikasi

- `php artisan test` hijau, `./vendor/bin/pint --dirty` bersih, PHPStan tanpa error baru (baseline 132), dan `npm run build` sukses.
- **Satu ronde cek visual** di 390×844, tema terang dan gelap: Beranda, Absen Masuk (headless Chrome dengan kamera palsu `--use-fake-device-for-media-stream`), Riwayat, Izin (daftar dan form), Profil, dan Kelas.
  - Beranda terang dibandingkan berdampingan dengan file desain pada lebar yang sama.
  - Cacat diperbaiki dalam satu batch, lalu dikonfirmasi satu ronde lagi.
- Screenshot disimpan di `docs/screenshots/redesign-guru/`.

## 8. Di luar cakupan

- Panel admin dan halaman auth/welcome.
- Tiga halaman kesiswaan yang masih memakai `x-layouts.app` (`notifications`, `referral-form`, `referral`).
- App native iOS/Android.
- Fitur baru di luar yang dibutuhkan desain Beranda: tidak ada notifikasi push dan tidak ada pengaturan baru selain tema di Profil.

## 9. Risiko

| Risiko | Mitigasi |
|---|---|
| Guru terbiasa dengan tombol Masuk/Pulang di nav | Tombol hero selalu di layar pertama Beranda; route lama tetap bisa dibuka |
| Fraunces dan Jakarta menambah ~94 KB font | Dimuat sekali dan di-cache service worker (cache-first `/build/`) |
| Perhitungan hari kerja berbeda dari ekspektasi sekolah | Definisi eksplisit di §4.1 dan dicakup test; mudah diubah di satu service |
| Halaman modul (BK/kesiswaan) berisi markup padat satu baris | Ditulis ulang per berkas dengan komponen; atribut yang dicek test dipertahankan |
