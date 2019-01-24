<?php

namespace Railroad\Usora\Controllers;

use Doctrine\ORM\EntityManager;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\MessageBag;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Managers\UsoraEntityManager;
use Railroad\Usora\Repositories\UserRepository;

class ResetPasswordController extends Controller
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * CookieController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UsoraEntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    /**
     * Reset the given user's password.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function reset(Request $request)
    {
        $request->validate(
            [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed|min:6',
            ]
        );

        $response =
            $this->broker()
                ->reset(
                    $request->only(
                        'email',
                        'password',
                        'password_confirmation',
                        'token'
                    ),
                    function ($user, $password) {

                        $user->setPassword($password);

                        $this->entityManager->persist($user);
                        $this->entityManager->flush();

                        event(new PasswordReset($user));

                        auth()->loginUsingId($user->getId());
                    }
                );

        if ($response === Password::PASSWORD_RESET) {
            session()->put('skip-third-party-auth-check', true);

            return redirect()
                ->to(config('usora.login_success_redirect_path'))
                ->with(
                    'successes',
                    new MessageBag(['password' => 'Your password has been reset successfully.'])
                );
        }

        return redirect()
            ->back()
            ->withErrors(['password' => 'Password reset failed, please try again.']);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
