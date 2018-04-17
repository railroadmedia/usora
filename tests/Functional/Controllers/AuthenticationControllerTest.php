<?php

namespace Railroad\Usora\Tests\Functional;

use Illuminate\Contracts\Hashing\Hasher;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class AuthenticationControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_authenticate_via_credentials_validation_failed()
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

    public function test_authenticate_via_verification_token_validation_failed()
    {
        $response = $this->call(
            'GET',
            '/authenticate/verification-token'
        );

        $response->assertSeeText('');
        $this->assertEmpty($this->app->make('auth')->guard()->id());
    }

    public function test_authenticate_via_verification_token_user_doesnt_exist()
    {
        $response = $this->call(
            'GET',
            '/authenticate/verification-token',
            ['uid' => 1, 'vt' => '123']
        );

        $response->assertSeeText('');
        $this->assertEmpty($this->app->make('auth')->guard()->id());
    }

    public function test_authenticate_via_verification_token()
    {
        $rawPassword = $this->faker->word;

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

        $response = $this->call(
            'GET',
            '/authenticate/verification-token',
            ['uid' => 1, 'vt' => $this->hasher->make($userId . $user['password'] . $user['session_salt'])]
        );

        $this->assertEquals($userId, $this->app->make('auth')->guard()->id());
    }

    public function test_authenticate_via_third_party_already_logged_in()
    {
        $rawPassword = $this->faker->word;

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

        $response = $this->call(
            'GET',
            '/authenticate/third-party'
        );

        $response->assertRedirect(ConfigService::$loginSuccessRedirectPath);
    }

    public function test_authenticate_via_third_party()
    {
        $response = $this->call(
            'GET',
            '/authenticate/third-party'
        );

        $response->assertSeeText(ConfigService::$loginSuccessRedirectPath);
        $response->assertSeeText(ConfigService::$loginPagePath);

        foreach (ConfigService::$domainsToCheckForAuthenticateOn as $domain) {
            $response->assertSeeText($domain);
        }
    }

    public function test_render_verification_token_via_post_message_no_logged_in_user()
    {
        $response = $this->call(
            'GET',
            '/authenticate/post-message-verification-token'
        );

        $response->assertSeeText("var failed = '1';");
    }

    public function test_render_verification_token_via_post_message()
    {
        $rawPassword = $this->faker->word;

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

        $user = auth()->loginUsingId($userId);

        $response = $this->call(
            'GET',
            '/authenticate/post-message-verification-token'
        );

        $response->assertSeeText(
            "var token = "
        );
        $response->assertSeeText("var userId = '" . $userId . "';");
        $response->assertSeeText("var failed = '';");
    }

    public function test_set_authentication_cookie_via_verification_token_validated_failed()
    {
        $response = $this->call(
            'POST',
            '/authenticate/set-authentication-cookie'
        );

        $response->assertSeeText('');
        $this->assertEmpty($this->app->make('auth')->guard()->id());
    }

    public function test_set_authentication_cookie_via_verification_token_invalid_token()
    {
        $rawPassword = $this->faker->word;

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

        $response = $this->call(
            'POST',
            '/authenticate/set-authentication-cookie',
            ['uid' => $userId, 'vt' => $this->hasher->make('123')]
        );

        $response->assertJson(['success' => 'false']);
        $this->assertEmpty($this->app->make('auth')->guard()->id());
    }

    public function test_set_authentication_cookie_via_verification_token()
    {
        $rawPassword = $this->faker->word;

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

        $response = $this->call(
            'POST',
            '/authenticate/set-authentication-cookie',
            ['uid' => $userId, 'vt' => $this->hasher->make($userId . $user['password'] . $user['session_salt'])]
        );

        $response->assertJson(['success' => 'true']);
        $this->assertEquals($userId, $this->app->make('auth')->guard()->id());
    }

    public function test_deauthenticate()
    {
        $rawPassword = $this->faker->word;

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

        $user = auth()->loginUsingId($userId);

        $this->assertEquals($userId, $this->app->make('auth')->guard()->id());

        $response = $this->call(
            'GET',
            '/deauthenticate'
        );

        $this->assertEmpty($this->app->make('auth')->guard()->id());
    }
}