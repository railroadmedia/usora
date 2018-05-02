<?php

namespace Railroad\Usora\Middleware;

use Closure;
use Illuminate\Http\Request;
use Railroad\Usora\Services\ConfigService;

class AuthenticateViaThirdParty
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->check() && session()->get('skip-third-party-auth-check') != true &&
            !empty(ConfigService::$domainsToCheckForAuthenticateOn)) {
            session()->put('skip-third-party-auth-check', true);
            session()->reflash();

            session()->put('failure-redirect-url', $request->getUri());

            return redirect()->route('usora.authenticate.third-party');
        }

        if (session()->get('skip-third-party-auth-check') == true) {
            session()->forget('skip-third-party-auth-check');
        }

        return $next($request);
    }
}