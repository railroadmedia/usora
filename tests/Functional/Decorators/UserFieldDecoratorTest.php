<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\Repositories\UserFieldRepository;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Tests\UsoraTestCase;

class UserFieldDecoratorTest extends UsoraTestCase
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var UserFieldRepository
     */
    protected $userFieldRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->userRepository = app(UserRepository::class);
        $this->userFieldRepository = app(UserFieldRepository::class);
    }

    public function test_decorate_none()
    {
        $user = $this->userRepository->create($this->faker->user());
        $response = $this->userRepository->read($user['id']);

        $this->assertEquals([], $response['fields']);
    }

    public function test_decorate_single()
    {
        $user = $this->userRepository->create($this->faker->user());
        $userField = $this->userFieldRepository->create($this->faker->userField(['user_id' => $user['id']]));

        $response = $this->userRepository->read($user['id']);

        $this->assertEquals([$userField], $response['fields']);
    }

    public function test_decorate_multiple()
    {
        $user = $this->userRepository->create($this->faker->user());
        $userField1 = $this->userFieldRepository->create($this->faker->userField(['user_id' => $user['id']]));
        $userField2 = $this->userFieldRepository->create($this->faker->userField(['user_id' => $user['id']]));
        $userField3 = $this->userFieldRepository->create($this->faker->userField(['user_id' => $user['id']]));

        $response = $this->userRepository->read($user['id']);

        $this->assertEquals([$userField1, $userField2, $userField3], $response['fields']);
    }
}