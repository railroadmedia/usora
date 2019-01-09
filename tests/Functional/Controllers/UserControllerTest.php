<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Illuminate\Support\Facades\Route;
use Railroad\Usora\DataFixtures\UserFixtureLoader;

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
            'usora/user/store',
            $userData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.users'),
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
            'usora/user/store',
            $userData
        );

        // assert response status is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the users data was not saved in the db
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
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
            'usora/user/store',
            []
        );

        $response->assertSessionHasErrors(
            ['display_name', 'email', 'password']
        );
    }

    public function test_user_update_with_owner()
    {
        $userId = 1;

        $this->authManager->guard()
            ->onceUsingId($userId);

        $newDisplayName = $this->faker->words(4, true);
        $newEmail = $this->faker->email;

        // assert the new display name is different from existing
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
            [
                'id' => $userId,
                'display_name' => $newDisplayName,
            ]
        );

        $this->call(
            'PATCH',
            'usora/user/update/' . $userId,
            [
                'display_name' => $newDisplayName,
                'email' => $newEmail,
            ]
        );

        // assert the new display name was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.users'),
            [
                'id' => $userId,
                'display_name' => $newDisplayName,
            ]
        );

        // assert the new email field was not saved in the db
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
            [
                'id' => $userId,
                'email' => $newEmail,
            ]
        );
    }

    public function test_user_update_with_permission()
    {
        $userIdToUpdate = 1;
        $userIdLoggedIn = 2;

        $this->authManager->guard()
            ->onceUsingId($userIdLoggedIn);

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $newDisplayName = $this->faker->words(4, true);
        $newEmail = $this->faker->email;

        // assert the new display name is different from existing
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
            [
                'id' => $userIdToUpdate,
                'display_name' => $newDisplayName,
            ]
        );

        $this->call(
            'PATCH',
            'usora/user/update/' . $userIdToUpdate,
            [
                'display_name' => $newDisplayName,
                'email' => $newEmail,
            ]
        );

        // assert the new display name was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.users'),
            [
                'id' => $userIdToUpdate,
                'display_name' => $newDisplayName,
            ]
        );

        // assert the new email field was not saved in the db
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
            [
                'id' => $userIdToUpdate,
                'email' => $newEmail,
            ]
        );
    }

    public function test_user_update_without_permission()
    {
        $userIdToUpdate = 1;
        $userIdLoggedIn = 2;

        $this->authManager->guard()
            ->onceUsingId($userIdLoggedIn);

        $newDisplayName = $this->faker->words(4, true);

        $response = $this->call(
            'PATCH',
            'usora/user/update/' . $userIdToUpdate,
            [
                'display_name' => $newDisplayName,
            ]
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the new display name was not saved in the db
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
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
            'usora/user/update/' . rand(),
            ['display_name' => 123]
        );

        $response->assertSessionHasErrors(['display_name']);
    }

    public function test_user_delete_with_permission()
    {
        $userId = 1;

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $this->call(
            'DELETE',
            'usora/user/delete/' . $userId
        );

        // assert the user was removed from the db
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
            [
                'id' => $userId,
            ]
        );
    }

    public function test_user_delete_without_permission()
    {
        $userId = 1;

        $response = $this->call(
            'DELETE',
            'usora/user/delete/' . $userId
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the user was not removed from the db
        $this->assertDatabaseHas(
            config('usora.tables.users'),
            [
                'id' => $userId,
            ]
        );
    }
}
