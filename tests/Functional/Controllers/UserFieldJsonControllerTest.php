<?php

namespace Railroad\Usora\Tests\Functional;

use Carbon\Carbon;
use Railroad\Usora\DataFixtures\UserFieldFixtureLoader;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\UserField;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class UserFieldJsonControllerTest extends UsoraTestCase
{
    const API_PREFIX = '/usora';

    protected function setUp()
    {
        parent::setUp();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class), app(UserFieldFixtureLoader::class)]);
    }

    public function test_users_field_update_by_key_create_with_permission()
    {
        $userId = rand();

        $this->authManager->guard()
            ->onceUsingId($userId);

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $userFieldsInputData = [
            'user_id' => 1,
            'key' => 'key+1',
            'value' => $this->faker->words(4, true),
        ];

        $response = $this->call(
            'PATCH',
            self::API_PREFIX . '/user-field/update-or-create-by-key',
            $userFieldsInputData
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldsInputData
        );
    }

    public function test_users_field_update_multiple_by_key_create_with_permission()
    {
        $userId = 1;

        $userFieldData = [
            'user_id' => $userId,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
        ];

        $userFieldsData = [
            [
                'key' => $userFieldData['key'],
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

        $userFieldsInputData = ['user_id' => $userId, 'fields' => []];

        foreach ($userFieldsData as $userField) {
            $userFieldsInputData['fields'][$userField['key']] = $userField['value'];
        }

        $this->authManager->guard()
            ->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            self::API_PREFIX . '/user-field/update-or-create-multiple-by-key',
            $userFieldsInputData
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // assert the users data was saved in the db
        foreach ($userFieldsData as $userField) {

            $this->assertDatabaseHas(
                ConfigService::$tableUserFields,
                $userField
            );
        }
    }

    public function test_users_field_update_multiple_by_key_create_validation_fail()
    {
        $userId = 1;

        $userFieldsData = [
            [
                'key' => $this->faker->word,
                'value' => $this->faker->words(4, true),
            ],
            [
                'key' => '',
                'value' => $this->faker->words(4, true),
            ],
            [
                'key' => $this->faker->word,
                'value' => $this->faker->words(4, true),
            ],
        ];

        $userFieldsInputData = ['user_id' => $userId, 'fields' => []];

        foreach ($userFieldsData as $userField) {
            $userFieldsInputData['fields'][$userField['key']] = $userField['value'];
        }

        $this->authManager->guard()
            ->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            self::API_PREFIX . '/user-field/update-or-create-multiple-by-key',
            $userFieldsInputData
        );

        // assert response status code
        $this->assertEquals(422, $response->getStatusCode());

        // assert response validation error messages
        $this->assertEquals(
            [
                [
                    "source" => "key",
                    "detail" => "The key field is required.",
                ],
            ],
            $response->decodeResponseJson()['errors']
        );

        // assert the users field data was not saved in the db
        foreach ($userFieldsData as $userField) {

            $this->assertDatabaseMissing(
                ConfigService::$tableUserFields,
                $userField
            );
        }
    }

    public function test_users_field_update_by_key_create_validation_fail()
    {
        $userId = 1;

        $userFieldsInputData = [
            'user_id' => $userId,
            'value' => $this->faker->words(4, true),
        ];

        $this->authManager->guard()
            ->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            self::API_PREFIX . '/user-field/update-or-create-by-key',
            $userFieldsInputData
        );

        // assert response status code
        $this->assertEquals(422, $response->getStatusCode());

        // assert response validation error messages
        $this->assertEquals(
            [
                [
                    "source" => "key",
                    "detail" => "The key field is required.",
                ],
            ],
            $response->decodeResponseJson()['errors']
        );

        // assert the users field data was not saved in the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            [
                'user_id' => $userId,
                'value' => $userFieldsInputData['value'],
            ]
        );
    }

    public function test_users_field_index_with_permission()
    {
        $userId = 1;
        $userFields = [
            [
                'key' => 'key+1',
                'id' => 1,
                'user' => [
                    'id' => '1',
                ],
                'value' => 'value 1',
            ],
            [
                'key' => 'key+2',
                'id' => 2,
                'user' => [
                    'id' => '1',
                ],
                'value' => 'value 2',
            ],
            [
                'key' => 'key+3',
                'id' => 3,
                'user' => [
                    'id' => '1',
                ],
                'value' => 'value 3',
            ],
        ];
        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $response = $this->call(
            'GET',
            self::API_PREFIX . '/user-field/index/' . $userId
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArraySubset($userFields, $response->decodeResponseJson());
    }

    public function test_users_field_show_with_permission()
    {
        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $response = $this->call(
            'GET',
            self::API_PREFIX . '/user-field/show/' . 1
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // assert the user data is subset of response
        $this->assertArraySubset(
            [
                'key' => 'key+1',
                'value' => 'value 1',
            ],
            $response->decodeResponseJson()
        );
    }

    public function test_users_field_show_without_permission()
    {
        $userId = 1;

        $response = $this->call(
            'GET',
            self::API_PREFIX . '/user-field/show/' . 1
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_users_field_store_with_permission()
    {
        $userId = 10;
        $this->authManager->guard()
            ->onceUsingId($userId);

        $userFieldData = [
            'user_id' => 1,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
        ];

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $response = $this->call(
            'PUT',
            self::API_PREFIX . '/user-field/store',
            $userFieldData
        );

        unset($userFieldData['user_id']);

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
        $userId = 2;
        $this->authManager->guard()
            ->onceUsingId($userId);

        $userFieldData = [
            'user_id' => 1,
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
        $this->assertEquals(
            [
                [
                    "source" => "key",
                    "detail" => "The key field is required.",
                ],
            ],
            $response->decodeResponseJson()['errors']
        );
    }

    public function test_users_field_update_with_permission()
    {
        $userId = 2;
        $this->authManager->guard()
            ->onceUsingId($userId);

        $userFieldData = [
            'user_id' => 1,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
        ];

        // assert new key/value generated
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            [
                'key' => $userFieldData['key'],
                'value' => $userFieldData['value'],
            ]
        );

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $response = $this->call(
            'PATCH',
            self::API_PREFIX . '/user-field/update/' . 1,
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        unset($userFieldData['user_id']);

        // assert the user data is subset of response
        $this->assertArraySubset($userFieldData, $response->decodeResponseJson());
    }

    public function test_users_field_update_with_owner()
    {
        $userId = 1;

        $userFieldData = [
            'user_id' => 1,
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
        ];

        // assert new key/value generated
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        $this->authManager->guard()
            ->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            self::API_PREFIX . '/user-field/update/' . 1,
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        unset($userFieldData['user_id']);
        // assert the user data is subset of response
        $this->assertArraySubset($userFieldData, $response->decodeResponseJson());
    }

    public function test_users_field_update_without_permission()
    {
        $userId = 2;

        $userFieldData = [
            'key' => $this->faker->word(),
            'value' => $this->faker->words(4, true),
        ];

        // assert new key/value generated
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            $userFieldData
        );

        $response = $this->call(
            'PATCH',
            self::API_PREFIX . '/user-field/update/' . 1,
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
        $userId = 2;
        $this->authManager->guard()
            ->onceUsingId($userId);

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $response = $this->call(
            'DELETE',
            self::API_PREFIX . '/user-field/delete/' . 1
        );

        // assert the user was removed from the db
        $this->assertDatabaseMissing(
            ConfigService::$tableUserFields,
            [
                'id' => 1,
            ]
        );

        // assert the response code is no content
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function test_users_field_delete_without_permission()
    {
        $userId = 2;
        $this->authManager->guard()
            ->onceUsingId($userId);

        $response = $this->call(
            'DELETE',
            self::API_PREFIX . '/user-field/delete/' . 1
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
