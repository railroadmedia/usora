<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Repositories\UserFieldRepository;
use Railroad\Usora\Requests\UserFieldCreateRequest;
use Railroad\Usora\Requests\UserFieldUpdateByKeyRequest;
use Railroad\Usora\Requests\UserFieldUpdateRequest;
use Railroad\Usora\Services\ConfigService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserFieldController extends Controller
{
    /**
     * @var UserFieldRepository
     */
    private $userFieldRepository;

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
    public function __construct(UserFieldRepository $userFieldRepository, PermissionService $permissionService)
    {
        $this->userFieldRepository = $userFieldRepository;
        $this->permissionService = $permissionService;

        $this->middleware(ConfigService::$authenticationControllerMiddleware);
    }

    /**
     * @param UserFieldCreateRequest $request
     * @return RedirectResponse
     */
    public function store(UserFieldCreateRequest $request)
    {
        if (
            !$this->permissionService->can(auth()->id(), 'create-users')
            && $request->get('user_id') != auth()->id()
        ) {
            throw new NotFoundHttpException();
        }

        if (!$request->has('user_id')) {
            $request->attributes->set('user_id', auth()->id());
        }

        $userField = $this->userFieldRepository->create(
            array_merge(
                $request->only(
                    [
                        'user_id',
                        'key',
                        'value',
                    ]
                ),
                [
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]
            )
        );

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()->away($request->get('redirect'))->with($message) :
            redirect()->back()->with($message);
    }

    /**
     * @param UserFieldUpdateRequest $request
     * @param integer $id
     * @return RedirectResponse
     */
    public function update(UserFieldUpdateRequest $request, $id)
    {
        $userField = $this->userFieldRepository->read($id);

        if (!$this->permissionService->can(auth()->id(), 'update-users')) {
            if ($userField['user_id'] !== auth()->id()) {
                throw new NotFoundHttpException();
            }

            $request->request->remove('user_id');
        }

        $userField = $this->userFieldRepository->update(
            $id,
            array_merge(
                $request->only(
                    [
                        'user_id',
                        'key',
                        'value',
                    ]
                ),
                [
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]
            )
        );

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()->away($request->get('redirect'))->with($message) :
            redirect()->back()->with($message);
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

        // update or create
        $updateCount = $this->userFieldRepository->query()
            ->where(
                [
                    'key' => $request->get('key'),
                    'user_id' => $userId,
                ]
            )
            ->update(
                [
                    'value' => $request->get('value'),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]
            );

        if ($updateCount == 0) {
            $this->userFieldRepository->create(
                [
                    'key' => $request->get('key'),
                    'user_id' => $userId,
                    'value' => $request->get('value'),
                    'created_at' => Carbon::now()->toDateTimeString(),
                ]
            );
        }

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()->away($request->get('redirect'))->with($message) :
            redirect()->back()->with($message);
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
                return $request->has('redirect') ?
                    redirect()->away($request->get('redirect'))->withErrors($validator) :
                    redirect()->back()->withErrors($validator);
            }
        }

        // update or create
        foreach ($fields as $key => $value) {
            $updateCount = $this->userFieldRepository->query()
                ->where(
                    [
                        'key' => $key,
                        'user_id' => $userId,
                    ]
                )
                ->update(
                    [
                        'value' => $value,
                        'updated_at' => Carbon::now()->toDateTimeString(),
                    ]
                );

            if ($updateCount == 0) {
                $this->userFieldRepository->create(
                    [
                        'key' => $key,
                        'user_id' => $userId,
                        'value' => $value,
                        'created_at' => Carbon::now()->toDateTimeString(),
                    ]
                );
            }
        }

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()->away($request->get('redirect'))->with($message) :
            redirect()->back()->with($message);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(Request $request, $id)
    {
        if (!$this->permissionService->can(auth()->id(), 'delete-users')) {
            throw new NotFoundHttpException();
        }

        $this->userFieldRepository->destroy($id);

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()->away($request->get('redirect'))->with($message) :
            redirect()->back()->with($message);
    }
}