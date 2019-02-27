<?php

namespace Railroad\Usora\Controllers;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\DoctrineArrayHydrator\ArrayHydrator;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Managers\UsoraEntityManager;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Requests\UserCreateRequest;
use Railroad\Usora\Requests\UserUpdateRequest;
use ReflectionException;
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
     * @var ArrayHydrator
     */
    private $arrayHydrator;

    /**
     * UserController constructor.
     *
     * @param EntityManager $entityManager
     * @param PermissionService $permissionService
     * @param Hasher $hasher
     */
    public function __construct(
        UsoraEntityManager $entityManager,
        PermissionService $permissionService
    ) {
        $this->entityManager = $entityManager;
        $this->permissionService = $permissionService;
        $this->arrayHydrator = new ArrayHydrator($this->entityManager);

        $this->userRepository = $this->entityManager->getRepository(User::class);

        $this->middleware([ConvertEmptyStringsToNull::class]);
    }

    /**
     * @param UserCreateRequest $request
     * @return RedirectResponse
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ReflectionException
     */
    public function store(UserCreateRequest $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'create-users')) {
            throw new NotFoundHttpException();
        }

        $user = new User();

        $this->arrayHydrator->hydrate($user, $request->onlyAllowed());

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
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ReflectionException
     */
    public function update(UserUpdateRequest $request, $id)
    {
        if (!$this->permissionService->can(auth()->id(), 'update-users') && auth()->id() != $id) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->find($id);

        if (empty($user)) {
            throw new NotFoundHttpException();
        }

        $this->arrayHydrator->hydrate($user, $request->onlyAllowed());

        // regular users are not allowed to change their emails here
        if ($this->permissionService->can(auth()->id(), 'update-users-email-without-confirmation') &&
            !empty($request->input('data.attributes.email'))) {

            $user->setEmail($request->input('data.attributes.email'));
        }

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
     * @param Request $request
     * @param integer $id
     * @return RedirectResponse
     * @throws ORMException
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