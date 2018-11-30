<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use MikeMcLin\WpPassword\Facades\WpPassword;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class AuthenticationControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class)]);
    }

    public function test_authenticate_via_credentials_validation_failed()
    {
        $response = $this->call(
            'POST',
            '/authenticate/credentials',
            ['email' => 'fail', 'password' => '123']
        );

        $response->assertSessionHasErrors(['invalid-credentials']);

        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    public function test_authenticate_via_credentials_too_many_attempts()
    {
        for ($i = 0; $i < 9; $i++) {
            $response = $this->call(
                'POST',
                '/authenticate/credentials',
                ['email' => 'test-1@test.com', 'password' => 'wrong-password']
            );
        }

        $response->assertSessionHasErrors(['throttle']);

        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    public function test_authenticate_via_credentials()
    {
        $userId = 1;

        $response = $this->call(
            'POST',
            '/authenticate/credentials',
            ['email' => 'test+1@test.com', 'password' => 'Password1#']
        );

        $this->assertEquals(
            $userId,
            $this->app->make('auth')
                ->guard()
                ->id()
        );
        $response->assertRedirect(ConfigService::$loginSuccessRedirectPath);
    }

    public function test_authenticate_via_verification_token_validation_failed()
    {
        $response = $this->call(
            'GET',
            '/authenticate/verification-token'
        );

        $response->assertSeeText('');
        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    public function test_authenticate_via_verification_token_user_doesnt_exist()
    {
        $response = $this->call(
            'GET',
            '/authenticate/verification-token',
            ['uid' => 1, 'vt' => '123']
        );

        $response->assertSeeText('');
        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    public function test_authenticate_via_verification_token()
    {
        $user =
            $this->entityManager->getRepository(User::class)
                ->find(1);

        $response = $this->call(
            'GET',
            '/authenticate/verification-token',
            ['uid' => 1, 'vt' => $this->hasher->make(1 . $user->getPassword() . 'salt1')]
        );

        $this->assertEquals(
            1,
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    public function test_authenticate_via_third_party_already_logged_in()
    {
        $userId = 1;

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
        $userId = 1;

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
        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    public function test_set_authentication_cookie_via_verification_token_invalid_token()
    {
        $userId = 1;

        $response = $this->call(
            'POST',
            '/authenticate/set-authentication-cookie',
            ['uid' => $userId, 'vt' => $this->hasher->make('123')]
        );

        $response->assertJson(['success' => 'false']);
        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    public function test_set_authentication_cookie_via_verification_token()
    {
        $user =
            $this->entityManager->getRepository(User::class)
                ->find(1);

        $response = $this->call(
            'POST',
            '/authenticate/set-authentication-cookie',
            [
                'uid' => $user->getId(),
                'vt' => $this->hasher->make($user->getId() . $user->getPassword() . $user->getSessionSalt()),
            ]
        );

        $response->assertJson(['success' => 'true']);
        $this->assertEquals(
            $user->getId(),
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    public function test_deauthenticate()
    {
        $userId = 1;

        $user = auth()->loginUsingId($userId);

        $this->assertEquals(
            $userId,
            $this->app->make('auth')
                ->guard()
                ->id()
        );

        $response = $this->call(
            'GET',
            '/deauthenticate'
        );

        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test_authentication_via_credentials_wordpress_hash()
    {
        $rawPassword = $this->faker->word;

        $user = new User();
        $user->setEmail('wptest+1@test.com');
        $user->setDisplayName('wptestuser1');
        $user->setPassword(WpPassword::make($rawPassword));
        $user->setSessionSalt('wpsalt1');

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $userId = $user->getId();

        $response = $this->call(
            'POST',
            '/authenticate/credentials',
            ['email' => $user->getEmail(), 'password' => $rawPassword]
        );

        $this->assertEquals(
            $userId,
            $this->app->make('auth')
                ->guard()
                ->id()
        );
        $response->assertRedirect(ConfigService::$loginSuccessRedirectPath);
    }
}