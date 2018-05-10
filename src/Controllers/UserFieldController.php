<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Routing\Controller;
use Railroad\Usora\Repositories\UserFieldRepository;
use Railroad\Usora\Services\ConfigService;

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

    // todo: store, update, delete
}