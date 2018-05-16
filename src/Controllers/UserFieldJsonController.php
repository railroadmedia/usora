<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Requests\UserFieldJsonCreateRequest;
use Railroad\Usora\Requests\UserFieldJsonUpdateRequest;
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
