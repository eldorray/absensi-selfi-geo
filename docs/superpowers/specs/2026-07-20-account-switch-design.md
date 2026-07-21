# Ganti Akun Cepat (Fast Account Switch) — Design

**Tanggal:** 2026-07-20
**Status:** Disetujui (menunggu review spec sebelum plan)

## Masalah

Seorang guru bisa mengajar di lebih dari satu unit (mis. MI 07:00–12:45 dan
SMP 13:00–17:00). Model data saat ini adalah **1 akun = 1 kantor = 1
shift/hari**, jadi guru butuh dua akun terpisah (MI & SMP). Berpindah antar
akun sekarang berarti logout lalu login ulang dengan password — merepotkan
saat harus absen pulang di MI lalu absen masuk di SMP.

Tujuan: guru bisa **berpindah akun dengan cepat tanpa mengetik password**,
sambil menjaga keamanan (jangan sampai akun bisa diambil-alih orang lain).

Opsi arsitektur yang dipilih: **tetap dua akun terpisah + tombol ganti akun
cepat** (Opsi C). Bukan menyatukan jadi satu akun multi-shift.

## Prinsip Keamanan (non-negotiable, CLAUDE.md §5)

- Switch **password-less** hanya boleh antar akun yang **sudah ditaut admin**.
- **Tidak boleh** switch ke akun **admin** (cegah privilege escalation).
- Verifikasi otoritatif di **server**, bukan hanya sembunyikan tombol di UI:
  target wajib ada di daftar akun-tertaut milik user yang sedang login.
- Endpoint switch **di-rate-limit**.
- Setiap switch **dicatat** (audit) dan bisa dilihat admin.

## Arsitektur

### 1. Model data — tautan akun

Tabel pivot self-relasi:

```
account_links
  - id
  - user_id           (FK users, cascade on delete)
  - linked_user_id    (FK users, cascade on delete)
  - timestamps
  - UNIQUE (user_id, linked_user_id)
```

Tautan disimpan **dua arah** (A→B dan B→A) agar simetris — kedua akun melihat
satu sama lain. Relasi di `User`:

```php
public function linkedAccounts(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'account_links', 'user_id', 'linked_user_id');
}
```

Alasan pivot (bukan satu kolom `linked_account_id`): mendukung >2 akun (guru 3
unit) tanpa perubahan skema, dan menghindari menjaga dua FK 1-1 tetap sinkron.

### 2. Penautan oleh admin

Di halaman **edit user** (`admin.users.edit`), tambah field multi-select
**"Akun Terkait"** berisi daftar user **non-admin lain**. Saat disimpan
(`UserController@update`), sinkronkan pivot **dua arah**:

- Untuk setiap target terpilih: pastikan baris (user→target) dan (target→user)
  ada.
- Hapus tautan yang tidak lagi dipilih (dua arah).

Validasi: setiap id di "akun terkait" harus `exists:users,id`, bukan diri
sendiri, dan **bukan** akun admin.

### 3. Alur switch + keamanan

**UI (sisi guru):** komponen "Ganti Akun" di dashboard/menu employee,
menampilkan daftar `linkedAccounts` (nama + kantor/unit). Setiap item adalah
form yang, saat ditekan, memunculkan **dialog konfirmasi** ("Ganti ke akun
<nama>?") sebelum submit. Catatan: komponen `admin-confirm` yang sudah ada
hanya dipasang di route admin, jadi sisi employee memakai konfirmasi
tersendiri — Alpine kecil `x-data` inline (pola `@submit.prevent` + tombol
Ya/Batal), bukan `confirm()` native, agar konsisten dengan gaya UI.

**Route:** `POST /account/switch` (bukan area admin), nama
`account.switch`, di grup `auth`, dengan `throttle` (mis. `throttle:10,1`).

**Controller `AccountSwitchController@store(Request)`:**
1. `$current = Auth::user()`.
2. Validasi `target_id` `required|exists:users,id`.
3. Ambil `$target = User::findOrFail(target_id)`.
4. Tolak (403) jika salah satu benar:
   - `$current->isAdmin()` atau `$target->isAdmin()`.
   - `$target` tidak ada di `$current->linkedAccounts` (cek relasi, bukan input).
   - `$target->id === $current->id`.
5. Catat audit (lihat §4).
6. `Auth::login($target)` lalu `$request->session()->regenerate()`.
7. Redirect ke `attendance.dashboard` dengan flash sukses
   "Berpindah ke akun <nama>.".

**Model sesi:** *replace* — sesi lama diganti jadi akun target (penggunaan
berurutan: kerja MI dulu, lalu SMP). Bukan multi-sesi bersamaan.

**Interaksi dengan kunci kantor:** tiap akun punya `office_id` sendiri, jadi
fitur kunci kantor absensi (yang sudah ada) otomatis mengikuti akun aktif.

### 4. Audit — tabel yang bisa dilihat admin

```
account_switch_logs
  - id
  - from_user_id   (FK users, nullOnDelete)
  - to_user_id     (FK users, nullOnDelete)
  - ip_address     (string, nullable)
  - created_at     (timestamp; updated_at tidak dipakai)
```

Model `AccountSwitchLog` dengan relasi `fromUser()` / `toUser()`. Dicatat di
dalam `AccountSwitchController@store` **sebelum** `Auth::login` (agar
`from_user_id` = user asal).

**Halaman admin (read-only):** route `admin.account-switches.index`
(`GET admin/account-switches`), controller
`Admin\AccountSwitchLogController@index`, view daftar terpaginasi: waktu, dari
akun, ke akun, IP. Tambah tautan di sidebar admin. Tidak ada create/edit/delete
dari UI.

## Komponen yang disentuh

- **Migrations:** `create_account_links_table`, `create_account_switch_logs_table`.
- **Models:** `User` (+ `linkedAccounts`), `AccountSwitchLog` (baru).
- **Controllers:** `AccountSwitchController` (baru, employee),
  `Admin\AccountSwitchLogController` (baru), `Admin\UserController@update/edit`
  (tambah sinkron tautan + data untuk field).
- **Routes:** `account.switch` (grup auth + throttle),
  `admin.account-switches.index` (grup admin).
- **Views:** komponen "Ganti Akun" di dashboard employee, field "Akun Terkait"
  di `admin/users/edit`, `admin/account-switches/index` (baru), tautan sidebar.

## Error handling

- Switch tidak sah (tak tertaut / ke admin / diri sendiri) → **403**.
- `target_id` tak valid → error validasi.
- Guru tanpa akun tertaut → komponen "Ganti Akun" tidak tampil.

## Testing (Pest)

1. Admin bisa menaut dua akun non-admin; pivot tersimpan dua arah.
2. Menyimpan tautan yang di-uncheck akan melepasnya (dua arah).
3. Admin tidak bisa menaut ke akun admin (validasi tolak).
4. Guru yang login bisa switch ke akun tertaut → auth berubah ke target,
   session regenerated, flash sukses.
5. Switch ke akun **tidak** tertaut → 403, auth tidak berubah.
6. Switch ke akun **admin** → 403.
7. Setiap switch membuat satu baris `account_switch_logs`
   (from/to/ip benar).
8. Non-admin tidak bisa membuka halaman log admin (redirect dashboard);
   admin bisa dan melihat entri.
9. Endpoint switch kena rate-limit setelah batas.

## Yang TIDAK dibangun (YAGNI, Fase 1)

- Multi-sesi bersamaan (Google-style account switcher).
- Auto-link berdasarkan NIP/NIK.
- PIN untuk switch.
- Menyatukan menjadi satu akun multi-shift (Opsi A).
