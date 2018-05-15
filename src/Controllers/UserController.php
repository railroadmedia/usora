<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Requests\UserCreateRequest;
use Railroad\Usora\Requests\UserUpdateRequest;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Services\ConfigService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
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
     * @param UserCreateRequest $request
     * @return RedirectResponse
     */
    public function store(UserCreateRequest $request)
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

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()->away($request->has('redirect'))->with($message) :
            redirect()->back()->with($message);
    }

    /**
     * @param UserUpdateRequest $request
     * @param integer $id
     * @return RedirectResponse
     */
    public function update(UserUpdateRequest $request, $id)
    {
        if (
            !$this->permissionService->can(auth()->id(), 'update-users')
            && auth()->id() != $id
        ) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->update(
            $id,
            array_merge(
                $request->only(
                    [
                        'display_name',
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
     * @param Request $request
     * @param integer $id
     * @return RedirectResponse
     */
    public function delete(Request $request, $id)
    {
        if (!$this->permissionService->can(auth()->id(), 'delete-users')) {
            throw new NotFoundHttpException();
        }

        $this->userRepository->destroy($id);

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()->away($request->get('redirect'))->with($message) :
            redirect()->back()->with($message);
    }
}