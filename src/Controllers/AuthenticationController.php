<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Illuminate\Auth\Recaller;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use MikeMcLin\WpPassword\Facades\WpPassword;
use Railroad\Usora\Entities\RememberToken;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Events\UserEvent;
use Railroad\Usora\Guards\SaltedSessionGuard;
use Railroad\Usora\Managers\UsoraEntityManager;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Services\ClientRelayService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthenticationController extends Controller
{
    use ThrottlesLogins;
    use ValidatesRequests;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Hasher
     */
    private $hasher;

    // the max login attempts allowed before lockout
    protected $maxAttempts = 6;

    /**
     * CookieController constructor.
     *
     * @param EntityManager $entityManager
     * @param Hasher $hasher
     */
    public function __construct(UsoraEntityManager $entityManager, Hasher $hasher)
    {
        $this->hasher = $hasher;
        $this->entityManager = $entityManager;

        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function authenticateViaCredentials(Request $request)
    {
        try {
            $this->validate(
                $request,
                [
                    'email' => 'required|string',
                    'password' => 'required|string',
                ]
            );
        } catch (ValidationException $exception) {
            session()->put('skip-third-party-auth-check', true);

            return $request->has('redirect') ?
                redirect()
                    ->to(config('usora.login_page_path') . '?redirect_to=' . $request->get('redirect'))
                    ->withErrors($exception->errors()) :
                redirect()
                    ->to(config('usora.login_page_path'))
                    ->withErrors($exception->errors());
        }

        $remember = false;

        if (config('usora.force_remember', false) == true || (boolean)$request->get('remember', false) == true) {
            $remember = true;
        }

        $request->attributes->set('remember', $remember);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $errors = ['throttle' => 'Too many login attempts. Try again later.'];

            session()->put('skip-third-party-auth-check', true);

            return $request->has('redirect') ?
                redirect()
                    ->to(config('usora.login_page_path') . '?redirect_to=' . $request->get('redirect'))
                    ->withErrors($errors) :
                redirect()
                    ->to(config('usora.login_page_path'))
                    ->withErrors($errors);
        }

        if (auth()->attempt($request->only('email', 'password'), $remember)) {
            $user = $this->userRepository->find(auth()->id());

            foreach (config('usora.domains_to_authenticate_on') as $domain) {
                ClientRelayService::authorizeUserOnDomain(
                    $user->getId(),
                    $this->hasher->make($user->getId() . $user->getPassword() . $user->getSessionSalt()),
                    $domain
                );
            }

            event(new UserEvent($user->getId(), 'authenticated'));

            $redirect =
                $request->has('redirect') ? $request->get('redirect') : config('usora.login_success_redirect_path');

            return redirect()->away($redirect);
        } else {
            $userByEmail = $this->userRepository->findOneBy(['email' => $request->get('email')]);

            if (!is_null($userByEmail)) {
                if (WpPassword::check(trim($request->get('password')), $userByEmail->getPassword())) {

                    auth()->loginUsingId($userByEmail->getId(), $remember);

                    foreach (config('usora.domains_to_authenticate_on') as $domain) {
                        ClientRelayService::authorizeUserOnDomain(
                            $userByEmail->getId(),
                            $this->hasher->make(
                                $userByEmail->getId() . $userByEmail->getPassword() . $userByEmail->getSessionSalt()
                            ),
                            $domain
                        );
                    }

                    event(new UserEvent($userByEmail->getId(), 'authenticated'));

                    $redirect = $request->has('redirect') ? $request->get('redirect') :
                        config('usora.login_success_redirect_path');

                    return redirect()->away($redirect);
                }
            }
        }

        $this->incrementLoginAttempts($request);

        $errors = ['invalid-credentials' => 'Invalid authentication credentials, please try again.'];

        session()->put('skip-third-party-auth-check', true);

        return $request->has('redirect') ?
            redirect()
                ->to(config('usora.login_page_path') . '?redirect_to=' . $request->get('redirect'))
                ->withErrors($errors) :
            redirect()
                ->to(config('usora.login_page_path'))
                ->withErrors($errors);
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

        $user = $this->userRepository->find($userId);

        if (!empty($user) &&
            $this->hasher->check($user->getId() . $user->getPassword() . $user->getSessionSalt(), $verificationToken)) {

            SaltedSessionGuard::$updateSalt = false;

            auth()->loginUsingId($user->getId(), $request->get('remember', false));
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

            return redirect()->to(config('usora.login_success_redirect_path'));
        }

        $domains = config('usora.domains_to_check_for_authentication');
        $currentSubdomain = array_reverse(explode('.', $request->getHttpHost()))[2] ?? '';

        foreach ($domains as $domainIndex => $domain) {
            $explodedDomain = explode('.', $domain);

            if (count($explodedDomain) == 2 && !empty($currentSubdomain)) {
                $domains[$domainIndex] = $currentSubdomain . '.' . $domain;
            }
        }

        return view(
            'usora::authentication-check',
            [
                'loginSuccessRedirectUrl' => session()->get(
                    'login-success-redirect-url',
                    url()->to(config('usora.login_success_redirect_path'))
                ),
                'loginPageUrl' => session()->get(
                    'login-page-url',
                    url()->to(config('usora.login_page_path'))
                ),
                'domains' => $domains,
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
                    'domains' => [],
                ]
            );
        }

        $domains = config('usora.domains_to_check_for_authentication');
        $currentSubdomain = array_reverse(explode('.', $request->getHttpHost()))[2] ?? '';

        foreach ($domains as $domainIndex => $domain) {
            $explodedDomain = explode('.', $domain);

            if (count($explodedDomain) == 2 && !empty($currentSubdomain)) {
                $domains[$domainIndex] = $currentSubdomain . '.' . $domain;
            }
        }

        // if we are authed via a remember token
        $rememberTokenCookieValue = $request->cookies->get(
            auth()
                ->guard()
                ->getRecallerName()
        );

        if (!empty($rememberTokenCookieValue)) {
            $recaller = new Recaller($rememberTokenCookieValue);

            $hash = 'remember_token|' . $this->hasher->make($user->getId() . $user->getPassword() . $recaller->token());
        } else {
            $hash = 'salt|' . $this->hasher->make($user->getId() . $user->getPassword() . $user->getSessionSalt());
        }

        return view(
            'usora::post-message-verification-token',
            [
                'failed' => false,
                'token' => $hash,
                'userId' => $user->getId(),
                'domains' => $domains,
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
                'uid' => 'required|integer|exists:' .
                    config('usora.database_connection_name') .
                    '.' .
                    config('usora.tables.users') .
                    ',id',
                'vt' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response('');
        }

        $userId = $request->get('uid');
        $verificationToken = $request->get('vt');

        $user = $this->userRepository->find($userId);

        if (strpos($verificationToken, 'remember_token|') === 0) {
            $verificationToken = substr($verificationToken, 15);

            $rememberTokens = $user->getRememberTokens();

            $iterator = $rememberTokens->getIterator();
            $iterator->uasort(function (RememberToken $a, RememberToken $b) {
                return ($a->getCreatedAt() > $b->getCreatedAt()) ? -1 : 1;
            });

            // only look at the last 15 tokens
            // todo: only keep 25 remember tokens per user, delete previous ones after that?
            $rememberTokens = new ArrayCollection(array_slice(iterator_to_array($iterator), 0, 20));

            foreach ($rememberTokens as $rememberToken) {

                // why is this hash checking so slow??
                if ($this->hasher->check(
                        $user->getId() . $user->getPassword() . $rememberToken->getToken(),
                        $verificationToken
                    ) && $rememberToken->getExpiresAt() > Carbon::now()) {

                    cookie()->queue(
                        cookie()->make(
                            auth()
                                ->guard()
                                ->getRecallerName(),
                            $user->getAuthIdentifier() .
                            '|' .
                            $rememberToken->getToken() .
                            '|' .
                            $user->getAuthPassword()
                        )
                    );
                }
            }

        } elseif (strpos($verificationToken, 'salt|') === 0) {

            $verificationToken = substr($verificationToken, 5);

            if ($this->hasher->check(
                $user->getId() . $user->getPassword() . $user->getSessionSalt(),
                $verificationToken
            )) {

                SaltedSessionGuard::$updateSalt = false;

                auth()->loginUsingId($userId, false);

                return response()->json(['success' => 'true']);
            }
        }

        return response()->json(['success' => 'false']);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function deauthenticate(Request $request)
    {
        session()->put('skip-third-party-auth-check', true);

        $user = auth()->user();

        if (empty($user)) {
            throw new NotFoundHttpException();
        }

        auth()->logout();

        return $request->has('redirect') ? redirect()->away($request->get('redirect')) :
            redirect()->to(config('usora.login_page_path'));
    }

    /**
     * @return string
     */
    public function username()
    {
        return 'email';
    }
}