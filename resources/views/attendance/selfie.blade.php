<x-layouts.mobile title="Absensi Masuk" activeTab="masuk">
    <x-slot:headerAction>
        <!-- Optional status dot -->
        <span class="flex h-2 w-2 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
    </x-slot:headerAction>

    <style>
        /* Viewfinder Scanner Animations */
        @keyframes scanline {
            0%, 100% { top: 12%; opacity: 0.8; }
            50% { top: 85%; opacity: 0.8; }
        }

        @keyframes pulse-ring {
            0% { transform: translate(-50%, -50%) scale(0.4); opacity: 0.8; }
            100% { transform: translate(-50%, -50%) scale(2.2); opacity: 0; }
        }

        .animate-scanline {
            animation: scanline 3.2s ease-in-out infinite;
        }

        .animate-pulse-ring {
            animation: pulse-ring 2.2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }

        /* Viewfinder */
        .viewfinder-border-glow {
            box-shadow: 0 0 16px rgba(34, 211, 238, 0.05);
        }

        .theme-scanline {
            background-color: var(--active-nav-color);
            box-shadow: 0 0 12px var(--active-nav-color);
        }

        .theme-brackets {
            border-color: var(--active-nav-color);
        }

        .theme-guideline {
            border-color: rgba(255, 255, 255, 0.15);
        }
        body.light-theme .theme-guideline {
            border-color: rgba(99, 102, 241, 0.2);
        }
    </style>

    <div x-data="attendanceForm()" x-init="init()" class="space-y-4 pb-4">

        @if ($todayAttendance)
            <!-- Already Checked In Screen -->
            <div class="rounded-3xl p-6 text-center theme-status-ok-card theme-status-ok-text relative overflow-hidden">
                <div class="relative w-24 h-24 mx-auto mb-4 rounded-full border-4 border-white/20 overflow-hidden shadow-md bg-slate-900">
                    <img src="{{ $todayAttendance->image_url }}" alt="Selfie" class="w-full h-full object-cover">
                </div>
                <h2 class="text-xl font-black font-display mb-1">Anda Sudah Absen!</h2>
                <p class="text-[10px] theme-text-muted uppercase tracking-wider font-outfit">Status Masuk Harian</p>
                
                <div class="my-4 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    {{ $todayAttendance->status->label() }}
                </div>
                
                <p class="text-xs theme-text-muted">Absen masuk dicatat pukul <span class="font-bold theme-text-main">{{ $todayAttendance->created_at->format('H:i') }} WIB</span></p>
                
                <a href="{{ route('attendance.dashboard') }}"
                    class="mt-6 flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl bg-white/10 hover:bg-white/15 theme-text-main font-bold text-xs uppercase tracking-wider transition-all duration-300 font-outfit border border-white/5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>
        @else
            <!-- Camera Viewfinder Section -->
            <div class="glass-card theme-border rounded-[24px] overflow-hidden p-2.5 viewfinder-border-glow">
                <div class="relative aspect-[3/4] bg-slate-950 rounded-[18px] overflow-hidden">
                    <video x-ref="video" x-show="!photoTaken" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas x-ref="canvas" x-show="photoTaken" class="w-full h-full object-cover"></canvas>

                    <!-- Camera Loading Overlay -->
                    <div x-show="cameraLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/90 z-20">
                        <svg class="animate-spin h-9 w-9 text-green-400 mb-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest font-outfit">Menghubungkan Kamera...</span>
                    </div>

                    <!-- Camera Access Error Overlay -->
                    <div x-show="cameraError" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/95 z-20 px-6 text-center">
                        <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center text-red-500 mb-3 border border-red-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-bold text-red-500 font-outfit uppercase tracking-wider">Akses Kamera Gagal</p>
                        <p class="text-[10px] text-slate-400 mt-1" x-text="cameraError"></p>
                    </div>

                    <!-- Liveness Model Loading Overlay -->
                    <div x-show="livenessLoading && !cameraError && !photoTaken" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/85 z-20">
                        <svg class="animate-spin h-9 w-9 text-green-400 mb-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest font-outfit">Memuat Deteksi Wajah...</span>
                    </div>

                    <!-- Liveness Model Error Overlay -->
                    <div x-show="livenessError && !photoTaken" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/95 z-20 px-6 text-center">
                        <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center text-red-500 mb-3 border border-red-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-bold text-red-500 font-outfit uppercase tracking-wider">Deteksi Wajah Gagal Dimuat</p>
                        <p class="text-[10px] text-slate-400 mt-1" x-text="livenessError"></p>
                        <button type="button" @click="startLiveness()"
                            class="mt-4 px-5 py-2 rounded-xl bg-green-500/90 text-white text-[10px] font-black uppercase tracking-wider font-outfit">Coba Lagi</button>
                    </div>

                    <!-- Blink capture flash -->
                    <div x-show="captureFlash" x-transition.opacity.duration.150ms class="absolute inset-0 bg-white z-30 pointer-events-none"></div>

                    <!-- Scanning Overlay Details -->
                    <div x-show="!photoTaken && !cameraLoading && !cameraError" class="absolute inset-0 pointer-events-none z-10">
                        <!-- Laser line -->
                        <div class="theme-scanline absolute left-2 right-2 h-[1px] rounded animate-scanline"></div>
                        
                        <!-- Brackets corners -->
                        <div class="theme-brackets absolute top-4 left-4 w-4 h-4 border-t-2 border-l-2 rounded-tl-sm opacity-60"></div>
                        <div class="theme-brackets absolute top-4 right-4 w-4 h-4 border-t-2 border-r-2 rounded-tr-sm opacity-60"></div>
                        <div class="theme-brackets absolute bottom-4 left-4 w-4 h-4 border-b-2 border-l-2 rounded-bl-sm opacity-60"></div>
                        <div class="theme-brackets absolute bottom-4 right-4 w-4 h-4 border-b-2 border-r-2 rounded-br-sm opacity-60"></div>

                        <!-- Face Guidance Ellipse -->
                        <div class="absolute inset-10 border border-dashed border-white/5 rounded-full flex items-center justify-center opacity-40">
                            <div class="theme-guideline w-[82%] h-[82%] border border-dashed rounded-full"></div>
                        </div>

                        <!-- Liveness Prompt Tag -->
                        <div x-show="!livenessLoading && !livenessError"
                            class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-950/80 backdrop-blur-md border transition-colors"
                            :class="faceDetected ? 'border-green-400/40' : 'border-white/10'">
                            <span class="w-1.5 h-1.5 rounded-full animate-pulse" :class="faceDetected ? 'bg-green-400' : 'bg-amber-400'"></span>
                            <span class="text-[8px] font-black tracking-widest uppercase font-outfit"
                                :class="faceDetected ? 'text-green-400' : 'text-amber-400'"
                                x-text="faceDetected ? 'Kedipkan Mata' : 'Arahkan Wajah ke Kamera'"></span>
                        </div>
                    </div>

                    <!-- Photo Success Badge -->
                    <div x-show="photoTaken" class="absolute inset-0 pointer-events-none z-10 flex items-center justify-center bg-black/10">
                        <span class="px-3.5 py-1.5 bg-emerald-500/90 backdrop-blur-md text-white rounded-full text-[9px] font-black uppercase tracking-wider shadow-lg flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                            Foto Terkunci
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Trigger: retake only; capture is automatic on blink -->
            <div class="flex gap-3">
                <div x-show="!photoTaken && !cameraError && !livenessError"
                    class="flex-1 flex items-center justify-center gap-2 rounded-2xl py-3.5 text-[11px] font-bold uppercase tracking-wider font-outfit text-slate-400 border border-dashed theme-border">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Kedip untuk Ambil Foto
                </div>
                <button type="button" @click="retakePhoto()" x-show="photoTaken"
                    class="flex-1 theme-nav-inactive flex items-center justify-center gap-2 rounded-2xl py-3.5 text-xs font-bold uppercase tracking-wider font-outfit hover:bg-white/5 active:scale-98 transition-all">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    Ulangi Foto
                </button>
            </div>

            <!-- Office Location Selection Card -->
            <div class="glass-card theme-border rounded-[22px] p-4 text-left">
                <label class="block text-[10px] font-bold tracking-wide uppercase theme-text-muted mb-2 font-outfit">
                    Kantor Tujuan
                    @if ($user->office_id)
                        <span class="ml-1 inline-flex items-center gap-1 text-[9px] normal-case text-emerald-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Terkunci oleh admin
                        </span>
                    @endif
                </label>
                <div class="relative">
                    <select x-model="officeId" @change="calculateDistance()" @if ($user->office_id) disabled @endif
                        class="w-full theme-input rounded-xl px-4 py-3 text-xs font-semibold appearance-none {{ $user->office_id ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }}">
                        @unless ($user->office_id)
                            <option value="">-- Pilih Lokasi Kerja --</option>
                        @endunless
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}" data-lat="{{ $office->latitude }}"
                                data-lng="{{ $office->longitude }}" data-radius="{{ $office->radius_meters }}">
                                {{ $office->name }}</option>
                        @endforeach
                    </select>
                    <!-- Custom Arrow icon -->
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none theme-text-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Geofencing Location Status & Alerts -->
            
            <!-- Distance OUTSIDE boundary Warning -->
            <div x-show="distanceWarning" x-transition.opacity
                class="rounded-[22px] theme-status-late-card theme-status-late-text p-4 text-left border relative overflow-hidden">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex-none bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div class="leading-tight flex-1">
                        <p class="font-black text-xs font-display">Diluar Radius Kantor</p>
                        <p class="text-[9px] theme-text-muted mt-0.5" x-text="'Jarak: ' + Math.round(currentDistance) + 'm (Maksimal: ' + maxDistance + 'm)'"></p>
                        <p class="text-[8px] opacity-75 mt-1">Anda berada di luar batas koordinat GPS kantor. Mohon masuk ke area kantor untuk melakukan absensi.</p>
                    </div>
                </div>
            </div>

            <!-- Distance INSIDE boundary OK -->
            <div x-show="distanceOk && locationFetched && officeId" x-transition.opacity
                class="rounded-[22px] theme-status-ok-card theme-status-ok-text p-4 text-left border relative overflow-hidden">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex-none bg-white/10 flex-center flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                        </svg>
                    </div>
                    <div class="leading-tight flex-1">
                        <p class="font-black text-xs font-display">Lokasi Terverifikasi</p>
                        <p class="text-[9px] theme-text-muted mt-0.5" x-text="'Jarak: ' + Math.round(currentDistance) + 'm dari titik pusat kantor'"></p>
                    </div>
                </div>
            </div>

            <!-- GPS Geolocation Details Card -->
            <div class="glass-card theme-border rounded-[22px] p-4 text-left relative overflow-hidden">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8.5 h-8.5 rounded-xl flex-none flex items-center justify-center relative bg-white/5">
                            <span x-show="locationLoading" class="absolute inset-0 rounded-xl bg-green-400/20 animate-pulse"></span>
                            <span x-show="locationFetched && !locationLoading" class="absolute inset-0.5 rounded-full bg-emerald-400/20 animate-pulse-ring"></span>
                            
                            <svg x-show="locationLoading" class="animate-spin w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            <svg x-show="locationFetched && !locationLoading" class="w-4 h-4 theme-status-ok-text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <svg x-show="locationError && !locationLoading" class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        
                        <div class="leading-none text-left">
                            <p class="text-xs font-bold theme-text-main" x-text="locationLoading ? 'Mengambil GPS...' : (locationFetched ? 'GPS Terkunci' : 'GPS Mati')"></p>
                            <p x-show="locationFetched" class="text-[9px] theme-text-muted mt-1" x-text="latitude + ', ' + longitude"></p>
                            <p x-show="locationError" class="text-[9px] text-red-500 mt-1" x-text="locationError"></p>
                        </div>
                    </div>
                    
                    <button type="button" @click="fetchLocation()" :disabled="locationLoading"
                        class="w-7 h-7 rounded-lg bg-white/5 border border-white/5 flex items-center justify-center theme-text-muted hover:theme-text-main hover:scale-105 active:scale-95 transition-all">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': locationLoading }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Validation Error Messages from Controller -->
            @if ($errors->any())
                <div class="rounded-[22px] border border-red-500/20 bg-red-500/10 p-4 text-left">
                    <ul class="text-xs text-red-500 font-medium list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Submit Form Button -->
            <form method="POST" action="{{ route('attendance.store') }}" @submit.prevent="submitForm" class="pt-2">
                @csrf
                <input type="hidden" name="latitude" x-model="latitude">
                <input type="hidden" name="longitude" x-model="longitude">
                <input type="hidden" name="image_base64" x-model="imageBase64">
                <input type="hidden" name="office_id" x-model="officeId">

                <button type="submit" :disabled="!canSubmit || isSubmitting"
                    class="w-full theme-btn-submit flex items-center justify-center gap-2 rounded-[1.4rem] py-4 text-xs font-bold uppercase tracking-wider font-outfit shadow-md disabled:opacity-50 disabled:scale-100 hover:scale-[1.01] active:scale-[0.99] transition-transform">
                    <svg x-show="isSubmitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    <svg x-show="!isSubmitting" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                    </svg>
                    <span x-text="isSubmitting ? 'Mengirim Data...' : 'SUBMIT ABSENSI MASUK'"></span>
                </button>
            </form>

            <!-- Inline Checklist Requirements Status -->
            <div class="flex items-center justify-center gap-3 pt-2 text-[9px] font-bold theme-text-muted font-outfit uppercase">
                <span class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full transition-colors" :class="officeId ? 'bg-emerald-500 shadow-[0_0_6px_#10b981]' : 'bg-white/20'"></span>
                    Kantor
                </span>
                <span class="text-white/20">•</span>
                <span class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full transition-colors" :class="locationFetched ? 'bg-emerald-500 shadow-[0_0_6px_#10b981]' : 'bg-white/20'"></span>
                    GPS Lokasi
                </span>
                <span class="text-white/20">•</span>
                <span class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full transition-colors" :class="photoTaken ? 'bg-emerald-500 shadow-[0_0_6px_#10b981]' : 'bg-white/20'"></span>
                    Selfie
                </span>
            </div>
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
                distanceOk: false,
                offices: @json($offices),

                get canSubmit() {
                    return this.officeId && this.locationFetched && this.photoTaken && !this.isSubmitting && !this.distanceWarning;
                },

                async init() {
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
                },

                onBlinkCapture() {
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
                    if (!this.cameraError) this.startLiveness();
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
                            const data = await response.json();
                            const errors = data.errors || {};
                            const errorMessages = Object.values(errors).flat().join('\n');
                            alert('Gagal absensi:\n' + (errorMessages || data.message || 'Terjadi kesalahan validasi.'));
                            this.isSubmitting = false;
                        } else {
                            const data = await response.json().catch(() => ({}));
                            alert('Gagal absensi: ' + (data.message || 'Terjadi kesalahan sistem server.'));
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        this.isSubmitting = false;
                        alert('Terjadi gangguan koneksi jaringan. Silakan coba kembali.');
                    }
                }
            };
        }
    </script>
</x-layouts.mobile>
