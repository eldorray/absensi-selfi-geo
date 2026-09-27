{{-- Badan halaman Absen Masuk/Pulang: kamera, status, kantor, lokasi, dan kirim.
     Berada di dalam komponen Alpine attendanceForm()/checkoutForm() milik halaman.
     Butuh $mode ('masuk'|'pulang') dan $action (URL form); $offices dan $user dari view induk. --}}
@php($isPulang = $mode === 'pulang')

<div class="sr-only" role="status" aria-live="polite" x-text="statusMessage"></div>
<div class="sr-only" role="alert" x-text="cameraError || livenessError || locationError || (distanceWarning ? 'Anda berada di luar radius kantor.' : '')"></div>

<section class="g-camera" aria-label="Kamera selfie">
    <video x-ref="video" x-show="!photoTaken" autoplay playsinline class="-scale-x-100"></video>
    {{-- Pratinjau dicerminkan seperti kaca; foto yang disimpan tetap tidak dicerminkan. --}}
    <canvas x-ref="canvas" x-show="photoTaken" class="-scale-x-100"></canvas>

    <div x-show="cameraLoading" class="g-camera__overlay">
        <x-guru.icon name="camera" :size="28" class="animate-pulse" />
        <strong>Menghubungkan kamera…</strong>
    </div>

    <div x-show="cameraError" x-cloak class="g-camera__overlay">
        <x-guru.icon name="alert" :size="28" />
        <strong>Akses kamera gagal</strong>
        <p x-text="cameraError"></p>
    </div>

    <div x-show="livenessLoading && !cameraError && !photoTaken" x-cloak class="g-camera__overlay">
        <x-guru.icon name="eye" :size="28" class="animate-pulse" />
        <strong>Memuat deteksi wajah…</strong>
        <p>Pertama kali bisa agak lama (±15 MB).</p>
    </div>

    <div x-show="livenessError && !photoTaken" x-cloak class="g-camera__overlay">
        <x-guru.icon name="alert" :size="28" />
        <strong>Deteksi wajah gagal dimuat</strong>
        <p x-text="livenessError"></p>
        <div class="mt-2 flex flex-wrap justify-center gap-2">
            <button type="button" @click="startLiveness()" class="g-btn g-btn--hero g-btn--sm">Coba Lagi</button>
            <button type="button" @click="useManualCapture()" class="g-btn g-btn--sm border-white/40 text-white">Foto Manual</button>
        </div>
    </div>

    <div x-show="captureFlash" x-cloak x-transition.opacity.duration.150ms class="g-camera__flash"></div>

    <div x-show="!photoTaken && !cameraLoading && !cameraError" aria-hidden="true">
        <div class="g-camera__scan"></div>
        <div class="g-camera__guide"></div>
        <span class="g-camera__bracket left-4 top-4 rounded-tl-lg border-l-2 border-t-2"></span>
        <span class="g-camera__bracket right-4 top-4 rounded-tr-lg border-r-2 border-t-2"></span>
        <span class="g-camera__bracket bottom-4 left-4 rounded-bl-lg border-b-2 border-l-2"></span>
        <span class="g-camera__bracket bottom-4 right-4 rounded-br-lg border-b-2 border-r-2"></span>
    </div>

    <div x-show="!photoTaken && !cameraLoading && !cameraError && !livenessLoading && !livenessError && !manualOnly" class="g-camera__prompt">
        <x-guru.icon name="eye" :size="20" />
        <span x-text="faceDetected ? 'Kedipkan mata untuk mengambil foto' : 'Arahkan wajah ke dalam bingkai'">Arahkan wajah ke dalam bingkai</span>
    </div>

    <div x-show="photoTaken" x-cloak class="g-camera__badge">
        <x-guru.chip tone="ok"><x-guru.icon name="check" :size="14" /><span x-text="livenessVerified ? 'Foto terkunci' : 'Foto manual'">Foto terkunci</span></x-guru.chip>
    </div>
</section>

<div class="flex flex-col gap-2">
    <div x-show="!photoTaken && !cameraError && !livenessError && !manualAllowed"
        class="flex min-h-14 items-center justify-center gap-2 rounded-2xl border border-dashed border-guru-border px-4 text-sm font-semibold text-guru-muted">
        <x-guru.icon name="eye" :size="18" /> Kedip untuk ambil foto
    </div>
    <button type="button" x-show="!photoTaken && !cameraError && !livenessError && manualAllowed" x-cloak @click="takeManualPhoto()" class="g-btn g-btn--secondary g-btn--block">
        <x-guru.icon name="camera" /> Ambil Foto Manual
    </button>
    <p x-show="manualAllowed && !photoTaken && !cameraError && !livenessError" x-cloak class="text-center text-xs text-guru-muted">
        Kedip tidak terdeteksi? Foto manual tetap bisa dipakai, tapi akan ditandai untuk diperiksa admin.
    </p>
    <button type="button" x-show="photoTaken" x-cloak @click="retakePhoto()" class="g-btn g-btn--secondary g-btn--block">
        <x-guru.icon name="refresh" /> Ulangi Foto
    </button>
</div>

<div class="g-status" aria-hidden="true">
    <span class="g-chip" :class="photoTaken ? 'g-chip--ok' : 'g-chip--neutral'">
        <x-guru.icon name="camera" :size="14" /><span x-text="photoTaken ? 'Wajah siap' : 'Wajah belum'">Wajah belum</span>
    </span>
    <span class="g-chip" :class="locationFetched && distanceOk ? 'g-chip--ok' : ((locationError || distanceWarning) ? 'g-chip--attn' : 'g-chip--neutral')">
        <x-guru.icon name="pin" :size="14" /><span x-text="locationFetched && distanceOk ? 'Lokasi sesuai' : ((locationError || distanceWarning) ? 'Lokasi bermasalah' : 'Mencari lokasi')">Mencari lokasi</span>
    </span>
    <span class="g-chip" :class="officeId ? 'g-chip--ok' : 'g-chip--neutral'">
        <x-guru.icon name="school" :size="14" /><span x-text="officeId ? 'Kantor dipilih' : 'Pilih kantor'">Pilih kantor</span>
    </span>
</div>

<x-guru.card as="div">
    <div class="g-field">
        <label for="office_id" class="g-label flex flex-wrap items-center gap-2">
            Kantor tujuan
            @if ($user->office_id)
                <x-guru.chip tone="ok"><x-guru.icon name="lock" :size="12" />Terkunci oleh admin</x-guru.chip>
            @endif
        </label>
        <select id="office_id" x-model="officeId" @change="calculateDistance()" @if ($user->office_id) disabled @endif class="g-input">
            @unless ($user->office_id)
                <option value="">-- Pilih Lokasi Kerja --</option>
            @endunless
            @foreach ($offices as $office)
                <option value="{{ $office->id }}" data-lat="{{ $office->latitude }}" data-lng="{{ $office->longitude }}" data-radius="{{ $office->radius_meters }}">{{ $office->name }}</option>
            @endforeach
        </select>
    </div>
</x-guru.card>

<x-guru.card as="div">
    <div class="flex items-center gap-3">
        <span class="g-list__icon" :class="locationError ? 'g-list__icon--attn' : 'g-list__icon--primary'"><x-guru.icon name="pin" /></span>
        <div class="min-w-0 flex-1">
            <p class="font-bold" x-text="locationLoading ? 'Mengambil lokasi GPS…' : (locationFetched ? 'Lokasi GPS didapat' : 'Lokasi GPS belum didapat')">Mengambil lokasi GPS…</p>
            <p x-show="locationFetched" x-cloak class="g-num text-xs text-guru-muted" x-text="latitude + ', ' + longitude + (accuracy ? ' · akurasi ±' + accuracy + ' m' : '')"></p>
            <p x-show="locationFetched && officeId" x-cloak class="g-num text-xs text-guru-muted" x-text="'Jarak ' + Math.round(currentDistance) + ' m · batas ' + maxDistance + ' m'"></p>
            <p x-show="locationError" x-cloak class="g-error mt-1" x-text="locationError"></p>
        </div>
        <button type="button" @click="fetchLocation()" :disabled="locationLoading" class="g-iconbtn" aria-label="Perbarui lokasi GPS">
            <x-guru.icon name="refresh" ::class="{ 'animate-spin': locationLoading }" />
        </button>
    </div>
</x-guru.card>

<x-guru.notice tone="error" title="Di luar radius kantor" x-show="distanceWarning" x-cloak>
    <p x-text="'Jarak ' + Math.round(currentDistance) + ' m, batas ' + maxDistance + ' m. Masuk ke area kantor untuk absen.'"></p>
    <p x-show="accuracy > 50" class="mt-1" x-text="'Sinyal GPS kurang akurat (±' + accuracy + ' m). Jika Anda sudah di kantor, coba dekat jendela atau di luar ruangan, lalu tekan tombol perbarui lokasi.'"></p>
</x-guru.notice>

<x-guru.notice tone="ok" title="Lokasi terverifikasi" x-show="distanceOk && locationFetched && officeId" x-cloak>
    <p x-text="'Jarak ' + Math.round(currentDistance) + ' m dari titik pusat kantor.'"></p>
</x-guru.notice>

@if ($errors->any())
    <x-guru.notice tone="error" title="Absen belum tersimpan" role="alert">
        <ul class="list-disc pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-guru.notice>
@endif

<form method="POST" action="{{ $action }}" @submit.prevent="submitForm">
    @csrf
    <input type="hidden" name="latitude" x-model="latitude">
    <input type="hidden" name="longitude" x-model="longitude">
    <input type="hidden" name="image_base64" x-model="imageBase64">
    <input type="hidden" name="office_id" x-model="officeId">

    <button type="submit" :disabled="!canSubmit || isSubmitting" :aria-busy="isSubmitting.toString()"
        class="g-btn g-btn--block {{ $isPulang ? 'g-btn--pulang' : 'g-btn--primary' }}">
        <x-guru.icon :name="$isPulang ? 'logout' : 'login'" />
        <span x-text="isSubmitting ? 'Mengirim…' : @js($isPulang ? 'Kirim Absen Pulang' : 'Kirim Absen Masuk')">{{ $isPulang ? 'Kirim Absen Pulang' : 'Kirim Absen Masuk' }}</span>
    </button>
</form>
