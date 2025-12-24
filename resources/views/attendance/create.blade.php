<x-layouts.app>
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                <svg class="inline-block w-8 h-8 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Absensi Selfie
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Lakukan absensi dengan foto selfie dan verifikasi lokasi GPS
            </p>
        </div>

        @if($todayAttendance)
            <!-- Already Checked In Card -->
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-2xl p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center">
                    <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-green-800 dark:text-green-200 mb-2">
                    Anda Sudah Absen Hari Ini
                </h2>
                <p class="text-green-600 dark:text-green-400 mb-4">
                    Status: <span class="font-semibold px-3 py-1 rounded-full {{ $todayAttendance->status->badgeClass() }}">{{ $todayAttendance->status->label() }}</span>
                </p>
                <p class="text-sm text-green-600 dark:text-green-400">
                    Check-in pada {{ $todayAttendance->created_at->format('H:i') }} WIB
                </p>
                <div class="mt-6">
                    <img src="{{ $todayAttendance->image_url }}" alt="Selfie Absensi" class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-green-200 dark:border-green-700">
                </div>
                <a href="{{ route('attendance.index') }}" class="inline-flex items-center mt-6 text-green-600 dark:text-green-400 hover:underline">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Lihat Riwayat Absensi
                </a>
            </div>
        @else
            <!-- Attendance Form -->
            <div x-data="attendanceForm()" x-init="init()" class="space-y-6">
                
                <!-- Success Message -->
                @if(session('success'))
                    <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-xl p-4 flex items-center">
                        <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-green-700 dark:text-green-300">{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl p-4">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <ul class="text-red-700 dark:text-red-300 text-sm list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Camera Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                            <h2 class="text-lg font-semibold text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Foto Selfie
                            </h2>
                        </div>
                        <div class="p-6">
                            <!-- Camera Preview -->
                            <div class="relative bg-gray-900 rounded-xl overflow-hidden aspect-[4/3] mb-4">
                                <video x-ref="video" x-show="!photoTaken" autoplay playsinline class="w-full h-full object-cover"></video>
                                <canvas x-ref="canvas" x-show="photoTaken" class="w-full h-full object-cover"></canvas>
                                
                                <!-- Camera Loading State -->
                                <div x-show="cameraLoading" class="absolute inset-0 flex items-center justify-center bg-gray-900">
                                    <div class="text-center">
                                        <svg class="animate-spin h-10 w-10 text-indigo-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-gray-400 text-sm">Mengaktifkan kamera...</span>
                                    </div>
                                </div>

                                <!-- Camera Error State -->
                                <div x-show="cameraError" class="absolute inset-0 flex items-center justify-center bg-gray-900">
                                    <div class="text-center px-4">
                                        <svg class="w-12 h-12 text-red-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <p class="text-red-400 text-sm" x-text="cameraError"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Camera Buttons -->
                            <div class="flex gap-3">
                                <button 
                                    type="button"
                                    @click="takePhoto()"
                                    x-show="!photoTaken && !cameraError"
                                    :disabled="cameraLoading"
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-medium py-3 px-4 rounded-xl transition-colors flex items-center justify-center"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Ambil Foto
                                </button>
                                <button 
                                    type="button"
                                    @click="retakePhoto()"
                                    x-show="photoTaken"
                                    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-4 rounded-xl transition-colors flex items-center justify-center"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Ulangi Foto
                                </button>
                            </div>

                            <!-- Photo Status -->
                            <div class="mt-4 flex items-center" :class="photoTaken ? 'text-green-600 dark:text-green-400' : 'text-gray-400'">
                                <svg x-show="photoTaken" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <svg x-show="!photoTaken" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="photoTaken ? 'Foto berhasil diambil' : 'Foto belum diambil'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Form Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                            <h2 class="text-lg font-semibold text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Lokasi & Kantor
                            </h2>
                        </div>
                        <div class="p-6">
                            <form method="POST" action="{{ route('attendance.store') }}" @submit.prevent="submitForm">
                                @csrf
                                
                                <!-- Hidden Fields -->
                                <input type="hidden" name="latitude" x-model="latitude">
                                <input type="hidden" name="longitude" x-model="longitude">
                                <input type="hidden" name="image_base64" x-model="imageBase64">

                                <!-- Office Selection -->
                                <div class="mb-6">
                                    <label for="office_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Pilih Kantor
                                    </label>
                                    <select 
                                        name="office_id" 
                                        id="office_id"
                                        x-model="officeId"
                                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3"
                                    >
                                        <option value="">-- Pilih Kantor --</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Location Status -->
                                <div class="mb-6 p-4 rounded-xl" :class="locationFetched ? 'bg-green-50 dark:bg-green-900/30' : 'bg-yellow-50 dark:bg-yellow-900/30'">
                                    <div class="flex items-start">
                                        <!-- Loading Spinner -->
                                        <svg x-show="locationLoading" class="animate-spin h-5 w-5 text-yellow-600 dark:text-yellow-400 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        
                                        <!-- Success Icon -->
                                        <svg x-show="locationFetched && !locationLoading" class="h-5 w-5 text-green-600 dark:text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>

                                        <!-- Error Icon -->
                                        <svg x-show="locationError && !locationLoading" class="h-5 w-5 text-red-600 dark:text-red-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>

                                        <div class="flex-1">
                                            <p class="font-medium" :class="locationFetched ? 'text-green-700 dark:text-green-300' : (locationError ? 'text-red-700 dark:text-red-300' : 'text-yellow-700 dark:text-yellow-300')">
                                                <span x-show="locationLoading">Mengambil lokasi GPS...</span>
                                                <span x-show="locationFetched && !locationLoading">Lokasi Berhasil Diambil</span>
                                                <span x-show="locationError && !locationLoading" x-text="locationError"></span>
                                            </p>
                                            <p x-show="locationFetched" class="text-sm text-green-600 dark:text-green-400 mt-1">
                                                Lat: <span x-text="latitude"></span>, Long: <span x-text="longitude"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Refresh Location Button -->
                                <button 
                                    type="button"
                                    @click="fetchLocation()"
                                    :disabled="locationLoading"
                                    class="w-full mb-6 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 text-gray-700 dark:text-gray-300 font-medium py-3 px-4 rounded-xl transition-colors flex items-center justify-center"
                                >
                                    <svg class="w-5 h-5 mr-2" :class="{'animate-spin': locationLoading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Refresh Lokasi
                                </button>

                                <!-- Submit Button -->
                                <button 
                                    type="submit"
                                    :disabled="!canSubmit || isSubmitting"
                                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-semibold py-4 px-6 rounded-xl transition-all transform hover:scale-[1.02] disabled:transform-none shadow-lg disabled:shadow-none flex items-center justify-center"
                                >
                                    <svg x-show="isSubmitting" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="!isSubmitting" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Submit Absensi'"></span>
                                </button>

                                <!-- Submit Requirements -->
                                <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                    <p class="font-medium mb-2">Syarat submit absensi:</p>
                                    <ul class="space-y-1">
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" :class="officeId ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Pilih kantor tujuan
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" :class="locationFetched ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Lokasi GPS berhasil diambil
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" :class="photoTaken ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Foto selfie sudah diambil
                                        </li>
                                    </ul>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function attendanceForm() {
                    return {
                        // Camera
                        cameraLoading: true,
                        cameraError: null,
                        photoTaken: false,
                        imageBase64: '',
                        stream: null,

                        // Location
                        locationLoading: false,
                        locationFetched: false,
                        locationError: null,
                        latitude: '',
                        longitude: '',

                        // Form
                        officeId: '',
                        isSubmitting: false,

                        get canSubmit() {
                            return this.officeId && this.locationFetched && this.photoTaken && !this.isSubmitting;
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
                                        width: { ideal: 640 },
                                        height: { ideal: 480 }
                                    }
                                });
                                this.$refs.video.srcObject = this.stream;
                                this.cameraLoading = false;
                            } catch (error) {
                                this.cameraLoading = false;
                                if (error.name === 'NotAllowedError') {
                                    this.cameraError = 'Akses kamera ditolak. Mohon izinkan akses kamera di pengaturan browser.';
                                } else if (error.name === 'NotFoundError') {
                                    this.cameraError = 'Kamera tidak ditemukan. Pastikan perangkat memiliki kamera.';
                                } else {
                                    this.cameraError = 'Gagal mengakses kamera: ' + error.message;
                                }
                                console.error('Camera error:', error);
                            }
                        },

                        takePhoto() {
                            const video = this.$refs.video;
                            const canvas = this.$refs.canvas;
                            const context = canvas.getContext('2d');

                            // Set canvas size to match video
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;

                            // Draw video frame to canvas
                            context.drawImage(video, 0, 0, canvas.width, canvas.height);

                            // Convert to Base64 JPEG
                            this.imageBase64 = canvas.toDataURL('image/jpeg', 0.8);
                            this.photoTaken = true;

                            // Stop camera stream to save resources
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
                                this.locationError = 'Geolocation tidak didukung oleh browser ini.';
                                return;
                            }

                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    this.latitude = position.coords.latitude.toFixed(8);
                                    this.longitude = position.coords.longitude.toFixed(8);
                                    this.locationFetched = true;
                                    this.locationLoading = false;
                                },
                                (error) => {
                                    this.locationLoading = false;
                                    switch (error.code) {
                                        case error.PERMISSION_DENIED:
                                            this.locationError = 'Akses lokasi ditolak. Mohon izinkan akses lokasi.';
                                            break;
                                        case error.POSITION_UNAVAILABLE:
                                            this.locationError = 'Informasi lokasi tidak tersedia.';
                                            break;
                                        case error.TIMEOUT:
                                            this.locationError = 'Waktu permintaan lokasi habis.';
                                            break;
                                        default:
                                            this.locationError = 'Gagal mendapatkan lokasi: ' + error.message;
                                    }
                                },
                                {
                                    enableHighAccuracy: true,
                                    timeout: 10000,
                                    maximumAge: 0
                                }
                            );
                        },

                        async submitForm() {
                            if (!this.canSubmit) return;

                            this.isSubmitting = true;

                            // Create form data
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
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });

                                if (response.redirected) {
                                    window.location.href = response.url;
                                } else {
                                    // Handle validation errors
                                    const data = await response.json();
                                    if (data.errors) {
                                        // Reload page to show errors
                                        window.location.reload();
                                    }
                                }
                            } catch (error) {
                                console.error('Submit error:', error);
                                this.isSubmitting = false;
                                alert('Terjadi kesalahan saat mengirim data. Silakan coba lagi.');
                            }
                        }
                    };
                }
            </script>
        @endif
    </div>
</x-layouts.app>
