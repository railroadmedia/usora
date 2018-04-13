<?php

namespace Railroad\Usora\Middleware;

use Closure;

class ReFlashSession
{
    public function handle($request, Closure $next)
    {
        session()->reflash();

        return $next($request);
    }
}