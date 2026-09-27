<x-layouts.mobile title="Profil Saya" backUrl="{{ route('attendance.dashboard') }}">
    <x-guru.card class="items-center text-center">
        <span class="g-avatar g-avatar--xl">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="">
            @else
                {{ $user->initials() }}
            @endif
        </span>
        <div class="flex flex-col gap-1">
            <p class="g-card__title">{{ $user->name }}</p>
            <p class="text-sm text-guru-muted">{{ $user->role?->name ?? 'Pegawai' }} · {{ $user->office?->name ?? '-' }}</p>
        </div>
    </x-guru.card>

    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif

    <form action="{{ route('attendance.profile.update') }}" method="POST" enctype="multipart/form-data" class="g-card" x-data="{ preview: null }">
        @csrf
        @method('PUT')
        <h2 class="g-h2">Data diri</h2>

        <div class="g-field">
            <span class="g-label">Foto profil</span>
            <div class="flex items-center gap-3">
                <span class="g-avatar">
                    <template x-if="preview"><img :src="preview" alt=""></template>
                    <template x-if="! preview">
                        <span class="contents">
                            @if ($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="">
                            @else
                                {{ $user->initials() }}
                            @endif
                        </span>
                    </template>
                </span>
                <label class="g-btn g-btn--secondary g-btn--sm cursor-pointer has-[input:focus-visible]:outline-3 has-[input:focus-visible]:outline-guru-primary">
                    <x-guru.icon name="image" /> Pilih foto
                    <input type="file" name="avatar" accept="image/*" class="sr-only" aria-describedby="avatar-hint"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null">
                </label>
            </div>
            <p id="avatar-hint" class="g-hint">JPG, PNG, atau WEBP, maksimal 8 MB. Otomatis dikompres.</p>
            @error('avatar')
                <p class="g-error">{{ $message }}</p>
            @enderror
        </div>

        <x-guru.field label="Nama lengkap" for="name" error="name" :required="true">
            <input type="text" name="name" id="name" autocomplete="name" value="{{ old('name', $user->name) }}" class="g-input" required
                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
        </x-guru.field>

        <x-guru.field label="Alamat email" for="email" error="email" :required="true">
            <input type="email" name="email" id="email" autocomplete="email" value="{{ old('email', $user->email) }}" class="g-input" required
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
        </x-guru.field>

        <x-guru.button type="submit" class="g-btn--block">Simpan Perubahan</x-guru.button>
    </form>

    <section class="g-card" aria-labelledby="judul-tema" x-data="{ mode: window.guruThemeMode() }">
        <h2 id="judul-tema" class="g-h2">Tema tampilan</h2>
        <div class="g-seg" role="radiogroup" aria-labelledby="judul-tema">
            @foreach (['light' => ['Terang', 'sun'], 'dark' => ['Gelap', 'moon'], 'system' => ['Ikut sistem', 'monitor']] as $value => [$label, $icon])
                <label>
                    <input type="radio" name="appearance" value="{{ $value }}" x-model="mode" @change="guruSetTheme(mode)">
                    <x-guru.icon :name="$icon" />
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </section>

    <x-guru.list>
        <x-guru.list-item :href="route('attendance.password')" icon="lock" tone="neutral" title="Ganti Password" desc="Kelola kata sandi akun" />
    </x-guru.list>

    <x-guru.card as="div">
        <h2 class="g-h2">Akun</h2>
        <dl class="g-dl">
            <div><dt>Instansi</dt><dd>{{ $user->office?->name ?? '-' }}</dd></div>
            <div><dt>Peran</dt><dd>{{ $user->role?->name ?? '-' }}</dd></div>
            <div><dt>Bergabung</dt><dd>{{ $user->created_at?->locale('id')->isoFormat('D MMMM Y') }}</dd></div>
        </dl>
    </x-guru.card>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-guru.button type="submit" variant="secondary" icon="logout" class="g-btn--block">Keluar</x-guru.button>
    </form>
</x-layouts.mobile>
