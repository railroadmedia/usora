<?php

namespace Railroad\Usora\Controllers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use MikeMcLin\WpPassword\Facades\WpPassword;
use Railroad\Permissions\Exceptions\NotAllowedException;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Managers\UsoraEntityManager;
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
    public function __construct(UsoraEntityManager $entityManager, Hasher $hasher, PermissionService $permissionService)
    {
        $this->entityManager = $entityManager;
        $this->hasher = $hasher;
        $this->permissionService = $permissionService;

        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    /**
     * Reset the given user's password.
     *
     * @permission Must be logged in
     * @permission Only users with edit-users ability
     *
     * @bodyParam current_password required
     * @bodyParam new_password required
     *
     * @param  Request $request
     * @return RedirectResponse
     * @throws NotAllowedException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Request $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'edit-users') &&
            $request->has('user_id') &&
            $request->get('user_id') != auth()->id()) {
            throw new NotAllowedException('You do not have permission to update this users password.');
        }

        try{
            $request->validate(
                [
                    'current_password' => 'required',
                    'new_password' => 'required|' . config('usora.password_creation_rules', 'confirmed|min:8|max:128'),
                ]
            );
        }catch(ValidationException $e){

            $messagesByField = $e->validator->getMessageBag()->getMessages();

            $messagesForFieldFailingField = reset($messagesByField);

            foreach($messagesForFieldFailingField as $messagesForField){
                $errorMessageToUser = $messagesForField;
                break;
            }

            $default = 'Please try again, and contact support if the problem persists.';

            return redirect()->back()->with('error-message', 'Error: ' . ($errorMessageToUser ?? $default));
        }

        /**
         * @var $user User
         */
        $user = $this->userRepository->find(auth()->id());

        if (
            !$this->hasher->check($request->get('current_password'), $user->getPassword())
            && !WpPassword::check(trim($request->get('current_password')), $user->getPassword())
        ) {
            return redirect()->back()->with('error-message', 'The current password you entered is incorrect.');
        }

        $user->setPassword($request->get('new_password'));

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
