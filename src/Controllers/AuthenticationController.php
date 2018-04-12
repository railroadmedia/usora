<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Session\Middleware\StartSession;
use Railroad\Usora\Services\ClientRelayService;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Services\UserService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $this->middleware(
            [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                VerifyCsrfToken::class,
            ]
        );
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

            auth()->loginUsingId($user['id'], true);
        }

        return response('');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function authenticateViaThirdParty(Request $request)
    {
        if (!empty(auth()->user())) {
            if (!empty($request->get('success_redirect'))) {
                return redirect()->away($request->get('success_redirect'));
            }

            return redirect()->away(ConfigService::$loginSuccessRedirectUrl);
        }

        return view(
            'usora::authentication-check',
            [
                'loginSuccessRedirectUrl' => ConfigService::$loginSuccessRedirectUrl,
                'loginPageUrl' => ConfigService::$loginPageUrl
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function renderRememberTokenViaPostMessage(Request $request)
    {
        $user = auth()->user();

        if (empty($user)) {
            throw new NotFoundHttpException();
        }

        return view(
            'usora::post-message-remember-token',
            [
                'rememberToken' => $user['remember_token'],
                'userId' => $user['id'],
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function setAuthenticationCookieViaRememberToken(Request $request)
    {
        $request->validate(
            [
                'uid' => 'required|integer|exists:' . ConfigService::$tableUsers . ',id',
                'rt' => 'required|string',
            ]
        );

        $userId = $request->get('uid');
        $rememberMeToken = $request->get('rt');

        $user = $this->userService->getById($userId);

        if ($user['remember_token'] === $rememberMeToken) {
            auth()->loginUsingId($userId, true);

            return response()->json(['success' => 'true']);
        }

        return response()->json(['success' => 'false']);
    }
}