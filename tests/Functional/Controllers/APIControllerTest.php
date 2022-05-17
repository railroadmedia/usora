<?php

namespace Railroad\Usora\Tests\Functional;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Faker\ORM\Doctrine\Populator;
use Illuminate\Notifications\AnonymousNotifiable;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Tests\UsoraTestCase;

class APIControllerTest extends UsoraTestCase
{
    use ArraySubsetAsserts;

    protected function setUp(): void
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

    public function test_login_token()
    {
        $rawPassword = 'Password12345!@';

        $response = $this->call(
            'PUT',
            'usora/api/login',
            [
                'email' => 'login_user_test@email.com',
                'password' => $rawPassword,
            ]
        );

        $response->assertJson(['success' => 'true']);

        $this->assertArrayHasKey('token', $response->json());
        $this->assertArrayHasKey('userId', $response->json());
        $this->assertEquals('bearer', $response->json()['tokenType']);
        $this->assertEquals(3600, $response->json()['expiresIn']);
    }

    public function test_invalid_credentials_auth()
    {
        $response = $this->call(
            'PUT',
            'usora/api/login',
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
        $this->assertArrayNotHasKey('token', $response->json());
    }

    public function test_logout()
    {
        $rawPassword = 'Password12345!@';

        $login = $this->call(
            'PUT',
            'usora/api/login',
            [
                'email' => 'login_user_test@email.com',
                'password' => $rawPassword,
            ]
        );

        $token = $login->json()['token'];

        $result = $this->call(
            'PUT',
            'usora/api/logout',
            [
                'token' => $token,
            ]
        );

        $result->assertJson(['success' => 'true']);
    }

    public function test_get_auth_user()
    {
        $rawPassword = 'Password12345!@';

        $response = $this->call(
            'PUT',
            'usora/api/login',
            [
                'email' => 'login_user_test@email.com',
                'password' => $rawPassword,
            ]
        );

        $token = $response->json()['token'];

        $result = $this->call(
            'PUT',
            'usora/api/profile',
            [
                'token' => $token,
            ]
        );

        $this->assertArraySubset(['email' => 'login_user_test@email.com'], $result->json());
    }

    public function test_send_reset_link_email_validation_failed()
    {
        $response = $this->call(
            'PUT',
            'usora/api/forgot',
            ['email' => '123']
        );

        $this->assertStringContainsString('Incorrect email address', $response->getContent());
    }

    public function test_send_reset_link_email()
    {
        $response = $this->call(
            'PUT',
            'usora/api/forgot',
            ['email' => 'login_user_test@email.com']
        );

        $this->notificationFake->assertSentTo(
            new AnonymousNotifiable(),
            config('usora.password_reset_notification_class')
        );

        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

    public function test_is_email_unique()
    {
        $response = $this->call(
            'GET',
            'usora/is-email-unique'
        );

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_is_email_unique_when_email_exists()
    {
        $response = $this->call(
            'GET',
            'usora/is-email-unique',
            [
                'email' => 'login_user_test@email.com'
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->json('exists'));
    }

    public function test_is_email_unique_when_email_not_exists()
    {
        $response = $this->call(
            'GET',
            'usora/is-email-unique',
            [
                'email' => 'login_usedr_test@email.com'
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertFalse($response->json('exists'));
    }
}