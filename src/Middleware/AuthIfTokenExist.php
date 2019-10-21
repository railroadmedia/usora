<?php

namespace Railroad\Usora\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\JWTAuth;

class AuthIfTokenExist extends BaseMiddleware
{
    /**
     * AuthIfTokenExist constructor.
     *
     * @param JWTAuth $auth
     */
    public function __construct(JWTAuth $auth)
    {
        parent::__construct($auth);
    }

    /**
     * @param $request
     * @param Closure $next
     * @param null $guard
     * @return mixed
     * @throws JWTException
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->parser()
            ->setRequest($request)
            ->hasToken()) {
            try {
                $user =
                    $this->auth->parseToken()
                        ->getPayload()
                        ->get('sub');

                auth()->loginUsingId($user, false);
            } catch (Exception $exception) {

            }
        }

        return $next($request);
    }
}
