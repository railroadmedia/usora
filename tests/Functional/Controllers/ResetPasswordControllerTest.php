<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Faker\ORM\Doctrine\Populator;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Str;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Tests\UsoraTestCase;

class ResetPasswordControllerTest extends UsoraTestCase
{
    /**
     * @var PasswordBroker
     */
    protected $passwordBroker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordBroker = $this->app->make(PasswordBroker::class);

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

    public function test_reset_password_validation_failed()
    {
        $response = $this->call(
            'POST',
            'usora/password/reset'
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
            'usora/password/reset',
            [
                'email' => 'test+1@test.com',
                'password' => $password,
                'password_confirmation' => $password,
                'token' => '123',
            ]
        );

        $this->assertFalse(auth()->attempt(['email' => 'test+1@test.com', 'password' => $password]));

        $response->assertSessionHasErrors(['password' => 'Password reset failed, please try again.',]);
    }

    public function test_reset_password()
    {
        $newPassword = Str::random(12);

        $user =
            $this->entityManager->getRepository(User::class)
                ->findOneBy(['email' => 'login_user_test@email.com']);

        $token = $this->passwordBroker->createToken($user);

        $this->assertTrue(auth()->attempt(['email' => $user->getEmail(), 'password' => 'Password12345!@']));

        $response = $this->call(
            'POST',
            'usora/password/reset',
            [
                'email' => $user->getEmail(),
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => $token,
            ]
        );

        $this->assertFalse(auth()->attempt(['email' => $user->getEmail(), 'password' => 'Password12345!@']));

        $this->assertTrue(auth()->attempt(['email' => $user->getEmail(), 'password' => $newPassword]));

    }

}