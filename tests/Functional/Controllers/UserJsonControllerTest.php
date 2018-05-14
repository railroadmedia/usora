<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class UserJsonControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_users_index_with_permission()
    {
        $this->permissionServiceMock->method('can')->willReturn(true);

        $userId = $this->createNewUser();
        $userId = $this->createNewUser();
        $userId = $this->createNewUser();
        $userId = $this->createNewUser();
        $userId = $this->createNewUser();
        $userId = $this->createNewUser();
        $userId = $this->createNewUser();

        $request = [
            'limit' => 3,
            'order_by_column' => 'display_name',
            'order_by_direction' => 'asc'
        ];

        $responsePageTwo = $this->call(
            'GET',
            '/api/user/index',
            $request + ['page' => 2]
        );

        // assert response status code
        $this->assertEquals(200, $responsePageTwo->getStatusCode());

        $dataPageTwo = $responsePageTwo->decodeResponseJson();

        // assert response length
        $this->assertEquals($request['limit'], count($dataPageTwo));

        // assert ascending order of display_name column
        for ($i = 0; $i < count($dataPageTwo) - 1; $i++) {
            $current = $dataPageTwo[$i];
            $next = $dataPageTwo[$i + 1];
            $cmp = strcasecmp($current['display_name'], $next['display_name']);
            $this->assertLessThanOrEqual(0, $cmp);
        }

        $responsePageOne = $this->call(
            'GET',
            '/api/user/index',
            $request + ['page' => 1]
        );

        $dataPageOne = $responsePageOne->decodeResponseJson();

        // assert response length
        $this->assertEquals($request['limit'], count($dataPageOne));

        // assert ascending order of display_name column across pages
        $cmp = strcasecmp($dataPageOne[count($dataPageOne) - 1]['display_name'], $dataPageTwo[0]['display_name']);
        $this->assertLessThanOrEqual(0, $cmp);
    }

    public function test_users_show_with_permission()
    {
        $rawPassword = $this->faker->word;

        $user = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($rawPassword),
            'remember_token' => str_random(60),
            'session_salt' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userId = $this->databaseManager->table(ConfigService::$tableUsers)
            ->insertGetId($user);

        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'GET',
            '/api/user/show/' . $userId
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // assert the user data is subset of response
        $this->assertArraySubset(
            [
                'email' => $user['email'],
                'display_name' => $user['display_name']
            ],
            $response->decodeResponseJson()
        );
    }

    public function test_users_show_without_permission()
    {
        $userId = $this->createNewUser();

        $response = $this->call(
            'GET',
            '/api/user/show/' . $userId
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_users_store_with_permission()
    {
        $userData = [
            'display_name' => $this->faker->words(4, true),
            'email' => $this->faker->email,
            'password' => 'my-password',
        ];

        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'PUT',
            '/api/user/store',
            $userData
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // assert the users password was encrypted and saved, and that they can login
        $this->assertTrue(auth()->attempt(['email' => $userData['email'], 'password' => $userData['password']]));

        unset($userData['password']);

        // assert the user data is subset of response
        $this->assertArraySubset($userData, $response->decodeResponseJson());

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUsers,
            $userData
        );
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
            '/api/user/store',
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
            'password' => $userData['password']
        ];

        // assert the users data can not be used to login
        $this->assertFalse(auth()->attempt($credentials));
    }

    public function test_users_store_validation_fail()
    {
        $response = $this->call(
            'PUT',
            '/api/user/store',
            []
        );

        // assert response status code
        $this->assertEquals(422, $response->getStatusCode());

        // assert response validation error messages
        $this->assertEquals([
            [
                "source" => "email",
                "detail" => "The email field is required.",
            ],
            [
                "source" => "display_name",
                "detail" => "The display name field is required.",
            ],
            [
                "source" => "password",
                "detail" => "The password field is required.",
            ]
        ], $response->decodeResponseJson()['errors']);
    }

    public function test_user_update_with_owner()
    {
        $userId = $this->createNewUser();

        $this->authManager->guard()->onceUsingId($userId);

        $newDisplayName = $this->faker->words(4, true);
        $newEmail = $this->faker->email;

        // assert the new display name is different from existing
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userId,
                'display_name' => $newDisplayName
            ]
        );

        $response = $this->call(
            'PATCH',
            '/api/user/update/' . $userId,
            [
                'display_name' => $newDisplayName,
                'email' => $newEmail,
            ]
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // assert the user data is subset of response
        $this->assertArraySubset(
            ['display_name' => $newDisplayName],
            $response->decodeResponseJson()
        );

        // assert the new display name was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUsers,
            [
                'id' => $userId,
                'display_name' => $newDisplayName
            ]
        );

        // assert the new email field was not saved in the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userId,
                'email' => $newEmail
            ]
        );
    }

    public function test_user_update_with_permission()
    {
        $userIdToUpdate = $this->createNewUser();

        $userIdLoggedIn = $this->createNewUser();

        $this->authManager->guard()->onceUsingId($userIdLoggedIn);

        $this->permissionServiceMock->method('can')->willReturn(true);

        $newDisplayName = $this->faker->words(4, true);
        $newEmail = $this->faker->email;

        // assert the new display name is different from existing
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userIdToUpdate,
                'display_name' => $newDisplayName
            ]
        );

        $response = $this->call(
            'PATCH',
            '/api/user/update/' . $userIdToUpdate,
            [
                'display_name' => $newDisplayName,
                'email' => $newEmail,
            ]
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // assert the user data is subset of response
        $this->assertArraySubset(
            ['display_name' => $newDisplayName],
            $response->decodeResponseJson()
        );

        // assert the new display name was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUsers,
            [
                'id' => $userIdToUpdate,
                'display_name' => $newDisplayName
            ]
        );

        // assert the new email field was not saved in the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userIdToUpdate,
                'email' => $newEmail
            ]
        );
    }

    public function test_user_update_without_permission()
    {
        $userIdToUpdate = $this->createNewUser();

        $userIdLoggedIn = $this->createNewUser();

        $this->authManager->guard()->onceUsingId($userIdLoggedIn);

        $newDisplayName = $this->faker->words(4, true);

        $response = $this->call(
            'PATCH',
            '/api/user/update/' . $userIdToUpdate,
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
                'display_name' => $newDisplayName
            ]
        );
    }

    public function test_user_update_validation_fail()
    {
        $response = $this->call(
            'PATCH',
            '/api/user/update/' . rand(),
            []
        );

        // assert response status code
        $this->assertEquals(422, $response->getStatusCode());

        // assert response validation error messages
        $this->assertEquals([
            [
                "source" => "display_name",
                "detail" => "The display name field is required.",
            ]
        ], $response->decodeResponseJson()['errors']);
    }

    public function test_user_delete_with_permission()
    {
        $userId = $this->createNewUser();

        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'DELETE',
            '/api/user/delete/' . $userId
        );

        // assert the response code is not found
        $this->assertEquals(204, $response->getStatusCode());

        // assert the user was removed from the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUsers,
            [
                'id' => $userId
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

        // echo "response code: " . $response->getStatusCode() . "\n";
        // echo "response: " . var_export($response->baseResponse->getContent(), true) . "\n";

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the user was not removed from the db
        $this->assertDatabaseHas(
            ConfigService::$tableUsers,
            [
                'id' => $userId
            ]
        );
    }
}
