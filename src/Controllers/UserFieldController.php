<?php

namespace Railroad\Usora\Controllers;

use Doctrine\ORM\EntityManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Entities\UserField;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Requests\UserFieldCreateRequest;
use Railroad\Usora\Requests\UserFieldUpdateByKeyRequest;
use Railroad\Usora\Requests\UserFieldUpdateRequest;
use Railroad\Usora\Services\ConfigService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserFieldController extends Controller
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
     * UserFieldController constructor.
     *
     * @param UserFieldRepository $userFieldRepository
     * @param PermissionService $permissionService
     */
    public function __construct(EntityManager $entityManager, PermissionService $permissionService)
    {
        $this->entityManager = $entityManager;
        $this->permissionService = $permissionService;

        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->userFieldRepository = $this->entityManager->getRepository(UserField::class);

        $this->middleware(ConfigService::$authenticationControllerMiddleware);
    }

    /**
     * @param UserFieldCreateRequest $request
     * @return RedirectResponse
     */
    public function store(UserFieldCreateRequest $request)
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
     * @param UserFieldUpdateRequest $request
     * @param integer $id
     * @return RedirectResponse
     */
    public function update(UserFieldUpdateRequest $request, $id)
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
     * @return RedirectResponse
     */
    public function updateOrCreateByKey(UserFieldUpdateByKeyRequest $request)
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
     * @return RedirectResponse
     */
    public function updateOrCreateMultipleByKey(Request $request)
    {
        $fields = $request->get('fields', []);
        $userId = auth()->id();

        if ($this->permissionService->can(auth()->id(), 'update-users')) {
            $userId = $request->get('user_id', auth()->id());
        }

        $user = $this->userRepository->find($userId);

        // validate
        foreach ($fields as $key => $value) {
            $validator = validator(
                ['key' => $key, 'value' => $value, 'user_id' => $user->getId()],
                [
                    'user_id' => 'required|numeric',
                    'key' => 'required|string|max:255|min:1',
                    'value' => 'nullable|string',
                ]
            );

            if ($validator->fails()) {
                return $request->has('redirect') ?
                    redirect()
                        ->away($request->get('redirect'))
                        ->withErrors($validator) :
                    redirect()
                        ->back()
                        ->withErrors($validator);
            }
        }

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
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(Request $request, $id)
    {
        if (!$this->permissionService->can(auth()->id(), 'delete-user-field')) {
            throw new NotFoundHttpException();
        }

        $userField = $this->userFieldRepository->find($id);

        if (!is_null($userField)) {
            $this->entityManager->remove($userField);
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