<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class UserFieldControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();
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
            '/user-field/store',
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        // assert the session has the success message
        $response->assertSessionHas('success', true);
    }

    public function test_users_field_store_with_owner()
    {
        $userId = $this->createNewUser();

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
        ];

        $this->authManager->guard()->onceUsingId($userId);

        $response = $this->call(
            'PUT',
            '/user-field/store',
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        // assert the session has the success message
        $response->assertSessionHas('success', true);
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
            '/user-field/store',
            $userFieldData
        );

        // assert the users data was not saved in the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        // assert response status is not found
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_users_field_store_validation_fail()
    {
        $response = $this->call(
            'PUT',
            '/user-field/store',
            []
        );

        // assert response status code
        $this->assertEquals(302, $response->getStatusCode());

        // assert validation fail
        $response->assertSessionHasErrors(['key']);
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
            '/user-field/update/' . $userFieldId,
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        // assert the session has the success message
        $response->assertSessionHas('success', true);
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
            '/user-field/update/' . $userFieldId,
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        // assert the session has the success message
        $response->assertSessionHas('success', true);
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
            '/user-field/update/' . $userFieldId,
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
            '/user-field/delete/' . $userFieldId
        );

        // assert the user was removed from the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            [
                'id' => $userFieldId
            ]
        );

        // assert the session has the success message
        $response->assertSessionHas('success', true);
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
            '/user-field/delete/' . $userFieldId
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
