<?php

namespace Railroad\Usora\Tests\Functional;

use Carbon\Carbon;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Str;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class ResetPasswordControllerTest extends UsoraTestCase
{
    /**
     * @var PasswordBroker
     */
    protected $passwordBroker;

    protected function setUp()
    {
        parent::setUp();

        $this->passwordBroker = $this->app->make(PasswordBroker::class);
        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class)]);
    }

    public function test_reset_password_validation_failed()
    {
        $response = $this->call(
            'POST',
            'password/reset'
        );

        $response->assertSessionHasErrors(
            [
                'token',
                'email',
                'password',
            ]
        );
    }

    public function test_reset_password_invalid_token()
    {
        $hashKey = Str::random(40);
        $password = Str::random(12);

        $response = $this->call(
            'POST',
            'password/reset',
            ['email' => 'test+1@test.com', 'password' => $password, 'password_confirmation' => $password, 'token' => '123']
        );

        $this->assertFalse(auth()->attempt(['email' => 'test+1@test.com', 'password' => $password]));

        $response->assertSessionHasErrors(['password' => 'Password reset failed, please try again.',]);
    }

    public function test_reset_password()
    {
        $newPassword = Str::random(12);

        $user =
            $this->entityManager->getRepository(User::class)
                ->find(1);

        $token = $this->passwordBroker->createToken($user);

        $response = $this->call(
            'POST',
            'password/reset',
            [
                'email' => $user->getEmail(),
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => $token,
            ]
        );

        $this->assertFalse(auth()->attempt(['email' => $user->getEmail(), 'password' => $user->getPassword()]));

        $this->assertTrue(auth()->attempt(['email' => $user->getEmail(), 'password' => $newPassword]));

    }

}