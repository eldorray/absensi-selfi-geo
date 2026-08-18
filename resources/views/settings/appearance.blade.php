<x-layouts.app>
    <div class="settings-page">
        <header class="admin-page-header settings-page-header">
            <span class="admin-kicker">Pengaturan akun</span>
            <h1>{{ __('Appearance') }}</h1>
            <p>{{ __('Update the appearance settings for your account') }}</p>
        </header>

        <div class="settings-layout">
            @include('settings.partials.navigation')

            <section class="settings-content admin-glass-panel" aria-labelledby="appearance-heading">
                <div class="settings-section-heading">
                    <span class="admin-tone-sky settings-section-icon" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z"></path>
                        </svg>
                    </span>
                    <div>
                        <h2 id="appearance-heading">Tema antarmuka</h2>
                        <p class="admin-muted">Pilih tema terang, gelap, atau ikuti pengaturan perangkat.</p>
                    </div>
                </div>

                <div class="settings-theme-options" role="group" aria-label="Theme">
                    <button type="button" value="light" data-appearance="light" onclick="setAppearance('light')" class="settings-theme-option">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="4"></circle>
                            <path stroke-linecap="round" d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path>
                        </svg>
                        <span>{{ __('Light') }}</span>
                    </button>
                    <button type="button" value="dark" data-appearance="dark" onclick="setAppearance('dark')" class="settings-theme-option">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z"></path>
                        </svg>
                        <span>{{ __('Dark') }}</span>
                    </button>
                    <button type="button" value="system" data-appearance="system" onclick="setAppearance('system')" class="settings-theme-option">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="13" rx="2"></rect>
                            <path stroke-linecap="round" d="M8 21h8m-4-4v4"></path>
                        </svg>
                        <span>{{ __('System') }}</span>
                    </button>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
