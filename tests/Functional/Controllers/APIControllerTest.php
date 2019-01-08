<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class APIControllerTest extends UsoraTestCase
{
    public function test_login_token()
    {
        $rawPassword = 'test123';
        $user = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($rawPassword),
            'remember_token' => str_random(60),
            'session_salt' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userId =
            $this->databaseManager->table(ConfigService::$tableUsers)
                ->insertGetId($user);

        $response = $this->call(
            'PUT',
            'api/login',
            [
                'email' => $user['email'],
                'password' => $rawPassword,
            ]
        );
        $response->assertJson(['success' => 'true']);

        $this->assertArrayHasKey('token', $response->decodeResponseJson());
        $this->assertArrayHasKey('userId', $response->decodeResponseJson());
    }

    public function test_invalid_credentials_auth()
    {
        $response = $this->call(
            'PUT',
            'api/login',
            [
                'email' => $this->faker->email,
                'password' => $this->faker->word,
            ]
        );
        $response->assertJson(
            [
                'success' => false,
                'message' => 'Invalid Email or Password',
            ]
        );

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertArrayNotHasKey('token', $response->decodeResponseJson());
    }

    public function test_logout()
    {
        $rawPassword = 'test123';
        $user = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($rawPassword),
            'remember_token' => str_random(60),
            'session_salt' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userId =
            $this->databaseManager->table(ConfigService::$tableUsers)
                ->insertGetId($user);

        $login = $this->call(
            'PUT',
            'api/login',
            [
                'email' => $user['email'],
                'password' => $rawPassword,
            ]
        );

        $token = $login->decodeResponseJson()['token'];

        $result = $this->call(
            'PUT',
            'api/logout',
            [
                'token' => $token
            ]
        );

        $result->assertJson(['success' => 'true']);
    }

    public function test_get_auth_user()
    {
        $rawPassword = 'test123';
        $user = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($rawPassword),
            'remember_token' => str_random(60),
            'session_salt' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userId =
            $this->databaseManager->table(ConfigService::$tableUsers)
                ->insertGetId($user);

        $login = $this->call(
            'PUT',
            'api/login',
            [
                'email' => $user['email'],
                'password' => $rawPassword,
            ]
        );

        $token = $login->decodeResponseJson()['token'];

        $result = $this->call(
            'PUT',
            'api/me',
            [
                'token' => $token
            ]
        );

        $this->assertArraySubset($user, $result->decodeResponseJson()['user']);
    }
}
