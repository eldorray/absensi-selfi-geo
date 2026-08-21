# Desain Fitur Kesiswaan

## Tujuan

Menyediakan pusat profil siswa yang menghubungkan administrator, wali kelas, dan Guru BK tanpa menggandakan pengelolaan data siswa atau membuka catatan profesional BK kepada pihak yang tidak berwenang.

Versi pertama tidak mencakup absensi siswa dan prestasi karena modul tersebut belum tersedia.

## Prinsip Utama

- Kesiswaan hanya membaca data siswa. Perubahan tetap dilakukan administrator melalui Data Siswa.
- Data siswa, kelas, tahun ajaran, penugasan wali kelas, dan Catatan BK yang ada digunakan sebagai sumber kebenaran.
- Akses dibatasi oleh peran, penugasan wali kelas aktif, kepemilikan rujukan, dan jenjang kantor Guru BK.
- Rujukan wali kelas dipisahkan dari Catatan BK profesional.
- Ringkasan untuk wali kelas tidak boleh mengungkap isi konseling atau catatan profesional.
- Lampiran bersifat privat dan tidak disajikan melalui URL penyimpanan publik.
- Rujukan dipertahankan untuk audit dan tidak memiliki hard-delete.

## Pengguna dan Cakupan Akses

### Administrator

- Melihat semua siswa MI dan SMP pada panel admin.
- Melihat profil siswa dan seluruh rujukan untuk pengawasan.
- Tidak mengubah data siswa dari Kesiswaan.
- Tidak mengubah isi rujukan yang dibuat wali kelas atau catatan profesional BK.

### Wali kelas

- Melihat menu Kesiswaan hanya jika memiliki penugasan pada tahun ajaran aktif.
- Melihat siswa yang berada dalam kelas aktif yang ditugaskan kepadanya.
- Membuat rujukan hanya untuk siswa dalam kelas tersebut.
- Membaca rujukan yang dibuatnya beserta status dan ringkasan aman dari Guru BK.
- Melihat ringkasan BK terbatas, bukan detail profesional.

### Guru BK

- Melihat menu Kesiswaan jika `is_bk_counselor` aktif dan `office.school_level` terpetakan.
- Melihat siswa sesuai jenjang kantornya.
- Melihat antrean rujukan baru sesuai jenjang.
- Setelah rujukan diambil, hanya Guru BK penanggung jawab dan administrator yang dapat membuka isinya.
- Dapat membuat satu Catatan BK profesional dari satu rujukan yang telah diambil.

### Guru biasa

- Tidak melihat menu Kesiswaan.
- Tidak dapat mengakses route Kesiswaan atau rujukan.

Akses gagal tertutup apabila penugasan wali kelas aktif atau pemetaan jenjang Guru BK tidak tersedia.

## Navigasi

### Panel admin

Sidebar memiliki menu Kesiswaan menuju daftar siswa lintas jenjang. Daftar menyediakan pencarian nama, NISN, NIK, dan kelas serta filter jenjang dan kelas.

### PWA guru

Menu utama menampilkan Kesiswaan bagi wali kelas aktif dan Guru BK yang memenuhi syarat. Badge notifikasi belum dibaca ditampilkan ketika relevan.

## Pusat Profil Siswa

Daftar dan profil bersifat mobile-friendly dan hanya-baca. Profil menampilkan data yang tersedia:

- Nama lengkap.
- NISN dan NIK.
- Jenjang dan kelas aktif.
- Wali kelas aktif.
- Tahun ajaran aktif.
- Kontak dan alamat.
- Ringkasan BK sesuai kewenangan.
- Daftar rujukan yang boleh dibaca pengguna.

Administrator tetap menggunakan menu Data Siswa untuk memperbarui identitas atau kelas.

## Ringkasan BK untuk Wali Kelas

Wali kelas hanya dapat melihat:

- Jumlah catatan aktif.
- Jenis umum: Pelanggaran atau Konseling.
- Status penanganan.
- Indikator perlu tindak lanjut.

Wali kelas tidak dapat melihat kronologi, isi konseling, catatan profesional, tindak lanjut internal, komunikasi wali, atau lampiran BK. Administrator dan Guru BK tetap tunduk pada policy Catatan BK yang sudah ada.

## Rujukan Wali Kelas

Wali kelas membuat rujukan dari profil siswa. Data rujukan terdiri atas:

- Alasan rujukan.
- Ringkasan pengamatan.
- Tanggal pengamatan.
- Urgensi: Normal, Penting, atau Mendesak.
- Maksimal tiga lampiran JPG, PNG, atau PDF.
- Maksimal 5 MB per lampiran.

Seluruh lampiran divalidasi sebelum rujukan disimpan. File disimpan secara privat dan hanya dialirkan melalui controller yang mengotorisasi pengguna.

## Antrean Guru BK

Rujukan MI hanya masuk antrean Guru BK MI dan rujukan SMP hanya masuk antrean Guru BK SMP. Semua Guru BK yang memenuhi syarat pada jenjang tersebut dapat membaca rujukan berstatus Baru.

Urutan antrean:

1. Mendesak.
2. Penting.
3. Normal.
4. Untuk urgensi yang sama, rujukan paling lama lebih dahulu.

Salah satu Guru BK mengambil rujukan sebagai penanggung jawab. Operasi pengambilan menggunakan transaksi dan conditional update atau row lock agar dua Guru BK tidak dapat mengambil rujukan yang sama. Pengambilalihan tidak tersedia bagi Guru BK lain; pelepasan atau koreksi penanggung jawab hanya melalui tindakan administratif yang diaudit jika kelak dibutuhkan.

## Status dan Riwayat

Status rujukan:

- `new` — Baru, belum memiliki penanggung jawab.
- `in_handling` — Ditangani oleh Guru BK.
- `completed` — Selesai.
- `rejected` — Ditolak.

Setiap transisi mencatat status lama, status baru, pelaku, waktu, dan ringkasan aman bila diwajibkan. Transisi yang diizinkan pada versi pertama:

- Baru → Ditangani.
- Baru → Ditolak.
- Ditangani → Selesai.
- Ditangani → Ditolak.

Tidak ada hard-delete. Isi awal rujukan tidak dapat diubah setelah dikirim agar jejak audit terjaga.

## Ringkasan Aman

Saat menolak atau menyelesaikan rujukan, Guru BK wajib memberikan ringkasan aman kepada wali kelas. Ringkasan dapat berisi hasil umum, arahan tindak lanjut untuk wali kelas, atau alasan penolakan.

Ringkasan tidak boleh memuat kronologi konseling, diagnosis atau dugaan diagnosis, pengakuan siswa, catatan profesional, lampiran BK, maupun komunikasi internal Guru BK. Wali kelas tidak dapat mengubah ringkasan tersebut.

## Hubungan dengan Catatan BK

Setelah mengambil rujukan, Guru BK dapat memilih “Buat Catatan BK”. Sistem mengisi siswa dan referensi rujukan secara otomatis, sedangkan isi profesional ditulis dalam formulir Catatan BK yang terpisah.

- Satu rujukan maksimal terhubung ke satu Catatan BK.
- Rujukan boleh ditolak atau diselesaikan tanpa Catatan BK.
- Pembuatan Catatan BK tidak otomatis menyelesaikan rujukan.
- Wali kelas tidak memperoleh akses ke Catatan BK yang terhubung.

## Notifikasi Internal

Gunakan mekanisme notifikasi database Laravel.

Guru BK sesuai jenjang menerima notifikasi ketika rujukan baru dibuat. Wali kelas pembuat menerima notifikasi ketika rujukan diambil, ditolak, atau diselesaikan.

Notifikasi memuat informasi minimum: nama siswa, status, dan tautan detail. Route tujuan tetap menjalankan authorization; notifikasi tidak memberikan hak akses baru. Versi pertama tidak mengirim Telegram, WhatsApp, email, atau push notification.

## Model Data yang Diusulkan

### `student_referrals`

Menyimpan siswa, wali kelas pembuat, jenjang saat dibuat, Guru BK penanggung jawab opsional, alasan, pengamatan, tanggal pengamatan, urgensi, status, ringkasan aman, waktu diambil/diselesaikan/ditolak, dan referensi Catatan BK opsional. Nilai jenjang disalin saat pembuatan untuk menjaga cakupan historis meskipun kelas siswa berubah.

### `student_referral_attachments`

Menyimpan metadata file privat: rujukan, path, nama asli, MIME, ukuran, dan pengunggah.

### `student_referral_status_histories`

Menyimpan setiap transisi status, pelaku, waktu, serta ringkasan aman yang terkait.

Relasi ke `bk_records` dibuat satu-ke-satu secara nullable dengan unique constraint agar satu rujukan tidak dapat menghasilkan lebih dari satu Catatan BK.

## Authorization

Policy dan query scope menjadi pengaman utama; penyembunyian menu hanya presentasi.

- Administrator dapat melihat semua profil dan rujukan, tetapi tidak mengubah isi asli.
- Wali kelas hanya dapat melihat siswa pada penugasan aktif dan rujukan yang dibuatnya.
- Guru BK melihat siswa dan rujukan Baru sesuai jenjang; rujukan non-Baru hanya dapat dibaca penanggung jawabnya.
- Download lampiran memanggil policy yang sama dengan detail rujukan.
- Query daftar selalu dibatasi di server dan tidak mengandalkan filter browser.
- Record di luar cakupan dikembalikan sebagai 404 bila keberadaannya perlu disembunyikan; pengguna tanpa capability menerima 403.

## Validasi dan Penanganan Kesalahan

- Semua pesan validasi menggunakan Bahasa Indonesia.
- Siswa harus masih berada dalam kelas aktif wali kelas pada saat pengiriman.
- Tanggal pengamatan tidak boleh melebihi hari ini.
- Urgensi harus salah satu nilai yang didukung.
- Seluruh lampiran harus lolos batas jumlah, MIME, dan ukuran sebelum transaksi penyimpanan dimulai.
- Kegagalan penyimpanan file membersihkan file yang sempat dibuat dan membatalkan record database.
- Konflik pengambilan rujukan menghasilkan pesan bahwa rujukan telah diambil Guru BK lain.

## Pengujian Penerimaan

Minimal mencakup:

- Guru biasa ditolak dan tidak melihat menu.
- Wali kelas aktif hanya melihat siswa kelasnya; wali kelas tanpa penugasan ditolak.
- Guru BK hanya melihat siswa dan antrean pada jenjang kantor; pemetaan kosong ditolak.
- Administrator melihat seluruh siswa dan rujukan dalam mode hanya-baca.
- Wali kelas melihat ringkasan BK tanpa kronologi, catatan profesional, komunikasi wali, atau lampiran BK.
- Wali kelas tidak dapat merujuk siswa di luar kelasnya.
- Validasi urgensi, tanggal, jumlah lampiran, MIME, dan ukuran menggunakan Bahasa Indonesia.
- Lampiran hanya dapat diunduh pihak yang berwenang.
- Antrean diurutkan menurut urgensi lalu umur rujukan.
- Dua Guru BK tidak dapat mengambil rujukan yang sama.
- Transisi status ilegal ditolak.
- Penolakan dan penyelesaian mewajibkan ringkasan aman.
- Notifikasi dibuat kepada penerima yang benar dan tautannya tetap terlindungi.
- Satu rujukan hanya dapat terhubung ke satu Catatan BK.
- Wali kelas tetap tidak dapat membaca Catatan BK yang terhubung.
- Tidak tersedia route hard-delete.

## Di Luar Cakupan Versi Pertama

- Absensi siswa.
- Prestasi siswa.
- Perubahan data siswa dari Kesiswaan.
- Telegram, WhatsApp, email, atau web push.
- Guru BK utama per jenjang.
- Pemilihan Guru BK oleh wali kelas.
- Pengambilalihan rujukan oleh Guru BK lain.
- Hard-delete rujukan atau riwayat.
- API Android untuk Kesiswaan dan rujukan, kecuali diminta terpisah.
