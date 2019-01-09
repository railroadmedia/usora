<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\DataFixtures\UserFieldFixtureLoader;
use Railroad\Usora\DataFixtures\UserFixtureLoader;

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
            'usora/user-field/store',
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.user_fields'),
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
            'usora/user-field/store',
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.user_fields'),
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
            'usora/user-field/store',
            $userFieldData
        );

        // assert the users data was not saved in the db
        $this->assertDatabaseMissing(
            config('usora.tables.user_fields'),
            $userFieldData
        );

        // assert response status is not found
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_users_field_store_validation_fail()
    {
        $response = $this->call(
            'PUT',
            'usora/user-field/store',
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
            config('usora.tables.user_fields'),
            [
                'id' => 1,
                'key' => $userFieldData['key'],
                'value' => $userFieldData['value'],
            ]
        );

        $this->permissionServiceMock->method('can')->willReturn(true);

        $response = $this->call(
            'PATCH',
            'usora/user-field/update/' . 1,
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.user_fields'),
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
            config('usora.tables.user_fields'),
            $userFieldData
        );

        $response = $this->call(
            'PATCH',
            'usora/user-field/update/' . 1,
            $userFieldData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.user_fields'),
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
            config('usora.tables.user_fields'),
            $userFieldData
        );

        $response = $this->call(
            'PATCH',
            'usora/user-field/update/' . 1,
            $userFieldData
        );

        // assert response status is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the new user field data was not saved in the db
        $this->assertDatabaseMissing(
            config('usora.tables.user_fields'),
            $userFieldData
        );
    }

    public function test_users_field_update_multiple_by_key_create_validation_fails()
    {
        $userId = 1;
        $this->authManager->guard()
            ->onceUsingId($userId);

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

       // $this->authManager->guard()->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            'usora/user-field/update-or-create-multiple-by-key',
            $userFieldsInputData
        );

        // assert the users data was saved in the db
        foreach ($userFieldsData as $userField) {
            $this->assertDatabaseMissing(
                config('usora.tables.user_fields'),
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
        $userId = 1;

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
            'usora/user-field/update-or-create-multiple-by-key',
            $userFieldsInputData
        );

        // assert the users data was saved in the db
        foreach ($userFieldsData as $userField) {

            $this->assertDatabaseHas(
                config('usora.tables.user_fields'),
                $userField
            );
        }

        // assert the session has the success message
        $response->assertSessionHas('success', true);
    }

    public function test_users_field_update_by_key_create()
    {
        $userId = 1;

        $userFieldsInputData = [
            'key' => $this->faker->word,
            'value' => $this->faker->words(4, true),
        ];

        $this->authManager->guard()->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            'usora/user-field/update-or-create-by-key',
            $userFieldsInputData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.user_fields'),
            $userFieldsInputData
        );

        // assert the session has the success message
        $response->assertSessionHas('success', true);
    }

    public function test_users_field_update_multiple_by_key_update()
    {
        $userId = 1;

        $userFieldsData = [
            [
                'key' => 'key+1',
                'value' => 'value 1',
            ],
            [
                'key' => 'key+2',
                'value' => 'value 2',
            ],
            [
                'key' => 'key+3',
                'value' => 'value 3',
            ],
        ];

        $this->authManager->guard()->onceUsingId($userId);

        foreach ($userFieldsData as $userFieldIndex => $userField) {
            $userFieldsData[$userFieldIndex]['value'] = $this->faker->word;
            $userField['value'] = $userFieldsData[$userFieldIndex]['value'];

            $userFieldsInputData['fields'][$userField['key']] = $userField['value'];
        }

        $response = $this->call(
            'PATCH',
            'usora/user-field/update-or-create-multiple-by-key',
            $userFieldsInputData
        );

        // assert the users data was saved in the db
        foreach ($userFieldsData as $userField) {
            $this->assertDatabaseHas(
                config('usora.tables.user_fields'),
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
            'usora/user-field/delete/' . 1
        );

        // assert the user was removed from the db
        $this->assertDatabaseMissing(
            config('usora.tables.user_fields'),
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
            'usora/user-field/delete/' . 1
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the user was not removed from the db
        $this->assertDatabaseHas(
            config('usora.tables.user_fields'),
            [
                'id' => 1,
            ]
        );
    }

    public function test_users_field_update_by_key()
    {
        $userId = 1;

        $userFieldsInputData = [
            'key' => 'key+1',
            'value' => $this->faker->words(4, true),
        ];

        $this->authManager->guard()->onceUsingId($userId);

        $response = $this->call(
            'PATCH',
            'usora/user-field/update-or-create-by-key',
            $userFieldsInputData
        );

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.user_fields'),
            $userFieldsInputData
        );

        // assert the session has the success message
        $response->assertSessionHas('success', true);
    }
}
