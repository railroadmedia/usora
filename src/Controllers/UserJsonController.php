<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Requests\UserJsonCreateRequest;
use Railroad\Usora\Requests\UserJsonUpdateRequest;
use Railroad\Usora\Services\ConfigService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserJsonController extends Controller
{
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
     * @param UserRepository $userRepository
     * @param PermissionService $permissionService
     * @param Hasher $hasher
     */
    public function __construct(UserRepository $userRepository, PermissionService $permissionService, Hasher $hasher)
    {
        $this->userRepository = $userRepository;
        $this->permissionService = $permissionService;
        $this->hasher = $hasher;

        $this->middleware(ConfigService::$authenticationControllerMiddleware);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'index-users')) {
            throw new NotFoundHttpException();
        }

        $query = $this->userRepository->query()
            ->limit($request->get('limit', 25))
            ->skip(($request->get('page', 1) - 1) * $request->get('limit', 25))
            ->orderBy($request->get('order_by_column', 'created_at'), $request->get('order_by_direction', 'desc'));

        $searchTerm = $request->get('search_term', '');

        if (!empty($searchTerm)) {
            $query = $query->where('email', 'LIKE', '%' . $searchTerm . '%')
                ->where('display_name', 'LIKE', '%' . $searchTerm . '%', 'OR');
        }

        $users = $query->get();

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

        $user = $this->userRepository->read($id);

        return response()->json($user);
    }

    /**
     * @param UserJsonCreateRequest $request
     * @return JsonResponse
     */
    public function store(UserJsonCreateRequest $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'create-users')) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->create(
            array_merge(
                $request->only(
                    [
                        'email',
                        'display_name',
                    ]
                ),
                [
                    'password' => $this->hasher->make($request->get('password')),
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]
            )
        );

        return response()->json($user);
    }

    /**
     * @param UserJsonUpdateRequest $request
     * @param integer $id
     * @return JsonResponse
     */
    public function update(UserJsonUpdateRequest $request, $id)
    {
        if (
            !$this->permissionService->can(auth()->id(), 'update-users')
            && auth()->id() != $id
        ) {
            throw new NotFoundHttpException();
        }

        $only = [
            'display_name',
        ];

        if ($this->permissionService->can(auth()->id(), 'update-users')) {
            $only[] = 'email';
        }

        $attributes = $request->only($only);

        if ($this->permissionService->can(auth()->id(), 'update-users') && !empty($request->get('password'))) {
            $attributes['password'] = $this->hasher->make($request->get('password'));
        }

        $user = $this->userRepository->update($id, $attributes);

        return response()->json($user);
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

        $this->userRepository->destroy($id);

        return new JsonResponse(null, 204);
    }
}