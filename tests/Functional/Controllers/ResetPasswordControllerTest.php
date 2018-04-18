<?php

namespace Railroad\Usora\Tests\Functional;

use Carbon\Carbon;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Str;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

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

        $user = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($password),
            'remember_token' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'updated_at' => time(),
        ];

        $userId = $this->databaseManager->table(ConfigService::$tableUsers)
            ->insertGetId($user);

        $this->databaseManager->connection(ConfigService::$databaseConnectionName)
            ->table(ConfigService::$tablePasswordResets)
            ->insert(
                [
                    'email' => $user['email'],
                    'token' => hash_hmac('sha256', Str::random(40), $hashKey),
                    'created_at' => Carbon::now()->toDateTimeString(),
                ]
            );

        $response = $this->call(
            'POST',
            'password/reset',
            ['email' => $user['email'], 'password' => $password, 'password_confirmation' => $password, 'token' => '123']
        );

        $this->assertTrue(auth()->attempt(['email' => $user['email'], 'password' => $password]));

        $response->assertSessionHasErrors(['password' => 'Password reset failed, please try again.',]);
    }

    public function test_reset_password()
    {
        $password = Str::random(12);
        $newPassword = Str::random(12);

        $user = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($password),
            'remember_token' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'updated_at' => time(),
        ];

        $userId = $this->databaseManager->table(ConfigService::$tableUsers)
            ->insertGetId($user);

        $userEntity = new User();
        $userEntity['email'] = $user['email'];

        $token = $this->passwordBroker->createToken($userEntity);

        $response = $this->call(
            'POST',
            'password/reset',
            [
                'email' => $user['email'],
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => $token,
            ]
        );

        $this->assertTrue(auth()->attempt(['email' => $user['email'], 'password' => $newPassword]));
    }

}