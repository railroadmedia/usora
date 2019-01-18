<?php

namespace Railroad\Usora\Controllers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Requests\UserJsonCreateRequest;
use Railroad\Usora\Requests\UserJsonUpdateRequest;
use Railroad\Usora\Services\ResponseService;
use Spatie\Fractal\Fractal;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserJsonController extends Controller
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
     * @param Request $request
     * @return Fractal
     */
    public function index(Request $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'index-users')) {
            throw new NotFoundHttpException();
        }

        $searchTerm = $request->get('search_term', '');

        $queryBuilder =
            $this->userRepository->createQueryBuilder('user')
                ->select("user");

        if (!empty($searchTerm)) {
            $queryBuilder->where(
                $queryBuilder->expr()
                    ->orX(
                        $queryBuilder->expr()
                            ->like('user.email', ':term'),
                        $queryBuilder->expr()
                            ->like('user.displayName', ':term')
                    )
            )
                ->setParameter('term', '%' . addcslashes($searchTerm, '%_') . '%');
        }

        $queryBuilder->setMaxResults($request->get('per_page', 25))
            ->setFirstResult(($request->get('page', 1) - 1) * $request->get('per_page', 25))
            ->orderBy(
                'user.' . $request->get('order_by_column', 'createdAt'),
                $request->get('order_by_direction', 'desc')
            );

        $users =
            $queryBuilder->getQuery()
                ->getResult();

        return ResponseService::user($users, $queryBuilder);
    }

    /**
     * @param integer $id
     * @return Fractal
     */
    public function show($id)
    {
        if (!$this->permissionService->can(auth()->id(), 'show-users')) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->find($id);

        return ResponseService::user($user);
    }

    /**
     * @param UserJsonCreateRequest $request
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function store(UserJsonCreateRequest $request)
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

        return ResponseService::user($user)
            ->respond(201);
    }

    /**
     * @param UserJsonUpdateRequest $request
     * @param integer $id
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(UserJsonUpdateRequest $request, $id)
    {
        if (!$this->permissionService->can(auth()->id(), 'update-users') && auth()->id() != $id) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->find($id);
        $user->setDisplayName($request->get('display_name'));

        if ($this->permissionService->can(auth()->id(), 'update-users')) {
            $user->setEmail($request->get('email'));
        }

        if ($this->permissionService->can(auth()->id(), 'update-users') && !empty($request->get('password'))) {
            $user->setPassword($this->hasher->make($request->get('password')));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return ResponseService::user($user)
            ->respond(200);
    }

    /**
     * @param integer $id
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete($id)
    {
        if (!$this->permissionService->can(auth()->id(), 'delete-users')) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->find($id);

        if (!is_null($user)) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            return ResponseService::empty(204);
        }

        return ResponseService::empty(404);
    }
}