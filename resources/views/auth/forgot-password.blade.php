<x-layouts.guest
    title="Lupa Password"
    description="Halaman reset password aplikasi absensi digital MI Daarul Hikmah."
    heading="Lupa Password"
    :back-href="route('login')"
    back-label="Kembali ke login"
    lead="Masukkan email terdaftar. Kami akan mengirim link untuk mengatur ulang password."
>
    <x-slot:icon>
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <rect x="5" y="10.5" width="14" height="10" rx="2.5"></rect>
            <path stroke-linecap="round" d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5M12 14.5v2"></path>
        </svg>
    </x-slot:icon>

    <section class="form-panel" aria-label="Form lupa password">
        @if (session('status'))
            <p class="status-message" role="status">{{ session('status') }}</p>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="form">
            @csrf

            <x-layouts.guest.field name="email" type="email" label="Email" :value="old('email')" placeholder="Masukkan email terdaftar" autocomplete="email" autofocus required />

            <button type="submit" class="submit-button">Kirim Link Reset</button>
        </form>

        <p class="panel-footer">
            <a class="text-link" href="{{ route('login') }}">Kembali ke login</a>
        </p>
    </section>
</x-layouts.guest>
