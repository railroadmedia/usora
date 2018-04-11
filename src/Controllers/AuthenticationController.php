<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Usora\Services\ClientRelayService;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Services\UserService;

class AuthenticationController extends Controller
{
    use ThrottlesLogins;

    // the max login attempts allowed before lockout
    protected $maxAttempts = 8;

    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * CookieController constructor.
     * @param UserService $userService
     * @param Hasher $hasher
     */
    public function __construct(UserService $userService, Hasher $hasher)
    {
        $this->userService = $userService;
        $this->hasher = $hasher;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function authenticateViaCredentials(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|string',
                'password' => 'required|string',
            ]
        );

//        if ($this->hasTooManyLoginAttempts($request)) {
//            $this->fireLockoutEvent($request);
//
//            // todo: error and route
//            return redirect()->away(ConfigService::$loginPageUrl);
//        }

        if (auth()->attempt($request->only('email', 'password'), true)) {
            $user = $this->userService->getById(auth()->id());

            foreach (ConfigService::$domainsToAuthenticateOn as $domain) {
                ClientRelayService::authorizeUserOnDomain(
                    $user['id'],
                    $this->hasher->make($user['id'] . $user['password'] . $user['remember_token']),
                    $domain
                );
            }

            // todo: success
            return redirect()->away(ConfigService::$loginSuccessRedirectUrl);
        }

        $this->incrementLoginAttempts($request);

        // todo: error and route
        return redirect()->away(ConfigService::$loginPageUrl);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function authenticateViaToken(Request $request)
    {
        $request->validate(
            [
                'v' => 'required|string',
                'uid' => 'required|integer',
            ]
        );

        $verificationToken = $request->get('v');
        $userId = $request->get('uid');

        $user = $this->userService->getById($userId);

        if (!empty($user) &&
            $this->hasher->check($user['id'] . $user['password'] . $user['remember_token'], $verificationToken)) {

            auth()->loginUsingId($user['id']);
        }

        return response('');
    }
}