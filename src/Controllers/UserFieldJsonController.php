<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Requests\UserFieldJsonCreateRequest;
use Railroad\Usora\Requests\UserFieldJsonUpdateRequest;
use Railroad\Usora\Requests\UserFieldJsonUpdateByKeyRequest;
use Railroad\Usora\Repositories\UserFieldRepository;
use Railroad\Usora\Services\ConfigService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserFieldJsonController extends Controller
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
     * UserFieldJsonController constructor.
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
     * @param integer $id
     * @return JsonResponse
     */
    public function index($id)
    {
        if (!$this->permissionService->can(auth()->id(), 'show-users')) {
            throw new NotFoundHttpException();
        }

        $users = $this->userFieldRepository->query()
            ->where('user_id', $id)
            ->get();

        return response()->json($users);
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

        $userField = $this->userFieldRepository->read($id);

        return response()->json($userField);
    }

    /**
     * @param UserFieldJsonCreateRequest $request
     * @return JsonResponse
     */
    public function store(UserFieldJsonCreateRequest $request)
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

        return response()->json($userField);
    }

    /*
     * @param UserFieldJsonUpdateRequest $request
     * @param integer $id
     * @return JsonResponse
     */
    public function update(UserFieldJsonUpdateRequest $request, $id)
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
                        'key',
                        'value',
                    ]
                ),
                [
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]
            )
        );

        return response()->json($userField);
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

            $userField = $this->userFieldRepository->create(
                [
                    'key' => $request->get('key'),
                    'user_id' => $userId,
                    'value' => $request->get('value'),
                    'created_at' => Carbon::now()->toDateTimeString(),
                ]
            );

        } else {

            $userField = $this->userFieldRepository->query()
                ->where(
                    [
                        'key' => $request->get('key'),
                        'user_id' => $userId,
                    ]
                )
                ->get()
                ->first();
        }

        return response()->json($userField);
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

                foreach ($validator->errors()->getMessages() as $key => $value) {
                    $errors[] = [
                        "source" => $key,
                        "detail" => $value[0]
                    ];
                }

                throw new HttpResponseException(response()->json(['status' => 'error',
                        'code' => 422,
                        'total_results' => 0,
                        'results' => [],
                        'errors' => $errors], 422));
            }
        }

        $userFields = [];

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
                $userField = $this->userFieldRepository->create(
                    [
                        'key' => $key,
                        'user_id' => $userId,
                        'value' => $value,
                        'created_at' => Carbon::now()->toDateTimeString(),
                    ]
                );
            } else {

                $userField = $this->userFieldRepository->query()
                ->where(
                    [
                        'key' => $key,
                        'user_id' => $userId,
                    ]
                )
                ->get()
                ->first();
            }

            $userFields[] = $userField;
        }

        return response()->json($userFields);
    }

    /**
     * @param integer $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        if (!$this->permissionService->can(auth()->id(), 'delete-users')) {
            throw new NotFoundHttpException();
        }

        $this->userFieldRepository->destroy($id);

        return new JsonResponse(null, 204);
    }
}
