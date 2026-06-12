<x-layouts.mobile title="Ganti Password" backUrl="{{ route('attendance.profile') }}" showNav="true">
    <div class="space-y-4 pb-4">
        
        <!-- Icon Banner -->
        <div class="flex flex-col items-center justify-center py-2">
            <div class="w-14 h-14 rounded-2xl theme-icon-password flex items-center justify-center text-amber-500 shadow-md">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <p class="text-[9px] theme-text-muted mt-2 uppercase font-bold tracking-wider font-outfit">Sandi Keamanan</p>
        </div>

        <!-- Form card -->
        <div class="glass-card theme-border rounded-[24px] p-5">
            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-xs theme-status-ok-text text-left font-bold">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-xs text-red-500 text-left font-medium">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('attendance.password.update') }}" method="POST" class="space-y-4 text-left">
                @csrf
                @method('PUT')

                <!-- Current Password -->
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Password Saat Ini</label>
                    <input type="password" name="current_password"
                        class="theme-input w-full rounded-2xl px-4 py-3 text-xs font-semibold"
                        placeholder="Masukkan password saat ini" required>
                </div>

                <!-- New Password -->
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Password Baru</label>
                    <input type="password" name="password"
                        class="theme-input w-full rounded-2xl px-4 py-3 text-xs font-semibold"
                        placeholder="Minimal 8 karakter" required>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation"
                        class="theme-input w-full rounded-2xl px-4 py-3 text-xs font-semibold"
                        placeholder="Ulangi password baru" required>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="theme-btn-submit flex w-full items-center justify-center rounded-[1.4rem] py-3.5 text-xs font-bold tracking-wider uppercase hover:scale-[1.01] active:scale-[0.99] font-outfit" style="background-color: var(--status-late-text); color: #fff; box-shadow: 0 10px 24px rgba(245, 158, 11, 0.15)">
                    Update Password
                </button>
            </form>
        </div>

        <!-- Security Tips Info Card -->
        <div class="glass-card theme-border rounded-[22px] p-4 text-left">
            <h3 class="font-black text-[10px] theme-text-muted font-outfit uppercase tracking-wider mb-3">Persyaratan Password Kuat</h3>
            
            <ul class="space-y-2 text-[10px] font-semibold theme-text-muted font-outfit">
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_6px_#10b981]"></span>
                    Gunakan minimal 8 karakter unik
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_6px_#10b981]"></span>
                    Kombinasikan huruf kapital, huruf kecil, dan angka
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_6px_#10b981]"></span>
                    Hindari penggunaan informasi pribadi (nama, tanggal lahir)
                </li>
            </ul>
        </div>

    </div>
</x-layouts.mobile>
