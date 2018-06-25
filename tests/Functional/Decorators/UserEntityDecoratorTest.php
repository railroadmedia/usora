<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\Entities\User;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Tests\UsoraTestCase;

class UserEntityDecoratorTest extends UsoraTestCase
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->userRepository = app(UserRepository::class);
    }

    public function test_decorate()
    {
        $user = $this->userRepository->create($this->faker->user());

        $this->assertInstanceOf(User::class, $user);
    }
}