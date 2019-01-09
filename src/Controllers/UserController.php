<?php

namespace Railroad\Usora\Controllers;

use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Requests\UserCreateRequest;
use Railroad\Usora\Requests\UserUpdateRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
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
     * @var PermissionService
     */
    private $permissionService;

    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * UserController constructor.
     *
     * @param EntityManager $entityManager
     * @param PermissionService $permissionService
     * @param Hasher $hasher
     */
    public function __construct(EntityManager $entityManager, PermissionService $permissionService, Hasher $hasher)
    {
        $this->entityManager = $entityManager;
        $this->permissionService = $permissionService;
        $this->hasher = $hasher;

        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    /**
     * @param UserCreateRequest $request
     * @return RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function store(UserCreateRequest $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'create-users')) {
            throw new NotFoundHttpException();
        }

        $user = new User();
        $user->setEmail($request->get('email'));
        $user->setDisplayName($request->get('display_name'));
        $user->setPassword($this->hasher->make($request->get('password')));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()
                ->away($request->get('redirect'))
                ->with($message) :
            redirect()
                ->back()
                ->with($message);
    }

    /**
     * @param UserUpdateRequest $request
     * @param integer $id
     * @return RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(UserUpdateRequest $request, $id)
    {
        if (!$this->permissionService->can(auth()->id(), 'update-users') && auth()->id() != $id) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->find($id);

        if (!is_null($user)) {
            $user->setDisplayName($request->get('display_name'));

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()
                ->away($request->get('redirect'))
                ->with($message) :
            redirect()
                ->back()
                ->with($message);
    }

    /**
     * @param Request $request
     * @param integer $id
     * @return RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete(Request $request, $id)
    {
        if (!$this->permissionService->can(auth()->id(), 'delete-users')) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->find($id);

        if (!is_null($user)) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()
                ->away($request->get('redirect'))
                ->with($message) :
            redirect()
                ->back()
                ->with($message);
    }
}