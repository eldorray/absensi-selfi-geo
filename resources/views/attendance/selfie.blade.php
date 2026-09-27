<x-layouts.mobile title="Absensi Masuk" backUrl="{{ route('attendance.dashboard') }}">
    <div x-data="attendanceForm()" x-init="init()" class="flex flex-col gap-4">
        @if ($todayAttendance)
            <x-guru.card variant="hero" aria-label="Absen masuk tercatat">
                <div class="flex items-center justify-between gap-3">
                    <span class="g-hero__label">Absen masuk tercatat</span>
                    <x-guru.chip :tone="$todayAttendance->status->value === 'late' ? 'hero-late' : 'hero'">{{ $todayAttendance->status->value === 'late' ? 'Terlambat' : 'Tepat waktu' }}</x-guru.chip>
                </div>
                <div class="flex items-center gap-4">
                    <img src="{{ $todayAttendance->image_url }}" alt="Foto absen masuk hari ini" width="72" height="72" class="size-18 flex-none rounded-2xl object-cover">
                    <div class="g-hero__time">
                        <span class="g-hero__time-label">Masuk</span>
                        <span class="g-hero__time-value">{{ $todayAttendance->created_at->format('H.i') }}</span>
                    </div>
                </div>
                <x-guru.button variant="hero" :href="route('attendance.dashboard')" icon="home">Kembali ke Beranda</x-guru.button>
            </x-guru.card>
        @else
            @include('attendance.partials.absen-form', ['mode' => 'masuk', 'action' => route('attendance.store')])
        @endif
    </div>

    <!-- Alpine.js logic controller -->
    <script>
        function attendanceForm() {
            return {
                cameraLoading: true,
                cameraError: null,
                photoTaken: false,
                imageBase64: '',
                stream: null,
                livenessLoading: false,
                livenessError: null,
                faceDetected: false,
                captureFlash: false,
                detector: null,
                manualAllowed: false,
                manualOnly: false,
                manualTimer: null,
                livenessVerified: true,
                locationLoading: false,
                locationFetched: false,
                locationError: null,
                latitude: '',
                longitude: '',
                officeId: '{{ $user->office_id }}',
                isSubmitting: false,
                currentDistance: 0,
                maxDistance: 0,
                distanceWarning: false,
                accuracy: 0,
                distanceOk: false,
                offices: @json($offices),

                get canSubmit() {
                    return this.officeId && this.locationFetched && this.photoTaken && !this.isSubmitting && !this.distanceWarning;
                },

                get statusMessage() {
                    if (this.photoTaken) return this.livenessVerified ? 'Foto berhasil diambil.' : 'Foto manual berhasil diambil.';
                    if (this.cameraLoading) return 'Menghubungkan kamera.';
                    if (this.livenessLoading) return 'Memuat deteksi wajah.';
                    if (this.manualAllowed) return 'Kedip tidak terdeteksi. Tombol Ambil Foto Manual tersedia.';
                    return this.faceDetected ? 'Wajah terdeteksi. Kedipkan mata untuk mengambil foto.' : 'Arahkan wajah ke kamera.';
                },

                async init() {
                    // Start fetching the face model while the camera permission prompt is open.
                    window.preloadBlinkDetector?.();
                    await this.initCamera();
                    this.fetchLocation();
                    if (!this.cameraError) this.startLiveness();
                },

                async startLiveness() {
                    this.livenessError = null;
                    this.livenessLoading = true;
                    this.faceDetected = false;
                    if (!window.createBlinkDetector) {
                        this.livenessLoading = false;
                        this.livenessError = 'Modul deteksi wajah tidak tersedia.';
                        return;
                    }
                    if (!this.detector) {
                        this.detector = window.createBlinkDetector({
                            video: this.$refs.video,
                            onState: ({ faceDetected }) => { this.faceDetected = faceDetected; },
                            onBlink: () => { this.onBlinkCapture(); },
                            onError: (e) => {
                                console.error('liveness load error', e);
                                this.livenessLoading = false;
                                this.livenessError = 'Gagal memuat model. Periksa koneksi lalu coba lagi.';
                            },
                        });
                    }
                    await this.detector.start();
                    if (!this.livenessError) this.livenessLoading = false;
                    if (!this.livenessError && !this.photoTaken) {
                        clearTimeout(this.manualTimer);
                        this.manualTimer = setTimeout(() => { this.manualAllowed = true; }, 15000);
                    }
                },

                takeManualPhoto() {
                    this.livenessVerified = false;
                    this.takePhoto();
                },

                useManualCapture() {
                    // Model failed to load: let the teacher frame the shot themselves.
                    if (this.detector) this.detector.stop();
                    this.livenessError = null;
                    this.manualOnly = true;
                    this.manualAllowed = true;
                },

                onBlinkCapture() {
                    this.livenessVerified = true;
                    this.captureFlash = true;
                    this.takePhoto();
                    setTimeout(() => { this.captureFlash = false; }, 220);
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
                            this.cameraError = 'Mohon izinkan akses kamera di pengaturan browser Anda.';
                        } else if (error.name === 'NotFoundError') {
                            this.cameraError = 'Kamera tidak ditemukan pada perangkat ini.';
                        } else {
                            this.cameraError = 'Gagal mengakses kamera perangkat.';
                        }
                    }
                },

                takePhoto() {
                    clearTimeout(this.manualTimer);
                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    const context = canvas.getContext('2d');
                    const maxPhotoDimension = 1024;
                    const photoScale = Math.min(1, maxPhotoDimension / Math.max(video.videoWidth, video.videoHeight));
                    canvas.width = Math.round(video.videoWidth * photoScale);
                    canvas.height = Math.round(video.videoHeight * photoScale);
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    this.imageBase64 = canvas.toDataURL('image/jpeg', 0.85);
                    this.photoTaken = true;
                    if (this.detector) this.detector.stop();
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                    }
                },

                async retakePhoto() {
                    this.photoTaken = false;
                    this.imageBase64 = '';
                    await this.initCamera();
                    if (!this.cameraError && !this.manualOnly) this.startLiveness();
                },

                fetchLocation() {
                    this.locationLoading = true;
                    this.locationError = null;
                    if (!navigator.geolocation) {
                        this.locationLoading = false;
                        this.locationError = 'Browser tidak mendukung GPS Geolocation.';
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.latitude = position.coords.latitude.toFixed(8);
                            this.longitude = position.coords.longitude.toFixed(8);
                            this.accuracy = Math.round(position.coords.accuracy);
                            this.locationFetched = true;
                            this.locationLoading = false;
                            this.calculateDistance();
                        },
                        (error) => {
                            this.locationLoading = false;
                            switch (error.code) {
                                case error.PERMISSION_DENIED:
                                    this.locationError = 'Izin akses lokasi GPS ditolak.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    this.locationError = 'Informasi koordinat lokasi tidak tersedia.';
                                    break;
                                default:
                                    this.locationError = 'Gagal mengambil koordinat lokasi.';
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

                submitErrorMessage(response, data) {
                    if (response.status === 401 || response.status === 419 || response.redirected) {
                        return 'Sesi Anda sudah berakhir. Muat ulang halaman atau login kembali, lalu coba lagi.';
                    }
                    if (response.status === 429) {
                        return 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.';
                    }
                    const messages = Object.values(data.errors || {}).flat().join('\n');
                    return messages || data.message || `Terjadi kesalahan server (kode ${response.status}). Silakan coba lagi.`;
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
                    formData.append('liveness_verified', this.livenessVerified ? '1' : '0');
                    try {
                        const response = await fetch('{{ route('attendance.store') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        // Only a JSON success counts: a redirect (e.g. to login after the
                        // session expired) or an HTML error page must not look like it worked.
                        const data = await response.json().catch(() => ({}));
                        if (response.ok && data.success) {
                            window.location.href = '{{ route('attendance.dashboard') }}';
                            return;
                        }
                        this.isSubmitting = false;
                        alert('Gagal absensi:\n' + this.submitErrorMessage(response, data));
                    } catch (error) {
                        this.isSubmitting = false;
                        alert('Terjadi gangguan koneksi jaringan. Silakan coba kembali.');
                    }
                }
            };
        }
    </script>
</x-layouts.mobile>
