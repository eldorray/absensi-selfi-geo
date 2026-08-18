<x-layouts.app>
    <div class="settings-page">
        <header class="admin-page-header settings-page-header">
            <span class="admin-kicker">Identitas aplikasi</span>
            <h1>Branding aplikasi</h1>
            <p>Atur logo antarmuka, favicon, dan ikon instalasi PWA.</p>
        </header>

        <div class="settings-layout">
            @include('settings.partials.navigation')

            <section class="settings-content admin-glass-panel" aria-labelledby="branding-heading">
                @if (session('success'))
                    <div class="admin-alert-success rounded-2xl p-4 text-sm font-semibold">{{ session('success') }}</div>
                @endif

                <div class="settings-section">
                    <div class="settings-section-heading">
                        <span class="admin-tone-emerald settings-section-icon" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"></rect><circle cx="8" cy="10" r="2"></circle><path stroke-linecap="round" stroke-linejoin="round" d="m5 17 4-4 3 3 2-2 5 3"></path></svg>
                        </span>
                        <div><h2 id="branding-heading">Logo dan ikon</h2><p class="admin-muted">Logo membangun identitas antarmuka. Ikon persegi dipakai browser dan instalasi PWA.</p></div>
                    </div>

                    <form method="POST" action="{{ route('settings.branding.update') }}" enctype="multipart/form-data" class="settings-form">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-5 lg:grid-cols-2">
                            <div class="rounded-3xl bg-emerald-50 p-5 dark:bg-emerald-950/30">
                                <div class="mb-4 flex min-h-28 items-center justify-center rounded-2xl bg-white p-5 dark:bg-gray-900">
                                    @if ($settings->logoUrl())
                                        <img src="{{ $settings->logoUrl() }}" alt="Logo aplikasi saat ini" class="max-h-20 max-w-full object-contain">
                                    @else
                                        <span class="text-xl font-bold text-emerald-800 dark:text-emerald-200">{{ config('app.name') }}</span>
                                    @endif
                                </div>
                                <label for="application_logo" class="admin-label">Logo aplikasi</label>
                                <input id="application_logo" name="application_logo" type="file" accept="image/png,image/webp,image/svg+xml" class="admin-field p-3">
                                <p class="admin-hint">PNG, WebP, atau SVG. Maksimal 2 MB.</p>
                                @error('application_logo')<p class="admin-text-danger mt-1.5 text-xs">{{ $message }}</p>@enderror
                            </div>

                            <div class="rounded-3xl bg-slate-100 p-5 dark:bg-slate-900/60">
                                <div class="mb-4 flex min-h-28 items-center justify-center rounded-2xl bg-white p-5 dark:bg-gray-900">
                                    <img src="{{ $settings->iconUrl() }}" alt="Ikon aplikasi saat ini" class="size-20 rounded-2xl object-cover">
                                </div>
                                <label for="application_icon" class="admin-label">Ikon aplikasi</label>
                                <input id="application_icon" name="application_icon" type="file" accept="image/png" class="admin-field p-3">
                                <p class="admin-hint">PNG persegi minimal 512 × 512 piksel. Maksimal 2 MB.</p>
                                @error('application_icon')<p class="admin-text-danger mt-1.5 text-xs">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <button type="submit" class="admin-button-primary px-5 py-3">Simpan branding</button>
                    </form>
                </div>

                @if ($settings->application_logo_path || $settings->application_icon_path)
                    <div class="settings-danger-zone">
                        <div><h2>Kembalikan aset bawaan</h2><p class="admin-muted">Hapus logo dan ikon unggahan, lalu gunakan identitas bawaan aplikasi.</p></div>
                        <form method="POST" action="{{ route('settings.branding.destroy') }}" x-data="{}" @submit.prevent="$dispatch('admin-confirm', { title: 'Kembalikan Branding', message: 'Logo dan ikon unggahan akan dihapus.', confirmText: 'Kembalikan', variant: 'danger', form: $el })">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-button-danger px-5 py-3">Gunakan bawaan</button>
                        </form>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
