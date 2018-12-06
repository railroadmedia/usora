<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Tests\UsoraTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class PasswordControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class)]);
    }

    public function test_users_store_with_permission()
    {
        $rawPassword = 'Password1#';

        $user = [
            'email' => 'test+1@test.com',
            'password' => $this->hasher->make($rawPassword),
            'display_name' => 'testuser1'
        ];

        $userId = 1;

        auth()->loginUsingId($userId);

        $this->permissionServiceMock->method('can')->willReturn(true);

        $newPassword = $this->faker->words(3, true);

        $this->assertTrue(auth()->attempt(['email' => $user['email'], 'password' => $rawPassword]));

        $response = $this->call(
            'PATCH',
            '/user/update-password',
            [
                'current_password' => $rawPassword,
                'new_password' => $newPassword,
                'new_password_confirmation' => $newPassword,
            ]
        );

        $this->assertFalse(auth()->attempt(['email' => $user['email'], 'password' => $rawPassword]));
        $this->assertTrue(auth()->attempt(['email' => $user['email'], 'password' => $newPassword]));
    }
}
