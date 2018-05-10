<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Services\ConfigService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserJsonController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * UserController constructor.
     *
     * @param UserRepository $userRepository
     * @param Hasher $hasher
     */
    public function __construct(UserRepository $userRepository, Hasher $hasher)
    {
        $this->userRepository = $userRepository;
        $this->hasher = $hasher;

        $this->middleware(ConfigService::$authenticationControllerMiddleware);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('users.index')) {
            throw new NotFoundHttpException();
        }

        $users = $this->userRepository->query()
            ->limit($request->get('limit', 25))
            ->skip(($request->get('page', 1) - 1) * $request->get('limit', 25))
            ->orderBy($request->get('order_by_column', 'created_at'), $request->get('order_by_direction', 'desc'))
            ->get();

        return response()->json($users);
    }

    /**
     * @param Request $request
     * @param integer $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        if (!$request->user()->can('users.show')) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->read($id);

        return response()->json($user);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('users.create')) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->create(
            $request->only(
                [
                    'id',
                    'email',
                ]
            )
        );

        return response()->json($user);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->can('users.update')) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->update(
            $id,
            $request->only(
                [
                    'display_name'
                ]
            )
        );

        return response()->json($user);
    }

    // todo: delete
}