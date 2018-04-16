<?php

namespace Railroad\Usora\Middleware;

use Closure;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ViewErrorBag;

class ShareAuthenticatedUserInViews
{
    /**
     * The view factory implementation.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * Create a new error binder instance.
     *
     * @param  \Illuminate\Contracts\View\Factory $view
     * @return void
     */
    public function __construct(Factory $view)
    {
        $this->view = $view;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // all views will have access to the logged in user object
        if (!empty(auth()->user())) {
            $this->view->share(
                'user',
                auth()->user() ?: new ViewErrorBag
            );
        }

        return $next($request);
    }
}
