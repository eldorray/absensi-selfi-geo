<x-layouts.guest
    title="Verifikasi Email"
    description="Verifikasi alamat email akun AbsenKu."
    heading="Verifikasi Email Anda"
    lead="Sebelum melanjutkan, buka email Anda dan klik link verifikasi. Jika belum menerima email, kirim ulang di bawah."
>
    <x-slot:icon>
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <rect x="3" y="5.5" width="18" height="13" rx="2.5"></rect>
            <path stroke-linecap="round" stroke-linejoin="round" d="m4 7.5 8 6 8-6"></path>
        </svg>
    </x-slot:icon>

    <section class="form-panel" aria-label="Kirim ulang verifikasi email">
        @if (session('status') === 'verification-link-sent')
            <p class="status-message" role="status">Link verifikasi baru telah dikirim ke alamat email Anda.</p>
        @endif

        <form method="POST" action="{{ route('verification.store') }}" class="form">
            @csrf
            <button type="submit" class="submit-button">Kirim Ulang Email Verifikasi</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="panel-footer">
            @csrf
            <button type="submit" class="text-link">Keluar</button>
        </form>
    </section>
</x-layouts.guest>
