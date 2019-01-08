<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class APIControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class)]);
    }

    public function test_login_token()
    {
        $rawPassword = 'Password1#';

        $response = $this->call(
            'PUT',
            'api/login',
            [
                'email' => 'test+1@test.com',
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
        $rawPassword = 'Password1#';

        $login = $this->call(
            'PUT',
            'api/login',
            [
                'email' => 'test+1@test.com',
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
        $rawPassword = 'Password1#';

        $response = $this->call(
            'PUT',
            'api/login',
            [
                'email' => 'test+1@test.com',
                'password' => $rawPassword,
            ]
        );

        $token = $response->decodeResponseJson()['token'];

        $result = $this->call(
            'PUT',
            'api/me',
            [
                'token' => $token
            ]
        );

        $this->assertArraySubset(['email' => 'test+1@test.com'], $result->decodeResponseJson());
    }
}