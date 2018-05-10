<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\Tests\UsoraTestCase;

class UserControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_users_update()
    {
        $userId = $this->createNewUser();

        $initialUserData = $this->userRepository->read($userId);

        $newDisplayName = $this->faker->words(4, true);

        // confirm a different new display name is generated
         $this->assertNotEquals($initialUserData->display_name, $newDisplayName, "Failed to generate new display name");

        $this->createUserAccess($userId, 'users.update');

        $this->authManager->guard()->onceUsingId($userId);

        $response = $this->call(
            'POST',
            '/users/update/' . $userId,
            [
                'display_name' => $newDisplayName,
                'email' => $this->faker->email
            ]
        );

        $this->assertEquals(200, $response->getStatusCode(), "Update request failed");

        $updatedUserData = $this->userRepository->read($userId);

        // confirm new display name is set in database
        $this->assertEquals($updatedUserData->display_name, $newDisplayName, "Failed to set new display name in database");

        // confirm email field not changed
        $this->assertEquals($updatedUserData->email, $initialUserData->email);

        // confirm updated database user is returned in response
        $this->assertArraySubset($response->decodeResponseJson(), $updatedUserData->getArrayCopy());
    }
}
