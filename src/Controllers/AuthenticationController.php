<?php

namespace Railroad\Usora\Controllers;

use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MikeMcLin\WpPassword\Facades\WpPassword;
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
        $this->validate(
            $request,
            [
                'email' => 'required|string',
                'password' => 'required|string',
            ]
        );

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $errors = ['throttle' => 'Too many login attempts. Try again later.'];

            return $request->has('redirect') ?
                redirect()
                    ->away($request->get('redirect'))
                    ->withErrors($errors) :
                redirect()
                    ->to(config('usora.login_page_path'))
                    ->withErrors($errors);
        }

        if (auth()->attempt($request->only('email', 'password'), config('usora.remember_me'))) {
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

                    SaltedSessionGuard::$updateSalt = false;
                    auth()->loginUsingId($userByEmail->getId(), config('usora.remember_me'));

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

                    $redirect =
                        $request->has('redirect') ? $request->get('redirect') :
                            config('usora.login_success_redirect_path');

                    return redirect()->away($redirect);
                }
            }
        }

        $this->incrementLoginAttempts($request);

        $errors = ['invalid-credentials' => 'Invalid authentication credentials, please try again.'];

        return $request->has('redirect') ?
            redirect()
                ->away($request->get('redirect'))
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

            auth()->loginUsingId($user->getId(), config('usora.remember_me'));
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

        return view(
            'usora::authentication-check',
            [
                'loginSuccessRedirectUrl' => url()->to(config('usora.login_success_redirect_path')),
                'loginPageUrl' => session()->get('failure-redirect-url', url()->to(config('usora.login_page_path'))),
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
                'token' => $this->hasher->make($user->getId() . $user->getPassword() . $user->getSessionSalt()),
                'userId' => $user->getId(),
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

        if ($this->hasher->check($user->getId() . $user->getPassword() . $user->getSessionSalt(), $verificationToken)) {
            SaltedSessionGuard::$updateSalt = false;

            auth()->loginUsingId($userId, config('usora.remember_me'));

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