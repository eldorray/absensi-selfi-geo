<x-layouts.mobile title="Ajukan Izin" backUrl="{{ route('attendance.leaves.index') }}" showNav="true">
    <div class="space-y-4 pb-4">
        
        <!-- Form card -->
        <div class="glass-card theme-border rounded-[24px] p-5 text-left">
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-xs text-red-500 font-medium">
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
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="izin" class="peer sr-only"
                                {{ old('type', 'izin') == 'izin' ? 'checked' : '' }} required>
                            <div class="p-3 text-center rounded-2xl glass-card border-2 theme-border peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 hover:bg-white/5 active:scale-98 transition-all flex flex-col items-center justify-center">
                                <svg class="w-5.5 h-5.5 text-cyan-500 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-[10px] font-bold theme-text-main font-outfit">Izin</span>
                            </div>
                            <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex h-5 w-5 scale-0 items-center justify-center rounded-full bg-cyan-500 text-white opacity-0 shadow-lg transition-all peer-checked:scale-100 peer-checked:opacity-100">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </label>

                        <!-- Cuti Tab -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="cuti" class="peer sr-only"
                                {{ old('type') == 'cuti' ? 'checked' : '' }}>
                            <div class="p-3 text-center rounded-2xl glass-card border-2 theme-border peer-checked:border-purple-500 peer-checked:bg-purple-500/10 hover:bg-white/5 active:scale-98 transition-all flex flex-col items-center justify-center">
                                <svg class="w-5.5 h-5.5 text-purple-500 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-[10px] font-bold theme-text-main font-outfit">Cuti</span>
                            </div>
                            <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex h-5 w-5 scale-0 items-center justify-center rounded-full bg-purple-500 text-white opacity-0 shadow-lg transition-all peer-checked:scale-100 peer-checked:opacity-100">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </label>

                        <!-- Sakit Tab -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="sakit" class="peer sr-only"
                                {{ old('type') == 'sakit' ? 'checked' : '' }}>
                            <div class="p-3 text-center rounded-2xl glass-card border-2 theme-border peer-checked:border-red-500 peer-checked:bg-red-500/10 hover:bg-white/5 active:scale-98 transition-all flex flex-col items-center justify-center">
                                <svg class="w-5.5 h-5.5 text-red-500 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                </svg>
                                <span class="text-[10px] font-bold theme-text-main font-outfit">Sakit</span>
                            </div>
                            <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex h-5 w-5 scale-0 items-center justify-center rounded-full bg-red-500 text-white opacity-0 shadow-lg transition-all peer-checked:scale-100 peer-checked:opacity-100">
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
                            class="theme-input w-full rounded-2xl px-4.5 py-3 text-xs font-semibold">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}"
                            class="theme-input w-full rounded-2xl px-4.5 py-3 text-xs font-semibold">
                    </div>
                </div>

                <!-- Reason Textarea -->
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Alasan Pengajuan</label>
                    <textarea name="reason" rows="3" placeholder="Tuliskan alasan perizinan Anda secara jelas..."
                        class="theme-input w-full rounded-2xl px-4.5 py-3 text-xs font-semibold" required>{{ old('reason') }}</textarea>
                </div>

                <!-- File Attachment Dropzone -->
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Dokumen Pendukung (Opsional)</label>
                    <div class="relative glass-card theme-border rounded-[22px] p-4 text-center cursor-pointer hover:bg-white/5 transition-colors duration-200">
                        <input type="file" name="attachment" id="attachment" accept="image/*" class="hidden">
                        
                        <!-- Image Preview display block -->
                        <div id="preview" class="hidden mb-3.5 max-w-[150px] mx-auto rounded-xl overflow-hidden border border-white/10">
                            <img id="preview-image" class="w-full max-h-36 object-contain">
                        </div>
                        
                        <label for="attachment" class="cursor-pointer block">
                            <svg class="w-8 h-8 mx-auto text-cyan-500 mb-2" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/>
                            </svg>
                            <p class="text-[9px] font-bold theme-text-main font-outfit uppercase">Pilih File Foto Bukti</p>
                            <p class="text-[8px] theme-text-muted mt-1 font-outfit">Maksimal file 5MB (JPG, PNG)</p>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="theme-btn-submit flex w-full items-center justify-center rounded-[1.4rem] py-3.5 text-xs font-bold tracking-wider uppercase hover:scale-[1.01] active:scale-[0.99] font-outfit">
                    Kirim Pengajuan Izin
                </button>
            </form>
        </div>

    </div>

    <!-- Script for Handling Attachment Image Preview -->
    <script>
        document.getElementById('attachment').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.getElementById('preview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-layouts.mobile>
