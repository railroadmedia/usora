<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Faker\ORM\Doctrine\Populator;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Tests\Hydrators\UserFakeDataHydrator;
use Railroad\Usora\Tests\UsoraTestCase;
use Railroad\Usora\Transformers\UserTransformer;

class UserJsonControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->fakeDataHydrator = new UserFakeDataHydrator($this->entityManager);

        $populator = new Populator($this->faker, $this->entityManager);

        $populator->addEntity(
            User::class,
            1,
            [
                'email' => 'test_email_123@email.com',
            ]
        );
        $populator->execute();

        for ($x = 0; $x < 3; $x++) {
            $populator->addEntity(
                User::class,
                1,
                [
                    'displayName' => 'test' . $x,
                ]
            );
            $populator->execute();
        }

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class)], true);
    }

    public function test_users_index_with_permission()
    {
        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $request = [
            'per_page' => 3,
            'sort' => 'displayName',
        ];

        $responsePageTwo = $this->call(
            'GET',
            'usora/json-api/user/index',
            $request + ['page' => 2]
        );

        // assert response status code
        $this->assertEquals(200, $responsePageTwo->getStatusCode());

        $dataPageTwo = $responsePageTwo->decodeResponseJson()['data'];

        // assert response length
        $this->assertEquals($request['per_page'], count($dataPageTwo));

        // assert ascending order of display_name column
        for ($i = 0; $i < count($dataPageTwo) - 1; $i++) {
            $current = $dataPageTwo[$i];
            $next = $dataPageTwo[$i + 1];

            $cmp = strcmp($current['attributes']['display_name'], $next['attributes']['display_name']);

            $this->assertLessThanOrEqual(0, $cmp);
        }

        $responsePageOne = $this->call(
            'GET',
            'usora/json-api/user/index',
            $request + ['page' => 1]
        );

        $dataPageOne = $responsePageOne->decodeResponseJson()['data'];

        // assert response length
        $this->assertEquals($request['per_page'], count($dataPageOne));

        // assert ascending order of display_name column across pages
        $cmp = strcasecmp(
            $dataPageOne[count($dataPageOne) - 1]['attributes']['display_name'],
            $dataPageTwo[0]['attributes']['display_name']
        );
        $this->assertLessThanOrEqual(0, $cmp);
    }

    public function test_users_index_with_display_name_criteria()
    {
        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $request = [
            'per_page' => 3,
            'sort' => 'displayName',
            'search_term' => 'test',
        ];

        $responsePageTwo = $this->call(
            'GET',
            'usora/json-api/user/index',
            $request + ['page' => 2]
        );

        // assert response status code
        $this->assertEquals(200, $responsePageTwo->getStatusCode());

        $dataPageTwo = $responsePageTwo->decodeResponseJson()['data'];

        // assert response length
        $this->assertEquals(1, count($dataPageTwo));

        // assert ascending order of display_name column
        for ($i = 0; $i < count($dataPageTwo) - 1; $i++) {
            $current = $dataPageTwo[$i];
            $next = $dataPageTwo[$i + 1];
            $cmp = strcasecmp($current['attributes']['display_name'], $next['attributes']['display_name']);
            $this->assertLessThanOrEqual(0, $cmp);
        }

        $responsePageOne = $this->call(
            'GET',
            'usora/json-api/user/index',
            $request + ['page' => 1]
        );

        $dataPageOne = $responsePageOne->decodeResponseJson()['data'];

        // assert response length
        $this->assertEquals($request['per_page'], count($dataPageOne));

        // assert ascending order of display_name column across pages
        $cmp = strcasecmp(
            $dataPageOne[count($dataPageOne) - 1]['attributes']['display_name'],
            $dataPageTwo[0]['attributes']['display_name']
        );
        $this->assertLessThanOrEqual(0, $cmp);
    }

    public function test_users_index_with_email_criteria()
    {
        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $request = [
            'per_page' => 3,
            'order_by_column' => 'displayName',
            'order_by_direction' => 'asc',
            'search_term' => 'test_email_123',
        ];

        $responsePage = $this->call(
            'GET',
            'usora/json-api/user/index',
            $request + ['page' => 1]
        );

        // assert response status code
        $this->assertEquals(200, $responsePage->getStatusCode());

        $dataPage = $responsePage->decodeResponseJson()['data'];

        $this->assertEquals(1, count($dataPage));

    }

    public function test_users_show_with_permission()
    {
        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $response = $this->call(
            'GET',
            'usora/json-api/user/show/' . 1
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // assert the user data is subset of response
        $this->assertArraySubset(
            [
                'email' => 'test_email_123@email.com',
            ],
            $response->decodeResponseJson()['data']['attributes']
        );
    }

    public function test_users_show_without_permission()
    {
        $userId = 3;
        $this->authManager->guard()
            ->onceUsingId($userId);

        $response = $this->call(
            'GET',
            'usora/json-api/user/show/' . 1
        );

        // assert the response code is not found
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_users_store_with_permission()
    {
        $attributes = $this->fakeDataHydrator->getAttributeArray(User::class, new UserTransformer());

        $attributes['password'] = 'password12345';
        $attributes['email'] = 'test_email@test.com';

        unset($attributes['created_at']);
        unset($attributes['updated_at']);

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $response = $this->call(
            'PUT',
            'usora/json-api/user/store',
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => $attributes,
                ],
            ]
        );

        // assert response status code
        $this->assertEquals(201, $response->getStatusCode());

        // assert the users password was encrypted and saved, and that they can login
        $this->assertTrue(auth()->attempt(['email' => $attributes, 'password' => $attributes['password']]));

        // assert the user data is subset of response
        unset($attributes['id']);
        unset($attributes['password']);

        $this->assertArraySubset($attributes, $response->decodeResponseJson()['data']['attributes']);

        // assert the users data was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.users'),
            $attributes
        );
    }

    public function test_users_store_without_permission()
    {
        $attributes = $this->fakeDataHydrator->getAttributeArray(User::class, new UserTransformer());

        $attributes['password'] = 'password12345';

        $response = $this->call(
            'PUT',
            'usora/json-api/user/store',
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => $attributes,
                ],
            ]
        );

        // assert response status is not found
        $this->assertEquals(404, $response->getStatusCode());

        // assert the users data was not saved in the db
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
            [
                'display_name' => $attributes['display_name'],
                'email' => $attributes['email'],
            ]
        );

        $credentials = [
            'email' => $attributes['email'],
            'password' => $attributes['password'],
        ];

        // assert the users data can not be used to login
        $this->assertFalse(auth()->attempt($credentials));
    }

    public function test_users_store_validation_fail()
    {
        $response = $this->call(
            'PUT',
            'usora/json-api/user/store',
            []
        );

        // assert response status code
        $this->assertEquals(422, $response->getStatusCode());

        // assert response validation error messages
        $this->assertEquals(
            [
                [
                    "source" => "data.attributes.email",
                    "detail" => "The email field is required.",
                ],
                [
                    "source" => "data.attributes.display_name",
                    "detail" => "The display name field is required.",
                ],
                [
                    "source" => "data.attributes.password",
                    "detail" => "The password field is required.",
                ],
            ],
            $response->decodeResponseJson()['errors']
        );
    }

    public function test_user_update_with_owner_only()
    {
        $userId = 1;

        $this->authManager->guard()
            ->onceUsingId($userId);

        $newAttributes = $this->fakeDataHydrator->getAttributeArray(User::class, new UserTransformer());

        unset($newAttributes['created_at']);
        unset($newAttributes['updated_at']);
        unset($newAttributes['id']);

        // assert the new display name is different from existing
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
            [
                'id' => $userId,
                'display_name' => $newAttributes['display_name'],
            ]
        );

        $response = $this->call(
            'PATCH',
            'usora/json-api/user/update/' . $userId,
            [
                'type' => 'user',
                'id' => $userId,
                'data' => [
                    'attributes' => $newAttributes,
                ],
            ]
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        // assert the new email field was not saved in the db
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
            [
                'id' => $userId,
                'email' => $newAttributes['email'],
            ]
        );

        unset($newAttributes['email']);

        // assert the user data is subset of response
        $this->assertArraySubset(
            $newAttributes,
            $response->decodeResponseJson()['data']['attributes']
        );

        // assert the new display name was saved in the db
        $this->assertDatabaseHas(
            config('usora.tables.users'),
            [
                'id' => $userId,
                'display_name' => $newAttributes['display_name'],
            ]
        );
    }

    public function test_user_update_no_permission_non_matching_logged_in_id()
    {
        $this->authManager->guard()
            ->onceUsingId(1);

        $response = $this->call(
            'PATCH',
            'usora/json-api/user/update/' . 2,
            [
                'type' => 'user',
                'id' => 2,
                'data' => [
                    'attributes' => ['display_name' => 'test123'],
                ],
            ]
        );

        $this->assertEquals(404, $response->getStatusCode());

        $this->assertDatabaseMissing(
            config('usora.tables.users'),
            [
                'id' => 2,
                'display_name' => 'test123',
            ]
        );
    }

    public function test_user_update_with_permission_to_edit_any_user()
    {
        $userIdToUpdate = 1;
        $userIdLoggedIn = 2;

        $this->authManager->guard()
            ->onceUsingId($userIdLoggedIn);

        $this->permissionServiceMock->method('can')
            ->willReturnOnConsecutiveCalls(true, true);

        $this->authManager->guard()
            ->onceUsingId($userIdLoggedIn);

        $newAttributes = ['email' => 'new_test_email@test.com'];

        $response = $this->call(
            'PATCH',
            'usora/json-api/user/update/' . $userIdToUpdate,
            [
                'type' => 'user',
                'id' => $userIdToUpdate,
                'data' => [
                    'attributes' => $newAttributes,
                ],
            ]
        );

        // assert response status code
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertDatabaseHas(
            config('usora.tables.users'),
            [
                'id' => $userIdToUpdate,
                'email' => $newAttributes['email'],
            ]
        );

        // assert the user data is subset of response
        $this->assertArraySubset(
            $newAttributes,
            $response->decodeResponseJson()['data']['attributes']
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
            'usora/json-api/user/update/' . $userIdToUpdate,
            [
                'type' => 'user',
                'id' => $userIdToUpdate,
                'data' => [
                    'attributes' => ['display_name' => $newDisplayName],
                ],
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
        $userIdLoggedIn = 2;

        $this->authManager->guard()
            ->onceUsingId($userIdLoggedIn);

        $response = $this->call(
            'PATCH',
            'usora/json-api/user/update/' . $userIdLoggedIn,
            [
                'type' => 'user',
                'id' => 2,
                'data' => [
                    'attributes' => ['display_name' => 'a'],
                ],
            ]
        );

        // assert response status code
        $this->assertEquals(422, $response->getStatusCode());

        // assert response validation error messages
        $this->assertEquals(
            [
                [
                    'source' => 'data.attributes.display_name',
                    'detail' => 'The display name must be at least 2 characters.',
                ],
            ],
            $response->decodeResponseJson()['errors']
        );
    }

    public function test_user_delete_with_permission()
    {
        $userId = 1;
        $this->authManager->guard()
            ->onceUsingId($userId);

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $response = $this->call(
            'DELETE',
            'usora/json-api/user/delete/' . $userId
        );

        // assert the response code is no content
        $this->assertEquals(204, $response->getStatusCode());

        // assert the user was removed from the db
        $this->assertDatabaseMissing(
            config('usora.tables.users'),
            [
                'id' => $userId,
            ]
        );
    }

    public function test_user_delete_with_permission_no_content()
    {
        $userId = 100;

        $this->authManager->guard()
            ->onceUsingId($userId);

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        $response = $this->call(
            'DELETE',
            'usora/json-api/user/delete/' . $userId
        );

        // assert the response code is no content
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_user_delete_without_permission()
    {
        $userId = 1;
        $this->authManager->guard()
            ->onceUsingId(rand());
        $response = $this->call(
            'DELETE',
            'usora/json-api/user/delete/' . $userId
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
