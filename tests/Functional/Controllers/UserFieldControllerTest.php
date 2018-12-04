<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\DataFixtures\UserFieldFixtureLoader;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;


class UserFieldControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class), app(UserFieldFixtureLoader::class)]);
    }

    public function test_users_field_store_with_permission()
    {
        $userId = 1;

        $this->authManager->guard()
            ->onceUsingId($userId);

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word,
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
        $userId = 1;

        $this->authManager->guard()
            ->onceUsingId($userId);

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word,
            'value' => $this->faker->words(4, true),
        ];

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
        $userId = 1;

        $this->authManager->guard()
            ->onceUsingId($userId);

        $userFieldData = [
            'user_id' => $this->faker->randomNumber(),
            'key' => $this->faker->word,
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
        $userId = 1;

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word,
            'value' => $this->faker->words(4, true),
        ];

        // assert new key/value generated
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            [
                'id' => 1,
                'key' => $userFieldData['key'],
                'value' => $userFieldData['value'],
            ]
        );

        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'PATCH',
            '/user-field/update/' . 1,
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
        $userId = 1;

        $this->authManager->guard()
            ->onceUsingId($userId);

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word,
            'value' => $this->faker->words(4, true),
        ];

        // assert new key/value generated
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        $response = $this->call(
            'PATCH',
            '/user-field/update/' . 1,
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
        $userId = 2;

        $this->authManager->guard()
            ->onceUsingId($userId);

        $userFieldData = [
            'key' => $this->faker->word,
            'value' => $this->faker->words(4, true),
        ];

        // assert new key/value generated
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        $response = $this->call(
            'PATCH',
            '/user-field/update/' . 1,
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

    public function test_users_field_update_multiple_by_key_create_validation_fails()
    {
        $userId = $this->createNewUser();

        $userFieldsData = [
            [
                'key' => '',
                'value' => $this->faker->words(4, true),
            ],
            [
                'key' => $this->faker->word,
                'value' => $this->faker->words(4, true),
            ],
            [
                'key' => $this->faker->word,
                'value' => $this->faker->words(4, true),
            ],
        ];

        $userFieldsInputData = [];

        foreach ($userFieldsData as $userField) {
            $userFieldsInputData['fields'][$userField['key']] = $userField['value'];
        }

        $this->authManager->guard()->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            '/user-field/update-or-create-multiple-by-key',
            $userFieldsInputData
        );

        // assert the users data was saved in the db
        foreach ($userFieldsData as $userField) {
            $this->assertDatabaseMissing(
                ConfigService::$tableUserFields,
                $userField
            );
        }

        // assert the session has the success message
        $response->assertSessionHasErrors(
            ["key",]
        );
    }

    public function test_users_field_update_multiple_by_key_create()
    {
        $userId = $this->createNewUser();

        $userFieldsData = [
            [
                'key' => $this->faker->word,
                'value' => $this->faker->words(4, true),
            ],
            [
                'key' => $this->faker->word,
                'value' => $this->faker->words(4, true),
            ],
            [
                'key' => $this->faker->word,
                'value' => $this->faker->words(4, true),
            ],
        ];

        $userFieldsInputData = [];

        foreach ($userFieldsData as $userField) {
            $userFieldsInputData['fields'][$userField['key']] = $userField['value'];
        }

        $this->authManager->guard()->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            '/user-field/update-or-create-multiple-by-key',
            $userFieldsInputData
        );

        // assert the users data was saved in the db
        foreach ($userFieldsData as $userField) {

            $this->assertDatabaseHas(
                ConfigService::$tableUserFields,
                $userField
            );
        }

        // assert the session has the success message
        $response->assertSessionHas('success', true);
    }

    public function test_users_field_update_by_key_create()
    {
        $userId = $this->createNewUser();

        $userFieldsInputData = [
            'key' => $this->faker->word,
            'value' => $this->faker->words(4, true),
        ];

        $this->authManager->guard()->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            '/user-field/update-or-create-by-key',
            $userFieldsInputData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldsInputData
        );

        // assert the session has the success message
        $response->assertSessionHas('success', true);
    }

    public function test_users_field_update_multiple_by_key_update()
    {
        $userId = $this->createNewUser();

        $userFieldsData = [
            [
                'key' => $this->faker->word,
                'value' => $this->faker->words(4, true),
            ],
            [
                'key' => $this->faker->word,
                'value' => $this->faker->words(4, true),
            ],
            [
                'key' => $this->faker->word,
                'value' => $this->faker->words(4, true),
            ],
        ];

        foreach ($userFieldsData as $userField) {
            $this->databaseManager->table(ConfigService::$tableUserFields)
                ->insertGetId(array_merge($userField, ['user_id' => $userId]));
        }

        $this->authManager->guard()->onceUsingId($userId);

        $userFieldsInputData = [];

        foreach ($userFieldsData as $userFieldIndex => $userField) {
            $userFieldsData[$userFieldIndex]['value'] = $this->faker->word;
            $userField['value'] = $userFieldsData[$userFieldIndex]['value'];

            $userFieldsInputData['fields'][$userField['key']] = $userField['value'];
        }

        $response = $this->call(
            'PATCH',
            '/user-field/update-or-create-multiple-by-key',
            $userFieldsInputData
        );

        // assert the users data was saved in the db
        foreach ($userFieldsData as $userField) {
            $this->assertDatabaseHas(
                ConfigService::$tableUserFields,
                $userField
            );
        }

        // assert the session has the success message
        $response->assertSessionHas('success', true);
    }

    public function test_users_field_delete_with_permission()
    {
        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'DELETE',
            '/user-field/delete/' . 1
        );

        // assert the user was removed from the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            [
                'id' => 1,
            ]
        );

        // assert the session has the success message
        $response->assertSessionHas('success', true);
    }

    public function test_users_field_delete_without_permission()
    {
        $response = $this->call(
            'DELETE',
            '/user-field/delete/' . 1
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the user was not removed from the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            [
                'id' => 1,
            ]
        );
    }
}
