<x-layouts.guest
    title="Atur Ulang Password"
    description="Halaman atur ulang password aplikasi absensi digital MI Daarul Hikmah."
    heading="Atur Ulang Password"
    :back-href="route('login')"
    back-label="Kembali ke login"
    lead="Silakan isi password baru Anda."
>
    <x-slot:icon>
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <circle cx="8" cy="15" r="4"></circle>
            <path stroke-linecap="round" stroke-linejoin="round" d="m10.8 12.2 8.7-8.7M16.5 6.5l2.5 2.5M14 9l2 2"></path>
        </svg>
    </x-slot:icon>

    <section class="form-panel" aria-label="Form atur ulang password">
        <form action="{{ route('password.store') }}" method="POST" class="form">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <x-layouts.guest.field name="email" type="email" label="Email" :value="old('email', request('email'))" placeholder="Masukkan email Anda" autocomplete="email" required />

            <x-layouts.guest.field name="password" type="password" label="Password Baru" toggle-label="password" placeholder="Minimal 8 karakter" autocomplete="new-password" required />

            <x-layouts.guest.field name="password_confirmation" type="password" label="Konfirmasi Password" toggle-label="konfirmasi password" placeholder="Konfirmasi password baru" autocomplete="new-password" required />

            <button type="submit" class="submit-button">Simpan Password Baru</button>
        </form>

        <p class="panel-footer">
            <a class="text-link" href="{{ route('login') }}">Kembali ke login</a>
        </p>
    </section>
</x-layouts.guest>
