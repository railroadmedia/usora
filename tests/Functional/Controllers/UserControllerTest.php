<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Illuminate\Support\Facades\DB;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class UserControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class)]);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test_users_store_with_permission()
    {
        $userData = [
            'display_name' => $this->faker->words(4, true),
            'email' => $this->faker->email,
            'password' => 'my-password',
        ];

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $response = $this->call(
            'PUT',
            '/user/store',
            $userData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUsers,
            [
                'display_name' => $userData['display_name'],
                'email' => $userData['email'],
            ]
        );

        // assert the users password was encrypted and saved, and that they can login
        $this->assertTrue(auth()->attempt(['email' => $userData['email'], 'password' => $userData['password']]));

        // assert the session has the success message
        $response->assertSessionHas('success', true);
    }

    public function test_users_store_without_permission()
    {
        $userData = [
            'display_name' => $this->faker->words(4, true),
            'email' => $this->faker->email,
            'password' => 'my-password',
        ];

        $response = $this->call(
            'PUT',
            '/user/store',
            $userData
        );

        // assert response status is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the users data was not saved in the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'display_name' => $userData['display_name'],
                'email' => $userData['email'],
            ]
        );

        $credentials = [
            'email' => $userData['email'],
            'password' => $userData['password'],
        ];

        // assert the users data can not be used to login
        $this->assertFalse(auth()->attempt($credentials));
    }

    public function test_users_store_validation_fail()
    {
        $response = $this->call(
            'PUT',
            '/user/store',
            []
        );

        $response->assertSessionHasErrors(
            ['display_name', 'email', 'password']
        );
    }

    public function test_user_update_with_owner()
    {
        $userId = $this->createNewUser();

        $this->authManager->guard()
            ->onceUsingId($userId);

        $newDisplayName = $this->faker->words(4, true);
        $newEmail = $this->faker->email;

        // assert the new display name is different from existing
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userId,
                'display_name' => $newDisplayName,
            ]
        );

        $this->call(
            'PATCH',
            '/user/update/' . $userId,
            [
                'display_name' => $newDisplayName,
                'email' => $newEmail,
            ]
        );

        // assert the new display name was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUsers,
            [
                'id' => $userId,
                'display_name' => $newDisplayName,
            ]
        );

        // assert the new email field was not saved in the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userId,
                'email' => $newEmail,
            ]
        );
    }

    public function test_user_update_with_permission()
    {
        $userIdToUpdate = $this->createNewUser();

        $userIdLoggedIn = $this->createNewUser();

        $this->authManager->guard()
            ->onceUsingId($userIdLoggedIn);

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $newDisplayName = $this->faker->words(4, true);
        $newEmail = $this->faker->email;

        // assert the new display name is different from existing
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userIdToUpdate,
                'display_name' => $newDisplayName,
            ]
        );

        $this->call(
            'PATCH',
            '/user/update/' . $userIdToUpdate,
            [
                'display_name' => $newDisplayName,
                'email' => $newEmail,
            ]
        );

        // assert the new display name was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUsers,
            [
                'id' => $userIdToUpdate,
                'display_name' => $newDisplayName,
            ]
        );

        // assert the new email field was not saved in the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userIdToUpdate,
                'email' => $newEmail,
            ]
        );
    }

    public function test_user_update_without_permission()
    {
        $userIdToUpdate = $this->createNewUser();

        $userIdLoggedIn = $this->createNewUser();

        $this->authManager->guard()
            ->onceUsingId($userIdLoggedIn);

        $newDisplayName = $this->faker->words(4, true);

        $response = $this->call(
            'PATCH',
            '/user/update/' . $userIdToUpdate,
            [
                'display_name' => $newDisplayName,
            ]
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the new display name was not saved in the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userIdToUpdate,
                'display_name' => $newDisplayName,
            ]
        );
    }

    public function test_user_update_validation_fail()
    {
        $response = $this->call(
            'PATCH',
            '/user/update/' . rand(),
            []
        );

        $response->assertSessionHasErrors(['display_name']);
    }

    public function test_user_delete_with_permission()
    {
        $userId = $this->createNewUser();

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $this->call(
            'DELETE',
            '/user/delete/' . $userId
        );

        // assert the user was removed from the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userId,
            ]
        );
    }

    public function test_user_delete_without_permission()
    {
        $userId = $this->createNewUser();

        $response = $this->call(
            'DELETE',
            '/user/delete/' . $userId
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the user was not removed from the db
        $this->assertDatabaseHas(
            ConfigService::$tableUsers,
            [
                'id' => $userId,
            ]
        );
    }
}
