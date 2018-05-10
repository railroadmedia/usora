<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
     * UserController constructor.
     *
     * @param UserRepository $userRepository
     * @param Hasher $hasher
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;

        $this->middleware(ConfigService::$authenticationControllerMiddleware);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('users.store')) {
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
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->can('users.update') || auth()->id() !== $id) {
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
            redirect()->away($request->has('redirect'))->with($message) :
            redirect()->back()->with($message);
    }

    // todo: delete
}