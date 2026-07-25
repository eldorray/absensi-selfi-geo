<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Teachers should not have to retype their password every day, but a trusted
// device should not stay trusted forever either. Laravel's default is 400 days;
// hold it to 30. The cookie is not refreshed on later visits, so this is a hard
// expiry counted from the moment they log in.
test('ticking remember me keeps the teacher signed in for 30 days', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'remember' => 'on',
    ]);

    $this->assertAuthenticated();

    $name = Auth::guard('web')->getRecallerName();
    $cookie = collect($response->headers->getCookies())
        ->first(fn ($c) => $c->getName() === $name);

    expect($cookie)->not->toBeNull()
        ->and($cookie->getExpiresTime())
        ->toBeGreaterThan(now()->addDays(29)->timestamp)
        ->toBeLessThan(now()->addDays(31)->timestamp);
});

test('leaving remember me unticked issues no remember cookie', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $name = Auth::guard('web')->getRecallerName();
    $cookie = collect($response->headers->getCookies())
        ->first(fn ($c) => $c->getName() === $name);

    expect($cookie)->toBeNull();
});

// The 30 days only helps if teachers actually get it, so the box starts ticked.
test('the remember me box is ticked by default on the login screen', function () {
    $this->get('/login')
        ->assertStatus(200)
        ->assertSee('name="remember" checked', false);
});
