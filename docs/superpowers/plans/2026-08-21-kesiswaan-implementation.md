# Kesiswaan Implementation Plan

> **For Hermes:** Execute task-by-task using TDD and independent review.

**Goal:** Membangun pusat profil siswa read-only dan rujukan wali kelas ke antrean Guru BK sesuai desain yang disetujui.

**Architecture:** Gunakan data siswa, kelas, penugasan wali kelas, dan BK yang ada. Tambahkan domain `StudentReferral` dengan policy/scope server-side, state machine terpisah dari BK, lampiran privat, database notification, serta UI admin dan PWA. Satu FK unik dari `bk_records` menghubungkan maksimal satu Catatan BK ke satu rujukan.

**Tech Stack:** Laravel 12, Blade/Alpine, Eloquent, Laravel Notifications, Pest 4, MySQL/SQLite tests, Tailwind 4.

---

## Task 1: Domain dan schema rujukan

**Files:** migration notifications/referrals/attachments/histories/BK FK; enums; models; factories; relasi pada Student/User/BkRecord; tests schema/model.

1. Tulis failing tests untuk tabel, casts, relasi, snapshot jenjang, FK unik BK, dan tidak adanya soft delete.
2. Jalankan focused tests dan pastikan gagal.
3. Buat migration melalui Artisan dan implementasikan model/factory/enum minimal.
4. Jalankan migration dan focused tests hingga lulus.

## Task 2: Authorization dan scope siswa/rujukan

**Files:** `app/Services/Kesiswaan/StudentAccessService.php`, `app/Policies/StudentReferralPolicy.php`, scope model, tests authorization.

1. Tulis failing tests untuk admin, wali aktif, guru biasa, BK terpetakan/tidak terpetakan, kepemilikan referral, antrean baru, dan record setelah claim.
2. Implementasikan query scope dan policy fail-closed.
3. Verifikasi 403 capability dan 404 out-of-scope.

## Task 3: Pusat profil admin dan PWA

**Files:** controller admin/employee Kesiswaan, routes, views index/show, sidebar/dashboard, tests.

1. Tulis failing feature tests untuk pencarian/filter, profil read-only, cakupan siswa, dan menu kondisional.
2. Implementasikan daftar lintas jenjang admin dan daftar scoped PWA.
3. Implementasikan profil yang menampilkan identitas, kelas/wali/tahun aktif, kontak, ringkasan BK, serta referral yang berwenang.
4. Pastikan tidak ada mutation route data siswa di Kesiswaan.

## Task 4: Ringkasan BK aman dan penutupan bypass lama

**Files:** `BkSummaryService`, partials, `MyClassController`/views, tests.

1. Tulis failing tests negatif yang memasukkan marker rahasia pada kronologi, konseling, follow-up, komunikasi wali, dan lampiran.
2. Implementasikan agregat jumlah/jenis/status/perlu tindak lanjut tanpa memuat isi profesional.
3. Ubah route Kelas Saya lama agar tidak lagi membuka kronologi/detail BK.
4. Jalankan tes Kesiswaan dan MyClass.

## Task 5: Pembuatan referral dan lampiran privat atomic

**Files:** Form Request, create service, attachment service/controller, form PWA, routes, tests.

1. Tulis failing tests untuk siswa di kelas aktif, tanggal, urgensi, maksimal 3 file, MIME, 5 MB, Bahasa Indonesia, dan file privat.
2. Implementasikan transaction pembuatan referral + history awal dan snapshot jenjang.
3. Implementasikan cleanup eksplisit jika penyimpanan file atau transaction gagal.
4. Implementasikan download terotorisasi dengan verifikasi attachment milik referral.

## Task 6: Antrean, claim, state machine, dan detail referral

**Files:** queue controller/service, claim/transition actions, transition request, views, tests.

1. Tulis failing tests urutan urgent→important→normal lalu oldest-first.
2. Implementasikan queue sesuai jenjang.
3. Tulis failing double-claim test lalu implementasikan conditional update atomik.
4. Tulis failing transition tests untuk legal/illegal transitions dan safe summary wajib.
5. Implementasikan audit history dan detail berbeda untuk wali/BK/admin.

## Task 7: Integrasi Catatan BK

**Files:** migration/model relation, BkRecordController/Store request/form/routes/tests.

1. Tulis failing tests bahwa hanya assignee referral in-handling dapat membuat BK record.
2. Paksa student/referral server-side; jangan percaya hidden input.
3. Tangani unique conflict secara ramah.
4. Pastikan pembuatan BK tidak menutup referral dan wali tetap tidak dapat membaca record.

## Task 8: Database notifications dan UI badge

**Files:** notification classes, notification controller/view, dashboard/sidebar, routes/tests.

1. Tulis failing tests penerima: semua BK jenjang saat created; wali saat claimed/rejected/completed.
2. Implementasikan channel database dengan payload minimum tanpa observasi sensitif.
3. Implementasikan daftar/read redirect terotorisasi dan badge unread Kesiswaan.
4. Pastikan guru biasa tidak melihat menu/badge.

## Task 9: Security audit dan acceptance

**Files:** route/security/acceptance tests.

1. Pastikan tidak ada route edit/delete referral/history atau API Android baru.
2. Uji ID swapping attachment, out-of-scope student/referral, admin read-only, dan bypass Kelas Saya.
3. Jalankan focused suites, Pint, Blade cache, route list, Vite build, `git diff --check`, dan full suite.
4. Minta independent spec-compliance dan code-quality review; perbaiki temuan lalu ulangi verifikasi.

## Verification commands

```bash
php artisan test tests/Feature/Kesiswaan tests/Unit/Kesiswaan
php artisan test tests/Feature/Employee/MyClassTest.php tests/Feature/Feature/BkAccessTest.php
php artisan test
vendor/bin/pint --dirty
php artisan view:clear && php artisan view:cache
php artisan route:list
npm run build
git diff --check
```
