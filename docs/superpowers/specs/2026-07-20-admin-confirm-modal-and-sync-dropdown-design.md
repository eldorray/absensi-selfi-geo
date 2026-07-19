# Modal Konfirmasi Admin + Dropdown Unit Sync

## Ringkasan

Dua perubahan pada admin panel:

1. **Modal konfirmasi custom** menggantikan dialog `confirm()` native pada semua aksi admin yang butuh konfirmasi (9 titik di 6 view). Satu komponen glass ber-Alpine, di-mount sekali, dipicu lewat event.
2. **Dropdown pilih unit** pada Sync Data Induk: admin memilih `Guru MI` atau `Guru SMP`, lalu sync menarik satu unit tersebut (bukan keduanya sekaligus).

Halaman employee, auth, dan settings di luar cakupan (tetap memakai `confirm()` native bila ada).

## Tujuan

- Konfirmasi aksi memakai modal yang konsisten dengan bahasa visual glass admin, bukan dialog bawaan browser.
- Admin memilih unit sumber saat sync, memberi kontrol per-unit.
- Tidak mengubah aksi/route/otorisasi yang mendasari; hanya lapisan konfirmasi dan pemilihan sumber.

## Non-tujuan

- Tidak mengganti `confirm()` di halaman employee (`settings/profile.blade.php`, `attendance/leaves/approval-show.blade.php`).
- Tidak menambah opsi "Semua unit" pada dropdown (hanya MI dan SMP).
- Tidak mengubah mekanisme sync selain menjadikannya per-satu-sumber.

## Bagian A — Modal Konfirmasi

### Komponen

`resources/views/components/admin/confirm-modal.blade.php` — modal glass ber-Alpine, di-mount sekali.

State Alpine: `open` (bool), `title` (string), `message` (string), `confirmText` (string), `variant` (`'danger'|'primary'|'success'`), `form` (HTMLFormElement|null).

Perilaku:

- Dengar `@admin-confirm.window`: set state dari `$event.detail`, `open = true`, lalu fokus tombol konfirmasi (`$nextTick`).
- Tombol **Konfirmasi**: `form?.submit(); open = false`. (Memakai `HTMLFormElement.submit()` yang melewati handler `submit` sehingga tak memicu ulang dispatch.)
- Tombol **Batal** / **Escape** (`@keydown.escape.window`) / klik overlay (`@click.self` pada overlay): `open = false`, `form = null`.
- Kelas: overlay `admin-modal-overlay`, panel `admin-glass-modal` (keduanya sudah ada di `app.css`).
- Tombol konfirmasi memetakan `variant` → kelas: `danger`→`admin-button-danger`, `success`→`admin-button-success`, selain itu `admin-button-primary`. Tombol batal `admin-button-secondary`.
- Transisi x-show memakai pola yang sama dengan modal foto laporan harian; `x-cloak` mencegah flash.

Aksesibilitas: `role="dialog"`, `aria-modal="true"`, `aria-labelledby` menunjuk judul; fokus awal ke tombol konfirmasi; Escape menutup; focus ring sudah global; reduced-motion sudah global.

### Mount

Di `resources/views/components/layouts/app.blade.php`, di dalam area ber-scope admin, render bersyarat:

```blade
@if ($isAdminRoute)
    <x-admin.confirm-modal />
@endif
```

`$isAdminRoute` sudah tersedia di layout (`request()->routeIs('admin.*')`).

### Pola pemicu

Setiap form yang butuh konfirmasi mengganti `onsubmit="return confirm('...')"` dengan Alpine:

```blade
<form action="..." method="POST"
    @submit.prevent="$dispatch('admin-confirm', {
        title: 'Hapus User',
        message: 'Yakin ingin menghapus user ini?',
        confirmText: 'Hapus',
        variant: 'danger',
        form: $el,
    })">
```

Form membutuhkan `x-data` agar `$dispatch`/`$el` bekerja bila belum berada dalam scope Alpine — tambahkan `x-data="{}"` pada form (atau pastikan berada di bawah root Alpine layout; layout `<body>` sudah punya `x-data`, jadi `$dispatch` tersedia — tetap sertakan `x-data="{}"` pada tiap form pemicu agar `$el` menunjuk form dan mandiri).

### Titik yang diubah (9 aksi, 6 file)

| File | Aksi | Pesan (dipertahankan) | variant | confirmText |
|---|---|---|---|---|
| `admin/users/index.blade.php` | Sync (lihat Bagian B) | dinamis (unit) | primary | Sync |
| `admin/users/index.blade.php` | Hapus user | Yakin ingin menghapus user ini? | danger | Hapus |
| `admin/roles/index.blade.php` | Hapus role | Yakin ingin menghapus role ini? | danger | Hapus |
| `admin/offices/index.blade.php` | Hapus kantor | Yakin ingin menghapus kantor ini? | danger | Hapus |
| `admin/announcements/index.blade.php` | Hapus informasi | Yakin ingin menghapus informasi ini? | danger | Hapus |
| `admin/academic-years/index.blade.php` | Aktifkan tahun ajaran | Aktifkan tahun ajaran ini? Jadwal kerja akan di-reset. | primary | Aktifkan |
| `admin/academic-years/index.blade.php` | Hapus tahun ajaran | Yakin ingin menghapus tahun ajaran ini? | danger | Hapus |
| `admin/leaves/show.blade.php` | Setujui izin | Setujui pengajuan ini? | success | Setujui |
| `admin/leaves/show.blade.php` | Tolak izin | Tolak pengajuan ini? | danger | Tolak |

Untuk `leaves/show.blade.php`, konfirmasi kini di level form (`@submit.prevent`), bukan `onclick` di tombol. Perilaku submit/route tak berubah. Field textarea alasan tolak tetap ikut ter-submit karena form yang sama di-submit oleh modal.

## Bagian B — Dropdown Unit Sync

### Service

`app/Services/UserSyncService.php`:

- Ubah tanda tangan menjadi `public function sync(string $source): array` dengan bentuk kembalian sama (`array{created,updated,failed,errors}`).
- Validasi awal: `if (! in_array($source, ['guru-mi', 'guru-smp'], true)) throw new RuntimeException('Sumber sync tidak valid.');`
- Hapus konstanta/loop `SOURCES`; lakukan loop paginasi hanya untuk `$source` yang diberikan. Sisanya (mapping, match by NIP, create/update, throw non-2xx/format, propagate ConnectionException) tetap.

### Controller

`app/Http/Controllers/Admin/UserController::syncFromApi`:

- Validasi request: `$request->validate(['source' => ['required', 'in:guru-mi,guru-smp']]);`
- Panggil `$service->sync($request->string('source')->toString());`
- Pesan sukses menyebut unit: label `guru-mi`→`Guru MI`, `guru-smp`→`Guru SMP`. Contoh: `"Sync Guru MI selesai: 3 user baru, 1 diperbarui."`
- Penanganan `ConnectionException` dan `RuntimeException` tetap seperti sekarang.

### UI

`resources/views/admin/users/index.blade.php` — ganti tombol sync tunggal dengan form berisi select + tombol:

```blade
<form action="{{ route('admin.users.sync') }}" method="POST" x-data="{}"
    @submit.prevent="$dispatch('admin-confirm', {
        title: 'Sync Data Induk',
        message: 'Tarik data ' + ($el.source.options[$el.source.selectedIndex].text) + ' dari API data induk? Ini membuat/memperbarui user.',
        confirmText: 'Sync',
        variant: 'primary',
        form: $el,
    })">
    @csrf
    <select name="source" class="admin-field ...">
        <option value="guru-mi">Guru MI</option>
        <option value="guru-smp">Guru SMP</option>
    </select>
    <button type="submit" class="admin-button-secondary ...">Sync Data Induk</button>
</form>
```

Select dan tombol ditata sejajar (mis. `inline-flex gap`), konsisten dengan tombol "Tambah User" di sebelahnya.

## Aliran Data

Tak berubah selain: (a) submit form kini melewati modal konfirmasi sebelum benar-benar dikirim; (b) request sync menyertakan `source`, dan service memproses satu unit. Route, CSRF, otorisasi admin, redirect, dan flash tetap.

## Testing

`tests/Feature/Admin/UserSyncTest.php` (perbarui):

- Semua panggilan `sync()` → `sync('guru-mi')` (atau `'guru-smp'`), dan `Http::fake` cukup untuk satu sumber.
- Test service: `sync('guru-mi')` hanya menarik endpoint guru-mi; `sync('guru-smp')` hanya guru-smp.
- Test service: `sync('invalid')` melempar `RuntimeException`.
- Test endpoint: `POST admin.users.sync` dengan `source=guru-mi` → sukses, user tercreate; tanpa `source` atau source invalid → gagal validasi (redirect back dengan error, tak ada user dibuat).
- Test non-admin & connection error tetap (sertakan `source` valid pada request).

Modal konfirmasi adalah perilaku front-end Alpine; verifikasi manual di browser (bukan test otomatis): tiap aksi memunculkan modal, Konfirmasi menjalankan aksi, Batal/Escape/klik-luar membatalkan.

## Verifikasi

- `php artisan test` hijau; `./vendor/bin/pint` (file berubah) & `./vendor/bin/phpstan analyse` tanpa error baru.
- `npm run build`, `php artisan view:cache` tanpa error Blade.
- Manual: tiap aksi konfirmasi memunculkan modal glass; sync MI/SMP menarik unit yang benar; light & dark mode; keyboard (Escape, fokus).
- Tak ada `confirm(` tersisa di `resources/views/admin/**`.

## Risiko & catatan

- **JS dinonaktifkan:** modal bergantung Alpine. Bila JS mati, `@submit.prevent` mencegah submit sehingga aksi tak jalan — dapat diterima untuk panel admin internal (konsisten dgn modal foto & sidebar yang juga butuh Alpine). Tak ada fallback `confirm()` agar tak ada dua dialog.
- **Konsistensi employee:** halaman employee tetap `confirm()` native; bila kelak diinginkan seragam, komponen ini bisa diperluas.
