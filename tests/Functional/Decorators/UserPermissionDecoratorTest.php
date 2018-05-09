<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Permissions\Factories\AccessFactory;
use Railroad\Permissions\Factories\UserAccessFactory;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Tests\UsoraTestCase;

class UserPermissionDecoratorTest extends UsoraTestCase
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var AccessFactory
     */
    protected $accessFactory;

    /**
     * @var UserAccessFactory
     */
    protected $userAccessFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->userRepository = app(UserRepository::class);
        $this->accessFactory = app(AccessFactory::class);
        $this->userAccessFactory = app(UserAccessFactory::class);
    }

    public function test_decorate_none()
    {
        $user = $this->userRepository->create($this->faker->user());

        $response = $this->userRepository->read($user['id']);

        $this->assertEquals(
            [],
            $response['access']
        );
    }

    public function test_decorate_single()
    {
        $user = $this->userRepository->create($this->faker->user());
        $access = $this->accessFactory->store();
        $userAccess = $this->userAccessFactory->store($access['id'], $user['id']);

        $response = $this->userRepository->read($user['id']);

        $this->assertEquals(
            [
                [
                    'id' => $access['id'],
                    'slug' => $access['slug'],
                    'name' => $access['name'],
                    'description' => $access['description'],
                    'brand' => $access['brand'],
                ],
            ],
            $response['access']
        );
    }

    public function test_decorate_multiple()
    {
        $user = $this->userRepository->create($this->faker->user());
        $access1 = $this->accessFactory->store();
        $userAccess1 = $this->userAccessFactory->store($access1['id'], $user['id']);
        $access2 = $this->accessFactory->store();
        $userAccess2 = $this->userAccessFactory->store($access2['id'], $user['id']);
        $access3 = $this->accessFactory->store();
        $userAccess3 = $this->userAccessFactory->store($access3['id'], $user['id']);

        $response = $this->userRepository->read($user['id']);

        $this->assertEquals(
            [
                [
                    'id' => $access1['id'],
                    'slug' => $access1['slug'],
                    'name' => $access1['name'],
                    'description' => $access1['description'],
                    'brand' => $access1['brand'],
                ],
                [
                    'id' => $access2['id'],
                    'slug' => $access2['slug'],
                    'name' => $access2['name'],
                    'description' => $access2['description'],
                    'brand' => $access2['brand'],
                ],
                [
                    'id' => $access3['id'],
                    'slug' => $access3['slug'],
                    'name' => $access3['name'],
                    'description' => $access3['description'],
                    'brand' => $access3['brand'],
                ],
            ],
            $response['access']
        );
    }
}