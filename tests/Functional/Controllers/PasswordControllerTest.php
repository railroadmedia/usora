<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Faker\ORM\Doctrine\Populator;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Tests\UsoraTestCase;

class PasswordControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $populator = new Populator($this->faker, $this->entityManager);

        $populator->addEntity(
            User::class,
            1,
            [
                'email' => 'login_user_test@email.com',
                'password' => 'Password12345!@',
            ]
        );
        $populator->execute();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class)], true);
    }

    public function test_users_store_with_permission()
    {
        $rawPassword = 'Password12345!@';
        $email = 'login_user_test@email.com';

        $userId = 1;

        auth()->loginUsingId($userId);

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $newPassword = $this->faker->words(3, true);

        $this->assertTrue(auth()->attempt(['email' => $email, 'password' => $rawPassword]));

        $response = $this->call(
            'PATCH',
            'usora/user/update-password',
            [
                'current_password' => $rawPassword,
                'new_password' => $newPassword,
                'new_password_confirmation' => $newPassword,
            ]
        );

        $this->assertFalse(auth()->attempt(['email' => $email, 'password' => $rawPassword]));
        $this->assertTrue(auth()->attempt(['email' => $email, 'password' => $newPassword]));
    }
}
