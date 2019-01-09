<?php

namespace Railroad\Usora\Controllers;

use Doctrine\ORM\EntityManager;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\MessageBag;
use Railroad\Permissions\Exceptions\NotAllowedException;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Repositories\UserRepository;

class PasswordController extends Controller
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
    public function __construct(EntityManager $entityManager, Hasher $hasher, PermissionService $permissionService)
    {
        $this->entityManager = $entityManager;
        $this->hasher = $hasher;
        $this->permissionService = $permissionService;

        $this->userRepository = $this->entityManager->getRepository(User::class);
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

        if (!auth()->attempt(['email' => $user->getEmail(), 'password' => $request->get('current_password')])) {
            return redirect()
                ->back()
                ->withErrors(
                    ['current_password' => 'The current password you entered is incorrect.']
                );
        }

        $hashedPassword = $this->hasher->make($request->get('new_password'));
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        event(new PasswordReset($user));

        auth()->loginUsingId($user->getId());

        return redirect()
            ->back()
            ->with(
                'successes',
                new MessageBag(['password' => 'Your password has been reset successfully.'])
            );
    }
}
