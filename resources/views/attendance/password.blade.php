<x-layouts.mobile title="Ganti Password" backUrl="{{ route('attendance.profile') }}">
    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif

    @if ($errors->any())
        <x-guru.notice tone="error" title="Password belum diganti" role="alert">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-guru.notice>
    @endif

    <form action="{{ route('attendance.password.update') }}" method="POST" class="g-card">
        @csrf
        @method('PUT')

        @foreach ([
            ['current_password', 'Password saat ini', 'current-password', 'Masukkan password saat ini'],
            ['password', 'Password baru', 'new-password', 'Minimal 8 karakter'],
            ['password_confirmation', 'Konfirmasi password baru', 'new-password', 'Ulangi password baru'],
        ] as [$name, $label, $autocomplete, $placeholder])
            <x-guru.field :label="$label" :for="$name" :error="$name" :required="true">
                <div class="relative" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" type="password" name="{{ $name }}" id="{{ $name }}" autocomplete="{{ $autocomplete }}"
                        placeholder="{{ $placeholder }}" class="g-input pr-14" required
                        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror>
                    <button type="button" @click="show = ! show" :aria-pressed="show.toString()" :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                        class="g-iconbtn absolute right-1 top-1/2 -translate-y-1/2 border-0 bg-transparent">
                        <x-guru.icon name="eye" x-show="! show" />
                        <x-guru.icon name="eye-off" x-show="show" x-cloak />
                    </button>
                </div>
            </x-guru.field>
        @endforeach

        <x-guru.button type="submit" class="g-btn--block">Simpan Password</x-guru.button>
    </form>

    <x-guru.notice title="Password yang kuat">
        <ul class="mt-1 list-disc pl-4">
            <li>Minimal 8 karakter.</li>
            <li>Campurkan huruf besar, huruf kecil, dan angka.</li>
            <li>Hindari nama atau tanggal lahir.</li>
        </ul>
    </x-guru.notice>
</x-layouts.mobile>
