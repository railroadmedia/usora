<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\MessageBag;
use Railroad\Permissions\Exceptions\NotAllowedException;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Services\ConfigService;

class PasswordController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Hasher
     */
    private $hasher;
    /**
     * @var PermissionService
     */
    private $permissionService;

    /**
     * CookieController constructor.
     *
     * @param UserRepository $userRepository
     * @param Hasher $hasher
     * @param PermissionService $permissionService
     */
    public function __construct(UserRepository $userRepository, Hasher $hasher, PermissionService $permissionService)
    {
        $this->userRepository = $userRepository;
        $this->hasher = $hasher;
        $this->permissionService = $permissionService;

        $this->middleware(ConfigService::$authenticationControllerMiddleware);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws NotAllowedException
     */
    public function update(Request $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'edit-users') &&
            $request->has('user_id') &&
            $request->get('user_id') != auth()->id()) {
            throw new NotAllowedException('You do not have permission to update this users password.');
        }

        $request->validate(
            [
                'current_password' => 'required|min:6',
                'new_password' => 'required|confirmed|min:6',
            ]
        );

        $user = auth()->user();

        if (!auth()->attempt(['email' => $user['email'], 'password' => $request->get('current_password')])) {
            return redirect()->back()->withErrors(
                ['current_password' => 'The current password you entered is incorrect.']
            );
        }

        $hashedPassword = $this->hasher->make($request->get('new_password'));

        $this->userRepository->updateOrCreate(
            ['id' => $request->get('user_id', $user['id'])],
            ['password' => $hashedPassword]
        );

        $user['password'] = $hashedPassword;

        event(new PasswordReset($user));

        auth()->loginUsingId($user['id']);

        return redirect()->back()
            ->with(
                'successes',
                new MessageBag(['password' => 'Your password has been reset successfully.'])
            );
    }
}
