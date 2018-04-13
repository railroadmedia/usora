<?php

namespace Railroad\Usora\Middleware;

use Closure;

class ThirdPartyAuthenticationCheck
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check() & session()->get('skip-third-party-auth-check') != true) {
            session()->put('skip-third-party-auth-check', true);
            session()->reflash();

            return redirect()->route('authenticate.third-party');
        }

        if (session()->get('skip-third-party-auth-check') == true) {
            session()->forget('skip-third-party-auth-check');
        }

        return $next($request);
    }
}