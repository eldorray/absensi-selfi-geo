<x-layouts.guest
    title="Masuk"
    description="Masuk ke aplikasi presensi MI Daarul Hikmah."
    heading="Masuk ke AbsenKu"
    heading-id="login-heading"
    lead="Gunakan akun yang diberikan administrator untuk melanjutkan presensi."
>
    <x-slot:icon>
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <circle cx="12" cy="8" r="3.5"></circle>
            <path stroke-linecap="round" d="M5.5 20c.8-3.8 3-5.8 6.5-5.8s5.7 2 6.5 5.8"></path>
        </svg>
    </x-slot:icon>

    <section class="form-panel" aria-label="Form masuk">
        @if (session('status'))
            <p class="status-message" role="status">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <p class="error-message" role="alert">Periksa kembali email dan password Anda.</p>
        @endif

        <form action="{{ route('login') }}" method="POST" class="form">
            @csrf

            <x-layouts.guest.field name="email" type="email" label="Email" :value="old('email')" placeholder="nama@sekolah.sch.id" autocomplete="email" autofocus required />

            <x-layouts.guest.field name="password" type="password" label="Password" toggle-label="password" placeholder="Masukkan password" autocomplete="current-password" required />

            <div class="form-options">
                <label class="remember">
                    <input type="checkbox" name="remember" checked>
                    <span>Ingat saya (30 hari)</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-link" href="{{ route('password.request') }}">Lupa password?</a>
                @endif
            </div>

            <button type="submit" class="submit-button">Masuk</button>
        </form>
    </section>
</x-layouts.guest>
