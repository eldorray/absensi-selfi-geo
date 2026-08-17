<x-layouts.mobile title="Profil Saya" backUrl="{{ route('attendance.dashboard') }}" isSheet="true" showNav="true">
    <div class="space-y-4 pb-4">
        
        <!-- Profile Avatar Badge -->
        <div class="flex flex-col items-center justify-center py-3">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-green-400 to-emerald-500 p-[1.5px] shadow-lg">
                <div class="w-full h-full rounded-2xl bg-slate-950 flex items-center justify-center border border-white/10 overflow-hidden">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                        <span class="text-white text-xl font-black font-outfit uppercase">{{ $user->initials() }}</span>
                    @endif
                </div>
            </div>
            <h3 class="font-black text-sm theme-text-main font-display mt-3 leading-none">{{ auth()->user()->name }}</h3>
            <p class="text-[9px] theme-text-muted mt-1 uppercase font-bold tracking-wider font-outfit">{{ auth()->user()->role?->name ?? 'Pegawai' }}</p>
        </div>

        <!-- Update details form -->
        <div class="glass-card theme-border rounded-[24px] p-5">
            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-xs theme-status-ok-text text-left">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('attendance.profile.update') }}" method="POST" enctype="multipart/form-data"
                class="space-y-4 text-left" x-data="{ preview: null }">
                @csrf
                @method('PUT')

                <!-- Avatar Field -->
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Foto Profil</label>
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-green-400 to-emerald-500 p-[1.5px] shrink-0">
                            <div class="w-full h-full rounded-2xl bg-slate-950 flex items-center justify-center border border-white/10 overflow-hidden">
                                <template x-if="preview">
                                    <img :src="preview" alt="" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!preview">
                                    <span>
                                        @if ($user->avatar_url)
                                            <img src="{{ $user->avatar_url }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-white text-sm font-black font-outfit uppercase">{{ $user->initials() }}</span>
                                        @endif
                                    </span>
                                </template>
                            </div>
                        </div>
                        <label class="theme-input cursor-pointer rounded-2xl px-4 py-2.5 text-[11px] font-bold uppercase tracking-wide font-outfit theme-text-main">
                            Pilih Foto
                            <input type="file" name="avatar" accept="image/*" class="hidden"
                                @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null">
                        </label>
                    </div>
                    <p class="mt-1.5 text-[9px] theme-text-muted">JPG/PNG/WEBP, maks 8MB. Otomatis dikompres.</p>
                    @error('avatar')
                        <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name Field -->
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="theme-input w-full rounded-2xl px-4 py-3 text-xs font-semibold"
                        placeholder="Nama Lengkap Anda" required>
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Field -->
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="theme-input w-full rounded-2xl px-4 py-3 text-xs font-semibold"
                        placeholder="email@domain.com" required>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="theme-btn-submit flex w-full items-center justify-center rounded-[1.4rem] py-3.5 text-xs font-bold tracking-wider uppercase hover:scale-[1.01] active:scale-[0.99] font-outfit">
                    Simpan Perubahan
                </button>
            </form>
        </div>

        <!-- Account Info metadata cards -->
        <div class="glass-card theme-border rounded-[22px] p-4 text-left">
            <h3 class="font-black text-[10px] theme-text-muted font-outfit uppercase tracking-wider mb-3.5">Detail Informasi Instansi</h3>
            
            <div class="space-y-3.5 text-xs">
                <div class="flex justify-between items-center theme-border-b pb-2">
                    <span class="text-[10px] theme-text-muted uppercase font-semibold">Instansi/Kantor</span>
                    <span class="font-bold theme-text-main font-outfit">{{ auth()->user()->office?->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center theme-border-b pb-2">
                    <span class="text-[10px] theme-text-muted uppercase font-semibold">Peran Pengguna</span>
                    <span class="font-bold theme-text-main font-outfit">{{ auth()->user()->role?->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[10px] theme-text-muted uppercase font-semibold">Tanggal Bergabung</span>
                    <span class="font-bold theme-text-main font-outfit">{{ auth()->user()->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Settings Links (Change password) -->
        <a href="{{ route('attendance.password') }}"
            class="flex items-center justify-between glass-card theme-border rounded-[22px] p-4.5 hover:scale-[1.01] transition-transform duration-300">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl theme-icon-password flex items-center justify-center text-amber-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div class="text-left leading-none">
                    <span class="font-black text-xs theme-text-main font-display">Ganti Password Akun</span>
                    <p class="text-[9px] theme-text-muted mt-1">Kelola keamanan password akun Anda</p>
                </div>
            </div>
            
            <div class="theme-text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </div>
        </a>

    </div>
</x-layouts.mobile>
