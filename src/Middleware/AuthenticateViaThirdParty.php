<?php

namespace Railroad\Usora\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AuthenticateViaThirdParty
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->check() &&
            session()->get('skip-third-party-auth-check') != true &&
            !empty(config('usora.domains_to_check_for_authentication'))) {

            session()->put('skip-third-party-auth-check', true);
            session()->reflash();

            session()->put('login-success-redirect-url', $request->getUri());

            return redirect()->route('usora.authenticate.with-third-party', $request->query());
        }

        if (session()->get('skip-third-party-auth-check') == true) {
            session()->forget('skip-third-party-auth-check');
        }

        return $next($request);
    }
}