<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class PasswordControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_users_store_with_permission()
    {
        $rawPassword = $this->faker->words(3, true);

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

        auth()->loginUsingId($userId);

        $this->permissionServiceMock->method('can')->willReturn(true);

        $newPassword = $this->faker->words(3, true);

        $this->assertTrue(auth()->attempt(['email' => $user['email'], 'password' => $rawPassword]));

        $response = $this->call(
            'PATCH',
            '/user/update-password',
            [
                'current_password' => $rawPassword,
                'new_password' => $newPassword,
                'new_password_confirmation' => $newPassword,
            ]
        );

        $this->assertFalse(auth()->attempt(['email' => $user['email'], 'password' => $rawPassword]));
        $this->assertTrue(auth()->attempt(['email' => $user['email'], 'password' => $newPassword]));
    }
}
