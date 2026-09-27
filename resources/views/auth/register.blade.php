<x-layouts.guest
    title="Daftar"
    description="Daftar akun aplikasi presensi MI Daarul Hikmah."
    heading="Buat Akun Baru"
    lead="Daftar untuk mulai menggunakan aplikasi."
>
    <x-slot:icon>
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <circle cx="10" cy="8" r="3.5"></circle>
            <path stroke-linecap="round" d="M3.5 20c.8-3.8 3-5.8 6.5-5.8 1.6 0 2.9.4 4 1.2M18.5 13v6M15.5 16h6"></path>
        </svg>
    </x-slot:icon>

    <section class="form-panel" aria-label="Form pendaftaran">
        <form action="{{ route('register') }}" method="POST" class="form">
            @csrf

            <x-layouts.guest.field name="name" label="Nama Lengkap" :value="old('name')" placeholder="Masukkan nama lengkap" autocomplete="name" autofocus />

            <x-layouts.guest.field name="email" type="email" label="Email" :value="old('email')" placeholder="Masukkan email Anda" autocomplete="email" />

            <x-layouts.guest.field name="password" type="password" label="Password" toggle-label="password" placeholder="Minimal 8 karakter" autocomplete="new-password" />

            <x-layouts.guest.field name="password_confirmation" type="password" label="Konfirmasi Password" toggle-label="konfirmasi password" placeholder="Ulangi password" autocomplete="new-password" />

            <button type="submit" class="submit-button">Daftar Sekarang</button>
        </form>

        <p class="panel-footer">
            <span>Sudah punya akun?</span>
            <a class="text-link" href="{{ route('login') }}">Masuk</a>
        </p>
    </section>
</x-layouts.guest>
