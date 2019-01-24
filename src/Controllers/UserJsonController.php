<?php

namespace Railroad\Usora\Controllers;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\DoctrineArrayHydrator\JsonApiHydrator;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Requests\UserJsonCreateRequest;
use Railroad\Usora\Requests\UserJsonUpdateRequest;
use Railroad\Usora\Services\ResponseService;
use ReflectionException;
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
     * @var JsonApiHydrator
     */
    private $jsonApiHydrator;

    /**
     * UserController constructor.
     *
     * @param EntityManager $entityManager
     * @param PermissionService $permissionService
     * @param Hasher $hasher
     * @param JsonApiHydrator $jsonApiHydrator
     */
    public function __construct(
        EntityManager $entityManager,
        PermissionService $permissionService,
        Hasher $hasher,
        JsonApiHydrator $jsonApiHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->permissionService = $permissionService;
        $this->hasher = $hasher;
        $this->jsonApiHydrator = $jsonApiHydrator;

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

        $queryBuilder = $this->userRepository->createQueryBuilder('user');

        if (!empty($request->get('search_term'))) {
            $queryBuilder->where(
                $queryBuilder->expr()
                    ->orX(
                        $queryBuilder->expr()
                            ->like('user.email', ':term'),
                        $queryBuilder->expr()
                            ->like('user.displayName', ':term'),
                        $queryBuilder->expr()
                            ->like('user.firstName', ':term'),
                        $queryBuilder->expr()
                            ->like('user.lastName', ':term'),
                        $queryBuilder->expr()
                            ->like('user.phoneNumber', ':term')
                    )
            )
                ->setParameter('term', '%' . $request->get('search_term') . '%');
        }

        $queryBuilder->setMaxResults($request->get('per_page', 25))
            ->setFirstResult(($request->get('page', 1) - 1) * $request->get('per_page', 25))
            ->orderBy(
                'user.' . trim($request->get('sort', 'createdAt'), '-'),
                substr($request->get('sort', 'createdAt'), 0, 1) === '-' ? 'desc' : 'asc'
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
     * @throws DBALException
     * @throws ReflectionException
     */
    public function store(UserJsonCreateRequest $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'create-users')) {
            throw new NotFoundHttpException();
        }

        $user = new User();

        $this->jsonApiHydrator->hydrate($user, $request->onlyAllowed());

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

        if (empty($user)) {
            throw new NotFoundHttpException();
        }

        $newAttributes = $request->onlyAllowed();

        $this->jsonApiHydrator->hydrate($user, $newAttributes);

        // regular users are not allowed to change their emails here
        if ($this->permissionService->can(auth()->id(), 'update-users-email-without-confirmation') &&
            !empty($request->input('data.attributes.email'))) {

            $user->setEmail($request->input('data.attributes.email'));
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