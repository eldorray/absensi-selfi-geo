<?php

it('renders the forgot password page zoomable with labelled, autocompleting inputs', function () {
    $this->get(route('password.request'))
        ->assertSuccessful()
        ->assertDontSee('user-scalable=no', false)
        ->assertDontSee('maximum-scale=1.0', false)
        ->assertSee('autocomplete="email"', false)
        ->assertSee('for="email"', false)
        ->assertSee('id="email"', false)
        ->assertSee('aria-label="Ganti Tema"', false)
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
        ->assertSee('aria-label="Ganti Tema"', false)
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
