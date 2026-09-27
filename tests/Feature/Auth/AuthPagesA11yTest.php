<?php

it('renders the forgot password page zoomable with labelled, autocompleting inputs', function () {
    $this->get(route('password.request'))
        ->assertSuccessful()
        ->assertDontSee('user-scalable=no', false)
        ->assertDontSee('maximum-scale=1.0', false)
        ->assertSee('autocomplete="email"', false)
        ->assertSee('for="email"', false)
        ->assertSee('id="email"', false)
        ->assertSee('aria-label="Aktifkan tema gelap"', false)
        ->assertSee('prefers-reduced-motion: reduce', false)
        ->assertDontSee('scale(0.92)', false);
});

it('marks the forgot password email as invalid when validation fails', function () {
    $this->from(route('password.request'))
        ->followingRedirects()
        ->post(route('password.email'), ['email' => 'bukan-email'])
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('aria-describedby="email-error"', false)
        ->assertSee('id="email-error"', false);
});

it('renders the reset password page zoomable with labelled, autocompleting inputs', function () {
    $this->get(route('password.reset', ['token' => 'dummy-token']))
        ->assertSuccessful()
        ->assertDontSee('user-scalable=no', false)
        ->assertSee('autocomplete="email"', false)
        ->assertSee('autocomplete="new-password"', false)
        ->assertSee('for="email"', false)
        ->assertSee('for="password"', false)
        ->assertSee('for="password_confirmation"', false)
        ->assertSee('aria-label="Aktifkan tema gelap"', false)
        ->assertDontSee('scale(0.92)', false);
});

it('renders the register page zoomable with labelled, autocompleting inputs', function () {
    $this->get(route('register'))
        ->assertSuccessful()
        ->assertDontSee('user-scalable=no', false)
        ->assertSee('autocomplete="name"', false)
        ->assertSee('autocomplete="email"', false)
        ->assertSee('autocomplete="new-password"', false)
        ->assertSee('for="name"', false)
        ->assertSee('for="email"', false)
        ->assertSee('for="password"', false)
        ->assertSee('for="password_confirmation"', false);
});

it('renders every guest auth page on the shared layout with the appearance theme contract', function (string $uri) {
    $this->get($uri)
        ->assertSuccessful()
        ->assertSee('<html lang="id" data-theme="light">', false)
        ->assertSee('data-auth-layout="guest"', false)
        ->assertSee('--md-sys-color-primary: #176b43', false)
        ->assertSee("localStorage.getItem('appearance') ?? localStorage.getItem('welcome-theme')", false)
        ->assertSee("localStorage.setItem('appearance'", false)
        ->assertSee("matchMedia('(prefers-color-scheme: dark)')", false)
        ->assertSee('data-theme-toggle', false)
        ->assertDontSee("localStorage.setItem('welcome-theme'", false)
        ->assertDontSee('animate-blob', false)
        ->assertDontSee('bg-grid-overlay', false)
        ->assertDontSee('glass-card', false)
        ->assertDontSee('text-sky-600', false);
})->with([
    'login' => '/login',
    'forgot password' => '/forgot-password',
    'reset password' => '/reset-password/dummy-token',
    'register' => '/register',
]);

it('renders the authenticated auth pages on the shared layout', function (string $uri) {
    $user = \App\Models\User::factory()->unverified()->create();

    $this->actingAs($user)->get($uri)
        ->assertSuccessful()
        ->assertSee('<html lang="id"', false)
        ->assertSee('data-auth-layout="guest"', false)
        ->assertDontSee('x-data', false);
})->with([
    'verify email' => '/verify-email',
    'confirm password' => '/confirm-password',
]);

it('wires reset password visibility toggles to their inputs', function () {
    $this->get(route('password.reset', ['token' => 'dummy-token']))
        ->assertSee('aria-controls="password"', false)
        ->assertSee('aria-controls="password_confirmation"', false)
        ->assertSee('aria-label="Tampilkan konfirmasi password"', false)
        ->assertSee('aria-pressed="false"', false);
});
