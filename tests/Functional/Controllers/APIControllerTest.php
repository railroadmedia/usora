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
            'POST',
            'api/login',
            [
                'email' => $user['email'],
                'password' => $rawPassword,
            ]
        );
        $response->assertJson(['success' => 'true']);

        $this->assertArrayHasKey('token',$response->decodeResponseJson());
    }
}
