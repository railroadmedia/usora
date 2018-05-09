<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Routing\Controller;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Services\ConfigService;

class UserController extends Controller
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
     * CookieController constructor.
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
}