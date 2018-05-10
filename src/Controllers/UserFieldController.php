<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Usora\Repositories\UserFieldRepository;
use Railroad\Usora\Services\ConfigService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserFieldController extends Controller
{
    /**
     * @var UserFieldRepository
     */
    private $userFieldRepository;

    public function __construct(UserFieldRepository $userFieldRepository)
    {
        $this->userFieldRepository = $userFieldRepository;

        $this->middleware(ConfigService::$authenticationControllerMiddleware);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('users.store') && $request->get('user_id') !== auth()->id()) {
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
            redirect()->away($request->has('redirect'))->with($message) :
            redirect()->back()->with($message);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $userField = $this->userFieldRepository->read($id);

        if (!$request->user()->can('users.update')) {
            if ($userField['user_id'] !== auth()->id()) {
                throw new NotFoundHttpException();
            }

            $request->attributes->remove('user_id');
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
            redirect()->away($request->has('redirect'))->with($message) :
            redirect()->back()->with($message);
    }

    // todo: delete
}