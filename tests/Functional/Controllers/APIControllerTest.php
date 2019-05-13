<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Faker\ORM\Doctrine\Populator;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Tests\UsoraTestCase;

class APIControllerTest extends UsoraTestCase
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

        $this->assertArrayHasKey('token', $response->decodeResponseJson());
        $this->assertArrayHasKey('userId', $response->decodeResponseJson());
        $this->assertEquals('bearer', $response->decodeResponseJson()['tokenType']);
        $this->assertEquals(3600, $response->decodeResponseJson()['expiresIn']);
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
        $this->assertArrayNotHasKey('token', $response->decodeResponseJson());
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

        $token = $login->decodeResponseJson()['token'];

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

        $token = $response->decodeResponseJson()['token'];

        $result = $this->call(
            'PUT',
            'usora/api/me',
            [
                'token' => $token,
            ]
        );

        $this->assertArraySubset(['email' => 'login_user_test@email.com'], $result->decodeResponseJson());
    }
}