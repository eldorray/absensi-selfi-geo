<x-layouts.app>
    <div class="settings-page">
        <header class="admin-page-header settings-page-header">
            <span class="admin-kicker">Pengaturan akun</span>
            <h1>{{ __('Profile') }}</h1>
            <p>{{ __('Update your name and email address') }}</p>
        </header>

        <div class="settings-layout">
            @include('settings.partials.navigation')

            <section class="settings-content admin-glass-panel" aria-labelledby="profile-form-heading">
                <div class="settings-section">
                    <div class="settings-section-heading">
                        <span class="admin-tone-emerald settings-section-icon" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="3.5"></circle>
                                <path stroke-linecap="round" d="M5.5 20c.8-3.8 3-5.8 6.5-5.8s5.7 2 6.5 5.8"></path>
                            </svg>
                        </span>
                        <div>
                            <h2 id="profile-form-heading">Informasi profil</h2>
                            <p class="admin-muted">Nama dan email yang digunakan untuk akun Anda.</p>
                        </div>
                    </div>

                    <form class="settings-form" action="{{ route('settings.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="admin-label" for="name">Name</label>
                            <input id="name" class="admin-field px-4 py-3 @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                            @error('name')<p class="admin-text-danger mt-1.5 text-xs">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="admin-label" for="email">Email</label>
                            <input id="email" class="admin-field px-4 py-3 @error('email') border-red-500 @enderror" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                            @error('email')<p class="admin-text-danger mt-1.5 text-xs">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="admin-button-primary px-5 py-3">{{ __('Save') }}</button>
                    </form>
                </div>

                <div class="settings-danger-zone">
                    <div>
                        <h2>{{ __('Delete account') }}</h2>
                        <p class="admin-muted">{{ __('Delete your account and all of its resources') }}</p>
                    </div>
                    <form action="{{ route('settings.profile.destroy') }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete your account?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-button-danger px-5 py-3">{{ __('Delete account') }}</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
