<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Faker\ORM\Doctrine\Populator;
use Illuminate\Routing\Router;
use MikeMcLin\WpPassword\Facades\WpPassword;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Middleware\AuthenticatedOnly;
use Railroad\Usora\Tests\UsoraTestCase;
use ReflectionClass;

class AuthenticationControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $populator = new Populator($this->faker, $this->entityManager);

        $populator->addEntity(
            User::class,
            1,
            [
                'email' => 'login_user_test@email.com',
                'password' => 'Password12345!@',
            ]
        );
        $populator->execute();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class)], true);
    }

    public function test_authenticate_via_credentials_validation_failed()
    {
        $response = $this->call(
            'POST',
            'usora/authenticate/with-credentials',
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
                'usora/authenticate/with-credentials',
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
            'usora/authenticate/with-credentials',
            ['email' => 'login_user_test@email.com', 'password' => 'Password12345!@']
        );

        $this->assertEquals(
            $userId,
            $this->app->make('auth')
                ->guard()
                ->id()
        );

        $response->assertRedirect(config('usora.login_success_redirect_path'));
    }

    public function test_authenticate_via_remember_token()
    {
        $userId = 1;

        $response = $this->call(
            'POST',
            'usora/authenticate/with-credentials',
            ['email' => 'login_user_test@email.com', 'password' => 'Password12345!@', 'remember' => true]
        );

        $this->assertEquals(
            $userId,
            $this->app->make('auth')
                ->guard()
                ->id()
        );

        session()->flush();
        auth()
            ->guard()
            ->nullCurrentUser();

        $cookies = [];

        foreach (cookie()->getQueuedCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }

        $this->permissionServiceMock->method('can')
            ->willReturn(true);

        /**
         * @var $router Router
         */
        $router = $this->app['router'];

        $router->pushMiddlewareToGroup('test_logged_in_route_group', AuthenticatedOnly::class);

        $response = $this->call(
            'GET',
            'usora/json-api/user/index',
            [],
            $cookies
        );

        $this->assertEquals(200, $response->getStatusCode());

        $router->middlewareGroup('test_logged_in_route_group', []);
    }

    public function test_authenticate_via_verification_token_validation_failed()
    {
        $response = $this->call(
            'GET',
            'usora/authenticate/with-verification-token'
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
            'usora/authenticate/with-verification-token',
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
            'usora/authenticate/with-verification-token',
            ['uid' => 1, 'vt' => $this->hasher->make($user->getId() . $user->getPassword() . $user->getSessionSalt())]
        );

        $response->assertSeeText('');

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
            'usora/authenticate/with-third-party'
        );

        $response->assertRedirect(config('usora.login_success_redirect_path'));
    }

    public function test_authenticate_via_third_party()
    {
        $response = $this->call(
            'GET',
            'usora/authenticate/with-third-party'
        );

        $response->assertSeeText(config('usora.login_success_redirect_path'));
        $response->assertSeeText(config('usora.login_page_path'));

        foreach (config('usora.domains_to_check_for_authentication') as $domain) {
            $response->assertSeeText($domain);
        }
    }

    public function test_render_verification_token_via_post_message_no_logged_in_user()
    {
        $response = $this->call(
            'GET',
            'usora/authenticate/render-post-message-verification-token'
        );

        $response->assertSeeText("var failed = '1';");
    }

    public function test_render_verification_token_via_post_message()
    {
        $userId = 1;

        $user = auth()->loginUsingId($userId);

        $response = $this->call(
            'GET',
            'usora/authenticate/render-post-message-verification-token'
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
            'usora/authenticate/set-authentication-cookie'
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
            'usora/authenticate/set-authentication-cookie',
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
            'usora/authenticate/set-authentication-cookie',
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
            'usora/deauthenticate'
        );

        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    public function test_deauthenticate_with_remember()
    {
        $userId = 1;

        $user = auth()->loginUsingId($userId, true);

        $this->assertEquals(
            $userId,
            $this->app->make('auth')
                ->guard()
                ->id()
        );

        $cookies = [];

        foreach (cookie()->getQueuedCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }

        $response = $this->call(
            'GET',
            'usora/deauthenticate',
            [],
            $cookies
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
     * @throws \ReflectionException
     */
    public function test_authentication_via_credentials_wordpress_hash()
    {
        $rawPassword = $this->faker->word;

        $user = new User();
        $user->setEmail('wptest+1@test.com');
        $user->setDisplayName('wptestuser1');
        $user->setSessionSalt('wpsalt1');

        // we must use reflection to set a custom password hash since the regular hashing is built in to the setter
        $reflectionClass = new ReflectionClass($user);
        $reflectionProperty = $reflectionClass->getProperty('password');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($user, WpPassword::make($rawPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $userId = $user->getId();

        $response = $this->call(
            'POST',
            'usora/authenticate/with-credentials',
            ['email' => $user->getEmail(), 'password' => $rawPassword]
        );

        $this->assertEquals(
            $userId,
            $this->app->make('auth')
                ->guard()
                ->id()
        );

        $response->assertRedirect(config('usora.login_success_redirect_path'));
    }
}