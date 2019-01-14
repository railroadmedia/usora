<?php

namespace Railroad\Usora\Controllers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JMS\Serializer\SerializerBuilder;
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
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

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

        $this->serializer =
            SerializerBuilder::create()
                ->build();
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

        $queryBuilder = $this->userRepository->createQueryBuilder('u');

        if (!empty($searchTerm)) {
            $queryBuilder->where(
                $queryBuilder->expr()
                    ->orX(
                        $queryBuilder->expr()
                            ->like('u.email', ':term'),
                        $queryBuilder->expr()
                            ->like('u.displayName', ':term')
                    )
            )
                ->setParameter('term', '%' . addcslashes($searchTerm, '%_') . '%');
        }

        $queryBuilder->setMaxResults($request->get('per_page', 25))
            ->setFirstResult(($request->get('page', 1) - 1) * $request->get('per_page', 25))
            ->orderBy(
                'u.' . $request->get('order_by_column', 'createdAt'),
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
     * @return Fractal
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

        return ResponseService::user($user);
    }

    /**
     * @param UserJsonUpdateRequest $request
     * @param integer $id
     * @return Fractal
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

        return ResponseService::user($user);
    }

    /**
     * @param integer $id
     * @return Fractal
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
        }

        return new Fractal(null, 204);
    }
}