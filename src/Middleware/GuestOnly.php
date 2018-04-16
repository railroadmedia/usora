<?php

namespace Railroad\Usora\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Railroad\Usora\Services\ConfigService;

class GuestOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return redirect(ConfigService::$loginSuccessRedirectPath);
        }

        return $next($request);
    }
}
