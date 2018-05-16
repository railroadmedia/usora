<?php

namespace Railroad\Usora\Tests\Functional;

use Carbon\Carbon;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class UserFieldJsonControllerTest extends UsoraTestCase
{
    const API_PREFIX = '/usora';

    protected function setUp()
    {
        parent::setUp();
    }

    public function test_users_field_index_with_permission()
    {
        $userId = $this->createNewUser();

        $userField = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];

        $userFieldId = $this->databaseManager->table(ConfigService::$tableUserFields)
            ->insertGetId($userField);

        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'GET',
            self::API_PREFIX . '/user-field/index/' . $userId
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset($userField, $response->decodeResponseJson()[0]);
    }

    public function test_users_field_show_with_permission()
    {
        $userId = $this->createNewUser();

        $userField = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userFieldId = $this->databaseManager->table(ConfigService::$tableUserFields)
            ->insertGetId($userField);

        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'GET',
            self::API_PREFIX . '/user-field/show/' . $userFieldId
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // assert the user data is subset of response
        $this->assertArraySubset(
            [
                'user_id' => $userField['user_id'],
                'key' => $userField['key'],
                'value' => $userField['value']
            ],
            $response->decodeResponseJson()
        );
    }

    public function test_users_field_show_without_permission()
    {
        $userId = $this->createNewUser();

        $userField = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userFieldId = $this->databaseManager->table(ConfigService::$tableUserFields)
            ->insertGetId($userField);

        $response = $this->call(
            'GET',
            self::API_PREFIX . '/user-field/show/' . $userFieldId
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_users_field_store_with_permission()
    {
        $userId = $this->createNewUser();

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
        ];

        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'PUT',
            self::API_PREFIX . '/user-field/store',
            $userFieldData
        );

        // assert the user data is subset of response
        $this->assertArraySubset($userFieldData, $response->decodeResponseJson());

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldData
        );
    }

    public function test_users_field_store_without_permission()
    {
        $userId = $this->createNewUser();

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
        ];

        $response = $this->call(
            'PUT',
            self::API_PREFIX . '/user-field/store',
            $userFieldData
        );

        // assert response status is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the users data was not saved in the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            $userFieldData
        );
    }

    public function test_users_field_store_validation_fail()
    {
        $response = $this->call(
            'PUT',
            self::API_PREFIX . '/user-field/store',
            []
        );

        // assert response status code
        $this->assertEquals(422, $response->getStatusCode());

        // assert response validation error messages
        $this->assertEquals([
            [
                "source" => "key",
                "detail" => "The key field is required.",
            ]
        ], $response->decodeResponseJson()['errors']);
    }

    public function test_users_field_update_with_permission()
    {
        $userId = $this->createNewUser();

        $userField = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userFieldId = $this->databaseManager->table(ConfigService::$tableUserFields)
            ->insertGetId($userField);

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true)
        ];

        // assert new key/value generated
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            [
                'id' => $userFieldId,
                'key' => $userFieldData['key'],
                'value' => $userFieldData['value']
            ]
        );

        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'PATCH',
            self::API_PREFIX . '/user-field/update/' . $userFieldId,
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        // assert the user data is subset of response
        $this->assertArraySubset($userFieldData, $response->decodeResponseJson());
    }

    public function test_users_field_update_with_owner()
    {
        $userId = $this->createNewUser();

        $userField = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userFieldId = $this->databaseManager->table(ConfigService::$tableUserFields)
            ->insertGetId($userField);

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true)
        ];

        // assert new key/value generated
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        $this->authManager->guard()->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            self::API_PREFIX . '/user-field/update/' . $userFieldId,
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        // assert the user data is subset of response
        $this->assertArraySubset($userFieldData, $response->decodeResponseJson());
    }

    public function test_users_field_update_without_permission()
    {
        $userId = $this->createNewUser();

        $userField = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userFieldId = $this->databaseManager->table(ConfigService::$tableUserFields)
            ->insertGetId($userField);

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true)
        ];

        // assert new key/value generated
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        $response = $this->call(
            'PATCH',
            self::API_PREFIX . '/user-field/update/' . $userFieldId,
            $userFieldData
        );

        // assert response status is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the new user field data was not saved in the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            $userFieldData
        );
    }

    public function test_users_field_delete_with_permission()
    {
        $userId = $this->createNewUser();

        $userField = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userFieldId = $this->databaseManager->table(ConfigService::$tableUserFields)
            ->insertGetId($userField);

        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'DELETE',
            self::API_PREFIX . '/user-field/delete/' . $userFieldId
        );

        // assert the user was removed from the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            [
                'id' => $userFieldId
            ]
        );

        // assert the response code is no content
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function test_users_field_delete_without_permission()
    {
        $userId = $this->createNewUser();

        $userField = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userFieldId = $this->databaseManager->table(ConfigService::$tableUserFields)
            ->insertGetId($userField);

        $response = $this->call(
            'DELETE',
            self::API_PREFIX . '/user-field/delete/' . $userFieldId
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the user was not removed from the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            [
                'id' => $userFieldId
            ]
        );
    }
}
