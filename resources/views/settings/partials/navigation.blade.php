<aside class="settings-navigation w-full shrink-0 md:w-64" aria-label="Pengaturan akun">
    <nav class="settings-navigation-surface">
        <a href="{{ route('settings.profile.edit') }}" @class([
            'settings-navigation-item',
            'is-active' => request()->routeIs('settings.profile.*'),
        ]) @if (request()->routeIs('settings.profile.*')) aria-current="page" @endif>
            <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="3.5"></circle>
                <path stroke-linecap="round" d="M5.5 20c.8-3.8 3-5.8 6.5-5.8s5.7 2 6.5 5.8"></path>
            </svg>
            <span>{{ __('Profile') }}</span>
        </a>
        <a href="{{ route('settings.password.edit') }}" @class([
            'settings-navigation-item',
            'is-active' => request()->routeIs('settings.password.*'),
        ]) @if (request()->routeIs('settings.password.*')) aria-current="page" @endif>
            <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <rect x="5" y="10" width="14" height="10" rx="2"></rect>
                <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"></path>
            </svg>
            <span>{{ __('Password') }}</span>
        </a>
        <a href="{{ route('settings.appearance.edit') }}" @class([
            'settings-navigation-item',
            'is-active' => request()->routeIs('settings.appearance.*'),
        ]) @if (request()->routeIs('settings.appearance.*')) aria-current="page" @endif>
            <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z"></path>
            </svg>
            <span>{{ __('Appearance') }}</span>
        </a>
        @if (auth()->user()?->isAdmin())
            <a href="{{ route('settings.branding.edit') }}" @class([
                'settings-navigation-item',
                'is-active' => request()->routeIs('settings.branding.*'),
            ]) @if (request()->routeIs('settings.branding.*')) aria-current="page" @endif>
                <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <rect x="4" y="4" width="16" height="16" rx="4"></rect>
                    <path stroke-linecap="round" d="M8 15 11 12l2 2 3-4"></path>
                </svg>
                <span>Branding</span>
            </a>
        @endif
    </nav>
</aside>
