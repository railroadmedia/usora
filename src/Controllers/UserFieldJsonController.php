<?php

namespace Railroad\Usora\Controllers;

use Doctrine\ORM\EntityManager;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JMS\Serializer\SerializerBuilder;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Entities\UserField;
use Railroad\Usora\Requests\UserFieldJsonCreateRequest;
use Railroad\Usora\Requests\UserFieldJsonUpdateByKeyRequest;
use Railroad\Usora\Requests\UserFieldJsonUpdateRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserFieldJsonController extends Controller
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserFieldRepository
     */
    private $userFieldRepository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    private $userRepository;

    /**
     * @var PermissionService
     */
    private $permissionService;

    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * UserFieldJsonController constructor.
     *
     * @param UserFieldRepository $userFieldRepository
     * @param PermissionService $permissionService
     */
    public function __construct(EntityManager $entityManager, PermissionService $permissionService)
    {
        $this->entityManager = $entityManager;
        $this->permissionService = $permissionService;

        $this->userFieldRepository = $this->entityManager->getRepository(UserField::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);

        $this->serializer =
            SerializerBuilder::create()
                ->build();
    }

    /**
     * @param integer $id
     * @return JsonResponse
     */
    public function index($id)
    {
        if (!$this->permissionService->can(auth()->id(), 'show-users')) {
            throw new NotFoundHttpException();
        }

        $userFields = $this->userFieldRepository->findBy(['user' => $id]);

        return response($this->serializer->serialize($userFields, 'json'));
    }

    /**
     * @param integer $id
     * @return JsonResponse
     */
    public function show($id)
    {
        if (!$this->permissionService->can(auth()->id(), 'show-users')) {
            throw new NotFoundHttpException();
        }

        $userField = $this->userFieldRepository->find($id);

        return response($this->serializer->serialize($userField, 'json'));
    }

    /**
     * @param UserFieldJsonCreateRequest $request
     * @return JsonResponse
     */
    public function store(UserFieldJsonCreateRequest $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'create-users') && $request->get('user_id') != auth()->id()) {
            throw new NotFoundHttpException();
        }

        if (!$request->has('user_id')) {
            $request->attributes->set('user_id', auth()->id());
        }

        $user = $this->userRepository->find($request->get('user_id'));

        $userField = new UserField();
        $userField->setUser($user);
        $userField->setKey($request->get('key'));
        $userField->setValue($request->get('value'));

        $this->entityManager->persist($userField);
        $this->entityManager->flush();

        return response($this->serializer->serialize($userField, 'json'));
    }

    /*
     * @param UserFieldJsonUpdateRequest $request
     * @param integer $id
     * @return JsonResponse
     */
    public function update(UserFieldJsonUpdateRequest $request, $id)
    {
        $userField = $this->userFieldRepository->find($id);

        if (!$this->permissionService->can(auth()->id(), 'update-users')) {
            if ($userField->getUser()
                    ->getId() !== auth()->id()) {
                throw new NotFoundHttpException();
            }

            $request->request->remove('user_id');
        }
        if (!is_null($userField)) {
            if ($request->get('user_id')) {
                $user = $this->userRepository->find($request->get('user_id'));
                $userField->setUser($user);
            }
            $userField->setKey($request->get('key'));
            $userField->setValue($request->get('value'));

            $this->entityManager->persist($userField);
            $this->entityManager->flush();
        }

        return response($this->serializer->serialize($userField, 'json'));
    }

    /**
     * @param UserFieldJsonUpdateByKeyRequest $request
     * @return JsonResponse
     */
    public function updateOrCreateByKey(UserFieldJsonUpdateByKeyRequest $request)
    {
        $userId = auth()->id();

        if ($this->permissionService->can(auth()->id(), 'update-users')) {
            $userId = $request->get('user_id', auth()->id());
        }

        $user = $this->userRepository->find($userId);

        $userField = $this->userFieldRepository->findOneBy(
            [
                'key' => $request->get('key'),
                'user' => $user->getId(),
            ]
        );

        if (is_null($userField)) {
            $userField = new UserField();

        }
        $userField->setKey($request->get('key'));
        $userField->setValue($request->get('value'));
        $userField->setUser($user);

        $this->entityManager->persist($userField);
        $this->entityManager->flush();

        return response($this->serializer->serialize($userField, 'json'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOrCreateMultipleByKey(Request $request)
    {
        $fields = $request->get('fields', []);
        $userId = auth()->id();

        if ($this->permissionService->can(auth()->id(), 'update-users')) {
            $userId = $request->get('user_id', auth()->id());
        }

        // validate
        foreach ($fields as $key => $value) {
            $validator = validator(
                ['key' => $key, 'value' => $value, 'user_id' => $userId],
                [
                    'user_id' => 'required|numeric',
                    'key' => 'required|string|max:255|min:1',
                    'value' => 'nullable|string',
                ]
            );

            if ($validator->fails()) {
                $errors = [];

                foreach (
                    $validator->errors()
                        ->getMessages() as $key => $value
                ) {
                    $errors[] = [
                        "source" => $key,
                        "detail" => $value[0],
                    ];
                }

                throw new HttpResponseException(
                    response()->json(
                        [
                            'status' => 'error',
                            'code' => 422,
                            'total_results' => 0,
                            'results' => [],
                            'errors' => $errors,
                        ],
                        422
                    )
                );
            }
        }

        $userFields = [];
        $user = $this->userRepository->find($userId);

        // update or create
        foreach ($fields as $key => $value) {
            $userField = $this->userFieldRepository->findOneBy(
                [
                    'key' => $key,
                    'user' => $user->getId(),
                ]
            );

            if (is_null($userField)) {
                $userField = new UserField();

            }
            $userField->setKey($key);
            $userField->setValue($value);
            $userField->setUser($user);

            $this->entityManager->persist($userField);
            $this->entityManager->flush();

            $userFields[] = $userField;
        }

        return response($this->serializer->serialize($userFields, 'json'));
    }

    /**
     * @param integer $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        if (!$this->permissionService->can(auth()->id(), 'delete-user-field')) {
            throw new NotFoundHttpException();
        }

        $userField = $this->userFieldRepository->find($id);

        if (!is_null($userField)) {
            $this->entityManager->remove($userField);
            $this->entityManager->flush();
        }

        return new JsonResponse(null, 204);
    }
}
