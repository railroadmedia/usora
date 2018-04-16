<?php

namespace Railroad\Usora\Tests\Functional;

use Illuminate\Contracts\Hashing\Hasher;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class AuthenticationControllerTest extends UsoraTestCase
{
    /**
     * @var $hasher Hasher
     */
    protected $hasher;

    protected function setUp()
    {
        parent::setUp();

        $this->hasher = app()->make(Hasher::class);
    }

    public function test_authenticate_via_credentials_validation_fails()
    {
        $response = $this->call(
            'POST',
            '/authenticate/credentials',
            ['email' => 'fail', 'password' => '123']
        );

        $response->assertSessionHasErrors(['invalid-credentials']);

        $this->assertEmpty($this->app->make('auth')->guard()->id());
    }

    public function test_authenticate_via_credentials_too_many_attempts()
    {
        $user = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($this->faker->word),
            'remember_token' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userId = $this->databaseManager->table(ConfigService::$tableUsers)
            ->insertGetId($user);

        for ($i = 0; $i < 9; $i++) {
            $response = $this->call(
                'POST',
                '/authenticate/credentials',
                ['email' => $user['email'], 'password' => '123']
            );
        }

        $response->assertSessionHasErrors(['throttle']);

        $this->assertEmpty($this->app->make('auth')->guard()->id());
    }

    public function test_authenticate_via_credentials()
    {
        $rawPassword = $this->faker->word;

        $user = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($rawPassword),
            'remember_token' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userId = $this->databaseManager->table(ConfigService::$tableUsers)
            ->insertGetId($user);

        $response = $this->call(
            'POST',
            '/authenticate/credentials',
            ['email' => $user['email'], 'password' => $rawPassword]
        );

        $this->assertEquals($userId, $this->app->make('auth')->guard()->id());
        $response->assertRedirect(ConfigService::$loginSuccessRedirectPath);
    }

    public function test_verification_failed()
    {
        $userId = $this->databaseManager->table(ConfigService::$tableUsers)
            ->insertGetId(
                [
                    'email' => $this->faker->email,
                    'password' => $this->hasher->make($this->faker->word),
                    'remember_token' => str_random(60),
                    'display_name' => $this->faker->words(4, true),
                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );

        $response = $this->call(
            'GET',
            '/authenticate/token',
            ['uid' => $userId, 'v' => rand()]
        );

        $this->assertEmpty($this->app->make('auth')->guard()->id());
    }

    public function test_verification_succeeded()
    {
        $userData = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($this->faker->word),
            'remember_token' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $userData['id'] = $this->databaseManager->table(ConfigService::$tableUsers)
            ->insertGetId($userData);

        $response = $this->call(
            'GET',
            '/authenticate/token',
            [
                'uid' => $userData['id'],
                'v' => $this->hasher->make($userData['id'] . $userData['password'] . $userData['remember_token'])
            ]
        );

        $this->assertEquals($userData['id'], $this->app->make('auth')->guard()->id());
        $this->assertEquals($userData['remember_token'], $this->app->make('auth')->guard()->user()['remember_token']);
    }
}