<?php

use App\Models\ApplicationSetting;

it('keeps uploaded logo and application title in one centered brand lockup', function () {
    ApplicationSetting::current()->update(['application_logo_path' => 'branding/logo.png']);

    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('class="brand-lockup"', false)
        ->assertSee('class="brand-logo"', false)
        ->assertSee('class="app-title"', false)
        ->assertSee('AbsenKu')
        ->assertSee('grid-template-columns: 48px minmax(0, 1fr) 48px', false);
});

it('renders the Material 3 login page with accessible controls', function () {
    $response = $this->get(route('login'));

    $response->assertSuccessful()
        ->assertSee('Masuk ke AbsenKu')
        ->assertSee('action="'.route('login').'"', false)
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="remember" checked', false)
        ->assertSee('autocomplete="email"', false)
        ->assertSee('autocomplete="current-password"', false)
        ->assertSee('class="phone-shell"', false)
        ->assertSee('class="dynamic-island"', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('data-password-toggle', false)
        ->assertSee('aria-label="Tampilkan password"', false)
        ->assertDontSee('animate-blob')
        ->assertDontSee('bg-grid-overlay')
        ->assertDontSee('glass-card');
});

it('renders validation feedback using an alert state', function () {
    $response = $this->from(route('login'))->post(route('login'), [
        'email' => 'bukan-email',
        'password' => '',
    ]);

    $response->assertRedirect(route('login'));

    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('role="alert"', false)
        ->assertSee('Periksa kembali email dan password Anda.');
});
