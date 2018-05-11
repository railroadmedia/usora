<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class UserControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();
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

    // todo: store user without permission
    // todo: store user validation fails

    // todo: update with and without permission
    // todo: update validation fails

    // todo: delete with and without permission

    public function test_users_update()
    {
//        $this->permissionServiceMockBuilder->getMock()->method('can')->willReturn(true);
//        $userId = $this->createNewUser();
//
//        $initialUserData = $this->userRepository->read($userId);
//
//        $newDisplayName = $this->faker->words(4, true);
//
//        // confirm a different new display name is generated
//        $this->assertNotEquals($initialUserData->display_name, $newDisplayName, "Failed to generate new display name");
//
//        $this->createUserAccess($userId, 'users.update');
//
//        $this->authManager->guard()->onceUsingId($userId);
//
//        $response = $this->call(
//            'POST',
//            '/users/update/' . $userId,
//            [
//                'display_name' => $newDisplayName,
//                'email' => $this->faker->email,
//            ]
//        );
//
//        $this->assertEquals(200, $response->getStatusCode(), "Update request failed");
//
//        $updatedUserData = $this->userRepository->read($userId);
//
//        // confirm new display name is set in database
//        $this->assertEquals(
//            $updatedUserData->display_name,
//            $newDisplayName,
//            "Failed to set new display name in database"
//        );
//
//        // confirm email field not changed
//        $this->assertEquals($updatedUserData->email, $initialUserData->email);
//
//        // confirm updated database user is returned in response
//        $this->assertArraySubset($response->decodeResponseJson(), $updatedUserData->getArrayCopy());
    }
}
