<?php

namespace Railroad\Usora\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class GuestOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)
            ->check()) {
            return redirect(config('usora.login_success_redirect_path'));
        }

        return $next($request);
    }
}
