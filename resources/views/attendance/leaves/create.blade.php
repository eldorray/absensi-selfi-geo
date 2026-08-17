<x-layouts.mobile title="Ajukan Izin" backUrl="{{ route('attendance.leaves.index') }}" isSheet="true" showNav="true">
    <div x-data="{ 
            leaveType: '{{ old('type', 'izin') }}',
            imagePreview: null,
            handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => { this.imagePreview = e.target.result; };
                    reader.readAsDataURL(file);
                } else {
                    this.imagePreview = null;
                }
            }
         }" 
         class="space-y-4 pb-4">
        
        <!-- Form card as Bottom Sheet layout -->
        <div class="glass-card theme-border rounded-[28px] p-5 text-left shadow-xl animate-stagger stagger-40">
            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-xs text-red-400 font-medium">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('attendance.leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Type Selector Radio Tabs (Izin, Cuti, Sakit) -->
                <div>
                    <label class="mb-2 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Jenis Perizinan</label>
                    <div class="grid grid-cols-3 gap-2.5">
                        
                        <!-- Izin Tab -->
                        <label class="relative cursor-pointer" @click="leaveType = 'izin'">
                            <input type="radio" name="type" value="izin" class="peer sr-only" x-model="leaveType" required>
                            <div class="p-3 text-center rounded-2xl glass-card border-2 transition-all duration-250 flex flex-col items-center justify-center peer-checked:border-emerald-500 peer-checked:bg-emerald-500/15"
                                 :class="leaveType === 'izin' ? 'border-emerald-500 bg-emerald-500/15 shadow-md' : 'theme-border hover:bg-white/5 opacity-80'">
                                <svg class="w-5.5 h-5.5 text-emerald-400 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-[10px] font-bold theme-text-main font-outfit">Izin</span>
                            </div>
                            <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex h-5 w-5 scale-0 opacity-0 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg transition-all peer-checked:scale-100 peer-checked:opacity-100">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </label>

                        <!-- Cuti Tab -->
                        <label class="relative cursor-pointer" @click="leaveType = 'cuti'">
                            <input type="radio" name="type" value="cuti" class="peer sr-only" x-model="leaveType">
                            <div class="p-3 text-center rounded-2xl glass-card border-2 transition-all duration-250 flex flex-col items-center justify-center peer-checked:border-emerald-500 peer-checked:bg-emerald-500/15"
                                 :class="leaveType === 'cuti' ? 'border-emerald-500 bg-emerald-500/15 shadow-md' : 'theme-border hover:bg-white/5 opacity-80'">
                                <svg class="w-5.5 h-5.5 text-green-400 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-[10px] font-bold theme-text-main font-outfit">Cuti</span>
                            </div>
                            <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex h-5 w-5 scale-0 opacity-0 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg transition-all peer-checked:scale-100 peer-checked:opacity-100">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </label>

                        <!-- Sakit Tab -->
                        <label class="relative cursor-pointer" @click="leaveType = 'sakit'">
                            <input type="radio" name="type" value="sakit" class="peer sr-only" x-model="leaveType">
                            <div class="p-3 text-center rounded-2xl glass-card border-2 transition-all duration-250 flex flex-col items-center justify-center peer-checked:border-amber-500 peer-checked:bg-amber-500/15"
                                 :class="leaveType === 'sakit' ? 'border-amber-500 bg-amber-500/15 shadow-md' : 'theme-border hover:bg-white/5 opacity-80'">
                                <svg class="w-5.5 h-5.5 text-amber-400 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                </svg>
                                <span class="text-[10px] font-bold theme-text-main font-outfit">Sakit</span>
                            </div>
                            <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex h-5 w-5 scale-0 opacity-0 items-center justify-center rounded-full bg-amber-500 text-white shadow-lg transition-all peer-checked:scale-100 peer-checked:opacity-100">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Date pickers -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
                            class="theme-input w-full rounded-2xl px-4 py-3 text-xs font-semibold" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}"
                            class="theme-input w-full rounded-2xl px-4 py-3 text-xs font-semibold" required>
                    </div>
                </div>

                <!-- Reason Textarea -->
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Alasan Pengajuan</label>
                    <textarea name="reason" rows="3" placeholder="Tuliskan alasan perizinan Anda secara jelas..."
                        class="theme-input w-full rounded-2xl px-4 py-3 text-xs font-semibold" required>{{ old('reason') }}</textarea>
                </div>

                <!-- File Attachment Dropzone -->
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Dokumen Pendukung (Opsional)</label>
                    <div class="relative glass-card theme-border rounded-[22px] p-4 text-center cursor-pointer hover:bg-white/5 transition-colors duration-200">
                        <input type="file" name="attachment" id="attachment" accept="image/*" class="hidden" @change="handleFileSelect($event)">
                        
                        <!-- Image Preview display block -->
                        <div x-show="imagePreview" class="mb-3.5 max-w-[150px] mx-auto rounded-xl overflow-hidden border border-white/10 relative">
                            <img :src="imagePreview" class="w-full max-h-36 object-contain">
                            <button type="button" @click.stop="imagePreview = null; document.getElementById('attachment').value = ''"
                                    class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/70 text-white flex items-center justify-center text-xs">
                                ✕
                            </button>
                        </div>
                        
                        <label for="attachment" class="cursor-pointer block">
                            <svg class="w-8 h-8 mx-auto text-emerald-400 mb-2" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/>
                            </svg>
                            <p class="text-[9px] font-bold theme-text-main font-outfit uppercase">Pilih File Foto Bukti / Surat</p>
                            <p class="text-[8px] theme-text-muted mt-0.5 font-outfit">Maksimal file 5MB (JPG, PNG)</p>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="theme-btn-submit flex w-full items-center justify-center rounded-full py-3.5 text-xs font-bold tracking-wider uppercase font-outfit shadow-lg">
                    Kirim Pengajuan Izin
                </button>
            </form>
        </div>

    </div>
</x-layouts.mobile>
