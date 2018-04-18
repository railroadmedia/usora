<?php

namespace Railroad\Usora\Tests\Functional;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class ForgotPasswordControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_send_reset_link_email_validation_failed()
    {
        $response = $this->call(
            'POST',
            'password/send-reset-email',
            ['email' => '123']
        );

        $response->assertSessionHasErrors(['email']);
    }

    public function test_send_reset_link_email()
    {
        $user = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($this->faker->word),
            'remember_token' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'updated_at' => time(),
        ];

        $userId = $this->databaseManager->table(ConfigService::$tableUsers)
            ->insertGetId($user);

        $userEntity = new User();
        $userEntity['id'] = $userId;

        $response = $this->call(
            'POST',
            'password/send-reset-email',
            ['email' => $user['email']]
        );

        $this->notificationFake->assertSentTo(new AnonymousNotifiable(), ConfigService::$passwordResetNotificationClass);

        $this->assertEmpty($this->app->make('auth')->guard()->id());
    }

}