<?php

namespace App\Providers;

use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set remember me cookie duration (30 days by default)
        $this->setRememberMeDuration();
    }

    /**
     * Set the remember me cookie duration from config.
     */
    protected function setRememberMeDuration(): void
    {
        // Get the remember duration from config (in minutes)
        $rememberDuration = config('auth.remember', 43200); // 30 days default

        // After authentication resolves, set the remember duration
        $this->app->resolving('auth', function ($auth) use ($rememberDuration) {
            $auth->extend('session', function ($app, $name, $config) use ($rememberDuration) {
                $guard = new SessionGuard(
                    $name,
                    Auth::createUserProvider($config['provider'] ?? null),
                    $app['session.store'],
                );

                // Set the remember duration
                $guard->setRememberDuration($rememberDuration);

                // Set the cookie jar for the guard
                if (method_exists($guard, 'setCookieJar')) {
                    $guard->setCookieJar($app['cookie']);
                }

                // Set the event dispatcher
                if (method_exists($guard, 'setDispatcher')) {
                    $guard->setDispatcher($app['events']);
                }

                // Set the request
                if (method_exists($guard, 'setRequest')) {
                    $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
                }

                return $guard;
            });
        });
    }
}
