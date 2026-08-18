<x-layouts.app>
    <div class="settings-page">
        <header class="admin-page-header settings-page-header">
            <span class="admin-kicker">Pengaturan akun</span>
            <h1>{{ __('Update password') }}</h1>
            <p>{{ __('Ensure your account is using a long, random password to stay secure') }}</p>
        </header>

        <div class="settings-layout">
            @include('settings.partials.navigation')

            <section class="settings-content admin-glass-panel" aria-labelledby="password-form-heading">
                <div class="settings-section-heading">
                    <span class="admin-tone-violet settings-section-icon" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="5" y="10" width="14" height="10" rx="2"></rect>
                            <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                        </svg>
                    </span>
                    <div>
                        <h2 id="password-form-heading">Password akun</h2>
                        <p class="admin-muted">Gunakan password yang berbeda dari layanan lain.</p>
                    </div>
                </div>

                <form class="settings-form" action="{{ route('settings.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="admin-label" for="current_password">Current Password</label>
                        <input id="current_password" class="admin-field px-4 py-3 @error('current_password') border-red-500 @enderror" type="password" name="current_password" autocomplete="current-password" required>
                        @error('current_password')<p class="admin-text-danger mt-1.5 text-xs">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="admin-label" for="password">New Password</label>
                        <input id="password" class="admin-field px-4 py-3 @error('password') border-red-500 @enderror" type="password" name="password" autocomplete="new-password" required>
                        @error('password')<p class="admin-text-danger mt-1.5 text-xs">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="admin-label" for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" class="admin-field px-4 py-3" type="password" name="password_confirmation" autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="admin-button-primary px-5 py-3">{{ __('Update Password') }}</button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.app>
