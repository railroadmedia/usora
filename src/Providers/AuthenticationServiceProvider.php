<?php

namespace Railroad\Usora\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Railroad\Usora\Guards\SaltedSessionGuard;

class AuthenticationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->provider(
            'usora',
            function () {
                return app()->make(UserServiceProvider::class);
            }
        );

        Auth::extend(
            'usora',
            function ($app, $name, array $config) {
                $guard = new SaltedSessionGuard(
                    $name,
                    Auth::createUserProvider($config['provider']),
                    $app['session.store']
                );

                if (method_exists($guard, 'setCookieJar')) {
                    $guard->setCookieJar($this->app['cookie']);
                }

                if (method_exists($guard, 'setDispatcher')) {
                    $guard->setDispatcher($this->app['events']);
                }

                if (method_exists($guard, 'setRequest')) {
                    $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
                }

                return $guard;
            }
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}