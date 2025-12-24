<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Absensi Selfie - AbsenKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f3f4f6;
            min-height: 100vh;
        }

        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0369a1 100%);
            min-height: 100vh;
        }

        .safe-bottom {
            padding-bottom: calc(80px + env(safe-area-inset-bottom));
        }
    </style>
</head>

<body>
    <div class="mobile-container safe-bottom" x-data="attendanceForm()" x-init="init()">
        <div class="min-h-screen pb-20">
            <!-- Header -->
            <div class="px-5 pt-8 pb-6">
                <div class="flex items-center justify-between">
                    <a href="{{ route('attendance.dashboard') }}" class="p-2 -ml-2 text-white/70 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </a>
                    <h1 class="text-white font-semibold text-lg">Absensi Selfie</h1>
                    <div class="w-10"></div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-6">

                @if ($todayAttendance)
                    <!-- Already Checked In -->
                    <div
                        class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-center text-white">
                        <div class="w-24 h-24 mx-auto mb-4 rounded-full border-4 border-white/50 overflow-hidden">
                            <img src="{{ $todayAttendance->image_url }}" alt="Selfie"
                                class="w-full h-full object-cover">
                        </div>
                        <h2 class="text-2xl font-bold mb-2">Sudah Absen!</h2>
                        <p class="text-white/80">Status: {{ $todayAttendance->status->label() }}</p>
                        <p class="text-white/70 text-sm mt-1">Check-in {{ $todayAttendance->created_at->format('H:i') }}
                            WIB</p>
                        <a href="{{ route('attendance.dashboard') }}"
                            class="inline-block mt-6 px-6 py-3 bg-white/20 hover:bg-white/30 rounded-xl font-medium transition-colors">
                            Kembali ke Beranda
                        </a>
                    </div>
                @else
                    <!-- Camera Section -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-5">
                        <div class="relative aspect-[3/4] bg-gray-900">
                            <video x-ref="video" x-show="!photoTaken" autoplay playsinline
                                class="w-full h-full object-cover"></video>
                            <canvas x-ref="canvas" x-show="photoTaken" class="w-full h-full object-cover"></canvas>

                            <!-- Camera Loading -->
                            <div x-show="cameraLoading"
                                class="absolute inset-0 flex items-center justify-center bg-gray-900">
                                <div class="text-center">
                                    <svg class="animate-spin h-12 w-12 text-sky-500 mx-auto mb-3" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="text-gray-400">Mengaktifkan kamera...</span>
                                </div>
                            </div>

                            <!-- Camera Error -->
                            <div x-show="cameraError"
                                class="absolute inset-0 flex items-center justify-center bg-gray-900">
                                <div class="text-center px-6">
                                    <svg class="w-16 h-16 text-red-500 mx-auto mb-3" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                    <p class="text-red-400" x-text="cameraError"></p>
                                </div>
                            </div>

                            <!-- Photo Overlay -->
                            <div x-show="photoTaken"
                                class="absolute bottom-4 left-4 right-4 flex items-center justify-center">
                                <span class="px-4 py-2 bg-green-500 text-white rounded-full text-sm font-medium">
                                    ✓ Foto Berhasil
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Camera Button -->
                    <div class="flex gap-3 mb-5">
                        <button type="button" @click="takePhoto()" x-show="!photoTaken && !cameraError"
                            :disabled="cameraLoading"
                            class="flex-1 bg-gradient-to-r from-sky-500 to-cyan-500 hover:from-sky-600 hover:to-cyan-600 disabled:from-gray-400 disabled:to-gray-500 text-white font-semibold py-4 px-6 rounded-2xl transition-all shadow-lg flex items-center justify-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                </path>
                            </svg>
                            Ambil Foto
                        </button>
                        <button type="button" @click="retakePhoto()" x-show="photoTaken"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-4 px-6 rounded-2xl transition-all flex items-center justify-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Ulangi
                        </button>
                    </div>

                    <!-- Office Selection -->
                    <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Pilih Kantor</label>
                        <select x-model="officeId" @change="calculateDistance()"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 py-3 text-gray-700">
                            <option value="">-- Pilih Kantor --</option>
                            @foreach ($offices as $office)
                                <option value="{{ $office->id }}" data-lat="{{ $office->latitude }}"
                                    data-lng="{{ $office->longitude }}" data-radius="{{ $office->radius_meters }}">
                                    {{ $office->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Distance Warning -->
                    <div x-show="distanceWarning" class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-5">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            <div>
                                <p class="font-semibold text-red-700">⚠️ Anda Jauh dari Lokasi Kantor!</p>
                                <p class="text-red-600 text-sm mt-1"
                                    x-text="'Jarak Anda: ' + Math.round(currentDistance) + ' meter (maksimal: ' + maxDistance + ' meter)'">
                                </p>
                                <p class="text-red-500 text-xs mt-1">Pastikan Anda berada di area kantor untuk
                                    melakukan absensi.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Distance OK -->
                    <div x-show="distanceOk && locationFetched && officeId"
                        class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-5">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-green-700">✓ Lokasi Valid</p>
                                <p class="text-green-600 text-sm"
                                    x-text="'Jarak: ' + Math.round(currentDistance) + ' meter dari kantor'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Location Status -->
                    <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                    :class="locationFetched ? 'bg-green-100' : 'bg-amber-100'">
                                    <svg x-show="locationLoading" class="animate-spin h-5 w-5 text-amber-600"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <svg x-show="locationFetched && !locationLoading" class="w-5 h-5 text-green-600"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg x-show="locationError && !locationLoading" class="w-5 h-5 text-red-600"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-800"
                                        x-text="locationLoading ? 'Mengambil lokasi...' : (locationFetched ? 'Lokasi Ditemukan' : 'Lokasi Error')">
                                    </p>
                                    <p x-show="locationFetched" class="text-sm text-gray-500"
                                        x-text="latitude + ', ' + longitude"></p>
                                    <p x-show="locationError" class="text-sm text-red-500" x-text="locationError">
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="fetchLocation()" :disabled="locationLoading"
                                class="p-2 text-sky-600 hover:bg-sky-50 rounded-lg">
                                <svg class="w-5 h-5" :class="{ 'animate-spin': locationLoading }" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-5">
                            <ul class="text-red-700 text-sm list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Submit Button -->
                    <form method="POST" action="{{ route('attendance.store') }}" @submit.prevent="submitForm">
                        @csrf
                        <input type="hidden" name="latitude" x-model="latitude">
                        <input type="hidden" name="longitude" x-model="longitude">
                        <input type="hidden" name="image_base64" x-model="imageBase64">
                        <input type="hidden" name="office_id" x-model="officeId">

                        <button type="submit" :disabled="!canSubmit || isSubmitting"
                            class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg flex items-center justify-center text-lg">
                            <svg x-show="isSubmitting" class="animate-spin h-6 w-6 mr-2" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg x-show="!isSubmitting" class="w-6 h-6 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span x-text="isSubmitting ? 'Memproses...' : 'SUBMIT ABSENSI'"></span>
                        </button>
                    </form>

                    <!-- Requirements Checklist -->
                    <div class="mt-4 text-center text-sm text-gray-500">
                        <p class="flex items-center justify-center">
                            <span class="w-3 h-3 rounded-full mr-2"
                                :class="officeId ? 'bg-green-500' : 'bg-gray-300'"></span>
                            Kantor dipilih
                        </p>
                        <p class="flex items-center justify-center mt-1">
                            <span class="w-3 h-3 rounded-full mr-2"
                                :class="locationFetched ? 'bg-green-500' : 'bg-gray-300'"></span>
                            Lokasi GPS
                        </p>
                        <p class="flex items-center justify-center mt-1">
                            <span class="w-3 h-3 rounded-full mr-2"
                                :class="photoTaken ? 'bg-green-500' : 'bg-gray-300'"></span>
                            Foto Selfie
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
            <div class="max-w-[480px] mx-auto flex items-center justify-around h-20">
                <a href="{{ route('attendance.dashboard') }}" class="flex flex-col items-center text-gray-400">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                    </svg>
                    <span class="text-xs mt-1 font-medium">Beranda</span>
                </a>

                <!-- Masuk Button -->
                <a href="{{ route('attendance.selfie') }}" class="flex flex-col items-center -mt-4">
                    <div
                        class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center {{ $todayAttendance ? 'bg-gray-300' : 'bg-gradient-to-r from-green-500 to-emerald-500' }}">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </div>
                    <span
                        class="text-xs mt-1 font-medium {{ $todayAttendance ? 'text-gray-400' : 'text-green-600' }}">Masuk</span>
                </a>

                <!-- Pulang Button -->
                <a href="{{ route('attendance.checkout') }}" class="flex flex-col items-center -mt-4">
                    @php
                        $canCheckout = $todayAttendance && !$todayAttendance->hasCheckedOut();
                    @endphp
                    <div
                        class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center {{ $canCheckout ? 'bg-gradient-to-r from-amber-500 to-orange-500' : 'bg-gray-300' }}">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </div>
                    <span
                        class="text-xs mt-1 font-medium {{ $canCheckout ? 'text-amber-600' : 'text-gray-400' }}">Pulang</span>
                </a>

                <a href="{{ route('attendance.index') }}" class="flex flex-col items-center text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                    <span class="text-xs mt-1 font-medium">Riwayat</span>
                </a>
            </div>
        </nav>
    </div>

    <script>
        function attendanceForm() {
            return {
                cameraLoading: true,
                cameraError: null,
                photoTaken: false,
                imageBase64: '',
                stream: null,
                locationLoading: false,
                locationFetched: false,
                locationError: null,
                latitude: '',
                longitude: '',
                officeId: '',
                isSubmitting: false,
                currentDistance: 0,
                maxDistance: 0,
                distanceWarning: false,
                distanceOk: false,
                offices: @json($offices),

                get canSubmit() {
                    return this.officeId && this.locationFetched && this.photoTaken && !this.isSubmitting && !this
                        .distanceWarning;
                },

                init() {
                    this.initCamera();
                    this.fetchLocation();
                },

                async initCamera() {
                    this.cameraLoading = true;
                    this.cameraError = null;
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: 'user',
                                width: {
                                    ideal: 640
                                },
                                height: {
                                    ideal: 480
                                }
                            }
                        });
                        this.$refs.video.srcObject = this.stream;
                        this.cameraLoading = false;
                    } catch (error) {
                        this.cameraLoading = false;
                        if (error.name === 'NotAllowedError') {
                            this.cameraError = 'Izinkan akses kamera di pengaturan browser.';
                        } else if (error.name === 'NotFoundError') {
                            this.cameraError = 'Kamera tidak ditemukan.';
                        } else {
                            this.cameraError = 'Gagal mengakses kamera.';
                        }
                    }
                },

                takePhoto() {
                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    const context = canvas.getContext('2d');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    this.imageBase64 = canvas.toDataURL('image/jpeg', 0.8);
                    this.photoTaken = true;
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                    }
                },

                async retakePhoto() {
                    this.photoTaken = false;
                    this.imageBase64 = '';
                    await this.initCamera();
                },

                fetchLocation() {
                    this.locationLoading = true;
                    this.locationError = null;
                    if (!navigator.geolocation) {
                        this.locationLoading = false;
                        this.locationError = 'Geolocation tidak didukung.';
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.latitude = position.coords.latitude.toFixed(8);
                            this.longitude = position.coords.longitude.toFixed(8);
                            this.locationFetched = true;
                            this.locationLoading = false;
                            this.calculateDistance();
                        },
                        (error) => {
                            this.locationLoading = false;
                            switch (error.code) {
                                case error.PERMISSION_DENIED:
                                    this.locationError = 'Akses lokasi ditolak.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    this.locationError = 'Lokasi tidak tersedia.';
                                    break;
                                default:
                                    this.locationError = 'Gagal mendapatkan lokasi.';
                            }
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                },

                calculateDistance() {
                    if (!this.officeId || !this.locationFetched) {
                        this.distanceWarning = false;
                        this.distanceOk = false;
                        return;
                    }
                    const office = this.offices.find(o => o.id == this.officeId);
                    if (!office) return;

                    const lat1 = parseFloat(this.latitude);
                    const lon1 = parseFloat(this.longitude);
                    const lat2 = parseFloat(office.latitude);
                    const lon2 = parseFloat(office.longitude);

                    // Haversine formula
                    const R = 6371000; // Earth radius in meters
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    this.currentDistance = R * c;
                    this.maxDistance = office.radius_meters;

                    if (this.currentDistance > this.maxDistance) {
                        this.distanceWarning = true;
                        this.distanceOk = false;
                    } else {
                        this.distanceWarning = false;
                        this.distanceOk = true;
                    }
                },

                async submitForm() {
                    if (!this.canSubmit) return;
                    this.isSubmitting = true;
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('office_id', this.officeId);
                    formData.append('latitude', this.latitude);
                    formData.append('longitude', this.longitude);
                    formData.append('image_base64', this.imageBase64);
                    try {
                        const response = await fetch('{{ route('attendance.store') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok || response.redirected) {
                            window.location.href = '{{ route('attendance.dashboard') }}';
                        } else if (response.status === 422) {
                            // Validation error
                            const data = await response.json();
                            const errors = data.errors || {};
                            const errorMessages = Object.values(errors).flat().join('\n');
                            alert('Gagal absen:\n' + (errorMessages || data.message || 'Terjadi kesalahan validasi.'));
                            this.isSubmitting = false;
                        } else {
                            const data = await response.json().catch(() => ({}));
                            alert('Gagal absen: ' + (data.message || 'Terjadi kesalahan server.'));
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        this.isSubmitting = false;
                        alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
                    }
                }
            };
        }
    </script>
</body>

</html>
