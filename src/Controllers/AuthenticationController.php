<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Session\Middleware\StartSession;
use Railroad\Usora\Guards\SaltedSessionGuard;
use Railroad\Usora\Middleware\ReFlashSession;
use Railroad\Usora\Services\ClientRelayService;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Services\UserService;
use Symfony\Component\HttpFoundation\Response;
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
                ReFlashSession::class,
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

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return redirect()->to(ConfigService::$loginPagePath)
                ->withErrors(['Too many login attempts. Try again later.']);
        }

        if (auth()->attempt($request->only('email', 'password'), ConfigService::$rememberMe)) {
            $user = $this->userService->getById(auth()->id());

            foreach (ConfigService::$domainsToAuthenticateOn as $domain) {
                ClientRelayService::authorizeUserOnDomain(
                    $user['id'],
                    $this->hasher->make($user['id'] . $user['password'] . $user['session_salt']),
                    $domain
                );
            }

            return redirect()->away(ConfigService::$loginSuccessRedirectPath);
        }

        $this->incrementLoginAttempts($request);

        return redirect()->to(ConfigService::$loginPagePath)
            ->withErrors(['Invalid authentication credentials, please try again.']);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function authenticateViaVerificationToken(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                'vt' => 'required|string',
                'uid' => 'required|integer',
            ]
        );

        if ($validator->fails()) {
            return response('');
        }

        $verificationToken = $request->get('vt');
        $userId = $request->get('uid');

        $user = $this->userService->getById($userId);

        if (!empty($user) &&
            $this->hasher->check($user['id'] . $user['password'] . $user['session_salt'], $verificationToken)) {

            SaltedSessionGuard::$updateSalt = false;

            auth()->loginUsingId($user['id'], ConfigService::$rememberMe);
        }

        return response('');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function authenticateViaThirdParty(Request $request)
    {
        session()->put('skip-third-party-auth-check', true);

        if (!empty(auth()->user())) {
            if (!empty($request->get('success_redirect'))) {
                return redirect()->away($request->get('success_redirect'));
            }

            return redirect()->to(ConfigService::$loginSuccessRedirectPath);
        }

        return view(
            'usora::authentication-check',
            [
                'loginSuccessRedirectUrl' => url()->to(ConfigService::$loginSuccessRedirectPath),
                'loginPageUrl' => url()->to(ConfigService::$loginPagePath)
            ]
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function renderVerificationTokenViaPostMessage(Request $request)
    {
        $user = auth()->user();

        if (empty($user)) {
            return view(
                'usora::post-message-verification-token',
                [
                    'failed' => true,
                    'token' => null,
                    'userId' => null,
                ]
            );
        }

        return view(
            'usora::post-message-verification-token',
            [
                'failed' => false,
                'token' => $this->hasher->make($user['id'] . $user['password'] . $user['session_salt']),
                'userId' => $user['id'],
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setAuthenticationCookieViaVerificationToken(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                'uid' => 'required|integer|exists:' . ConfigService::$tableUsers . ',id',
                'vt' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response('');
        }

        $userId = $request->get('uid');
        $verificationToken = $request->get('vt');

        $user = $this->userService->getById($userId);

        if ($this->hasher->check($user['id'] . $user['password'] . $user['session_salt'], $verificationToken)) {
            auth()->loginUsingId($userId, ConfigService::$rememberMe);

            return response()->json(['success' => 'true']);
        }

        return response()->json(['success' => 'false']);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function deauthenticate(Request $request)
    {
        $user = auth()->user();

        if (empty($user)) {
            throw new NotFoundHttpException();
        }

        auth()->logout();

        return redirect()->to(ConfigService::$loginPagePath);
    }

    /**
     * @return string
     */
    public function username()
    {
        return 'email';
    }
}