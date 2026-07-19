# Sync Kelola User dari API Data Induk

## Ringkasan

Tambah kemampuan menarik (sync) data pegawai dari API "data induk"
(`https://datainduk.ypdhalmadani.sch.id`) ke tabel `users` pada modul Kelola
User. Admin menekan satu tombol; sistem menarik seluruh pegawai unit MI & SMP,
lalu membuat user baru atau memperbarui yang sudah ada — tanpa duplikat.

Mekanisme mengacu pada implementasi `syncFromApi` di proyek referензi
`Documents/Coding/Antigravity/e-raport` (GuruController), disesuaikan dengan
skema `users` proyek absensi ini (role_id FK, office_id FK, tanpa kolom
nip/nik).

## Tujuan

- Admin dapat mengisi/menyegarkan daftar user dari sumber data induk dengan satu aksi.
- Re-sync bersifat idempotent: mencocokkan record API ke user lewat NIP, sehingga memperbarui, bukan menggandakan.
- Tidak menimpa penyesuaian manual admin (role, kantor, password, email) pada user yang sudah ada.

## Non-tujuan

- Tidak menyinkronkan siswa (bukan user aplikasi ini).
- Tidak menyimpan field data induk yang tak dipakai absensi (gender, tempat/tanggal lahir, is_active).
- Tidak membuat penjadwalan sync otomatis (cron) — hanya manual via tombol. Fase lanjut bila perlu.
- Tidak memaksa ganti password saat login pertama (fase lanjut; dicatat sebagai risiko).

## Keputusan desain (disetujui)

1. **Kunci identitas:** tambah kolom `nip` + `nik` (nullable, unique) ke `users`, cocokkan by `nip`.
2. **Sumber:** `guru-mi` + `guru-smp` ditarik bersama dalam satu aksi sync.
3. **Role & kantor user baru:** role `Guru` (slug `guru`), `office_id` null. Admin menetapkan kantor/role spesifik lewat Edit.
4. **Password user baru:** = NIP (di-hash). Predictable — dicatat sebagai risiko, mitigasi (wajib ganti) di fase lanjut.

## Arsitektur

Controller tipis; logika bisnis di service (mengikuti standar kode §4).

### Berkas dibuat

- `app/Services/UserSyncService.php` — orkestrasi sync: loop sumber & paginasi, mapping, match, create/update, akumulasi hitungan.
- `database/migrations/XXXX_add_nip_nik_to_users_table.php` — kolom `nip`, `nik` (string, nullable, unique) setelah `email`.
- `tests/Feature/Admin/UserSyncTest.php` — feature test dengan `Http::fake()`.

### Berkas diubah

- `app/Models/User.php` — tambah `nip`, `nik` ke `$fillable`.
- `app/Http/Controllers/Admin/UserController.php` — method `syncFromApi(): RedirectResponse`.
- `routes/web.php` — `POST admin/users/sync` bernama `admin.users.sync`, di dalam grup `Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')` (web.php:70), didaftarkan sebelum `Route::resource('users', ...)`.
- `config/services.php` — blok `data_induk` dengan `base_url` dari `SYNC_API_BASE_URL`.
- `.env.example` — `SYNC_API_BASE_URL=https://datainduk.ypdhalmadani.sch.id`.
- `resources/views/admin/users/index.blade.php` — tombol "Sync Data Induk" (form POST + `onsubmit confirm`).

## UserSyncService — kontrak

```php
public function sync(): SyncResult // DTO/array: created, updated, failed, errors[]
```

Perilaku:

- Sumber tetap: `['guru-mi', 'guru-smp']`.
- Untuk tiap sumber, loop paginasi: `Http::timeout(60)->get("{base}/api/{source}/all", ['page' => $page])`.
  - `base` dari `config('services.data_induk.base_url')`.
  - Response non-2xx → lempar `RuntimeException` berisi status (controller ubah jadi flash error).
  - Ambil daftar dari `$data['data'] ?? $data`; jika bukan array → error "Format response API tidak valid."
  - Paginasi berhenti bila tak ada `next_page_url` dan `current_page >= last_page`; guard maksimum 1000 halaman.
- Mapping per record:
  - `nama` = `full_name` ?? `nama` ?? null; jika kosong → failed++, lanjut.
  - `nip` = `nik` (identitas); `nik` = `nik`. Jika `nip` kosong → failed++, lanjut.
  - `email` = `Str::slug($nip, '.').'@guru.local'`.
- Match: `User::where('nip', $nip)->first()`.
  - **Ada** → `update(['name' => $nama, 'nik' => $nik])`. Role/office/email/password tak disentuh.
  - **Tidak ada** → `create([...])` dengan role guru, office null, password `Hash::make($nip)`.
- Role guru diambil sekali di awal: `Role::where('slug', 'guru')->first()`. Jika tak ada → lempar error jelas ("Role 'guru' belum ada, seed dulu.").
- Setiap error per-record ditangkap (try/catch), failed++, pesan disimpan di `errors[]`. Termasuk pelanggaran unique email (nip beda tapi email sama).
- Kembalikan hitungan.

## UserController::syncFromApi

- Otorisasi: dilindungi middleware `['auth', 'admin']` dari grup route admin (web.php:70). Cukup; tak perlu policy tambahan.
- Panggil `UserSyncService::sync()`.
- Sukses → `back()->with('success', "Sync selesai: {created} user baru, {updated} diperbarui" . (failed ? ", {failed} gagal." : "."))`.
- `ConnectionException` → `back()->with('error', 'Tidak dapat terhubung ke API data induk.')`.
- `RuntimeException`/lainnya → `back()->with('error', $e->getMessage())`, dan `Log::error`.

## Alur data

```
Admin klik "Sync Data Induk"
  → POST admin.users.sync
    → UserController::syncFromApi
      → UserSyncService::sync
        foreach source in [guru-mi, guru-smp]:
          page = 1
          loop:
            GET {base}/api/{source}/all?page=page
            foreach record: map → match by nip → update|create
            page++ sampai habis
        return {created, updated, failed}
      → flash success/error
    → redirect back (users index menampilkan pesan)
```

## UI

Di header [admin/users/index.blade.php], sebelah tombol "Tambah User":

```blade
<form action="{{ route('admin.users.sync') }}" method="POST"
      onsubmit="return confirm('Tarik data pegawai dari API data induk? Ini membuat/memperbarui user.')">
    @csrf
    <button type="submit" class="admin-button-secondary inline-flex items-center gap-2 px-4 py-2 text-sm">
        {{-- ikon refresh --}}
        Sync Data Induk
    </button>
</form>
```

Pesan hasil memakai alert glass yang sudah ada (`admin-alert-success` / `admin-alert-danger`) — halaman index sudah merender `session('success')`/`session('error')`.

## Keamanan (§5)

- `$fillable` eksplisit — tambahkan `nip`, `nik`; tidak memakai `User::create($request->all())`.
- Route hanya untuk admin (grup middleware admin yang sudah ada).
- Password user baru di-hash (`Hash::make`). Risiko predictable (= NIP) dicatat; mitigasi wajib-ganti ditunda ke fase lanjut.
- Tidak ada rahasia di-hardcode; base URL via `.env`/config.
- API data induk publik (tanpa token pada referensi). Jika kelak butuh token, tambah header via config — di luar scope sekarang.

## Testing (§8)

`tests/Feature/Admin/UserSyncTest.php` dengan `Http::fake()`:

1. **Happy path:** fake 1–2 halaman guru-mi & guru-smp → assert user tercreate dengan `nip`, role guru, `office_id` null, password terverifikasi = NIP.
2. **Idempotent:** jalankan sync dua kali → jumlah user tak bertambah di run kedua; nama ter-update bila berubah.
3. **Tak menimpa manual:** user existing yang office/role-nya diubah admin → setelah sync, office/role tetap; hanya name/nik ter-update.
4. **Otorisasi:** user non-admin POST sync → ditolak (403/redirect).
5. **Error koneksi:** `Http::fake` lempar `ConnectionException` → redirect back dengan flash error, tak ada user dibuat.

Jalankan `php artisan test`, `./vendor/bin/pint`, `./vendor/bin/phpstan analyse` sebelum menyatakan selesai.

## Risiko & catatan

- **Password predictable (= NIP):** internal, tapi lemah. Fase lanjut: paksa ganti saat login pertama.
- **Email `@guru.local`:** domain internal non-routable (sesuai referensi). Login pakai email ini; admin mengelola. Bila pegawai butuh email asli, ubah manual lewat Edit (sync tak menimpa email existing).
- **Dedupe lintas unit:** NIK sama di guru-mi & guru-smp → jadi satu user (match by nip). Sesuai harapan.
- **Sinkron blocking:** sync jalan sinkron dalam satu request; dataset besar bisa lama. Timeout HTTP 60s/halaman. Bila dataset sangat besar, pertimbangkan queue — di luar scope sekarang.
