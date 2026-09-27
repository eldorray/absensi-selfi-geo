<x-layouts.guest
    title="Konfirmasi Password"
    description="Konfirmasi password akun AbsenKu."
    heading="Konfirmasi Password"
    lead="Demi keamanan, masukkan password Anda sebelum melanjutkan."
>
    <x-slot:icon>
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linejoin="round" d="M12 3 5 6v5.5c0 4.3 2.9 8 7 9.5 4.1-1.5 7-5.2 7-9.5V6l-7-3Z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2.2 2.2L15.5 10"></path>
        </svg>
    </x-slot:icon>

    <section class="form-panel" aria-label="Form konfirmasi password">
        <form method="POST" action="{{ route('password.confirm') }}" class="form">
            @csrf

            <x-layouts.guest.field name="password" type="password" label="Password" toggle-label="password" placeholder="Masukkan password" autocomplete="current-password" autofocus required />

            <button type="submit" class="submit-button">Konfirmasi Password</button>
        </form>

        @if (Route::has('password.request'))
            <p class="panel-footer">
                <a class="text-link" href="{{ route('password.request') }}">Lupa password?</a>
            </p>
        @endif
    </section>
</x-layouts.guest>
