<?php

namespace Railroad\Usora\Tests\Functional;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;
use Railroad\Usora\Notifications\EmailChange;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\AnonymousNotifiable;

class EmailChangeControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_request()
    {
        Notification::fake();

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

        $this->authManager->guard()->onceUsingId($userId);

        $newEmail = $this->faker->email;

        $this->assertNotEquals($newEmail, $user['email']);

        $response = $this->call(
            'POST',
            '/email-change/request',
            ['email' => $newEmail]
        );

        // assert response code
        $this->assertEquals(302, $response->getStatusCode());

        // assert session message
        $response->assertSessionHas(
            ['success']
        );

        // assert the request was saved in db
        $this->assertDatabaseHas(
            ConfigService::$tableEmailChanges,
            [
                'user_id' => $userId,
                'email' => $newEmail
            ]
        );

        // assert the email was sent
        Notification::assertSentTo(
            (new AnonymousNotifiable)
                ->route(ConfigService::$emailChangeNotificationChannel, $newEmail),
            ConfigService::$emailChangeNotificationClass
        );
    }

    public function test_request_validation_fail()
    {
        $response = $this->call(
            'POST',
            '/email-change/request',
            []
        );

        $response->assertSessionHasErrors(
            ['email']
        );

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
            '/email-change/request',
            ['email' => $user['email']]
        );

        $response->assertSessionHasErrors(
            ['email']
        );
    }

    // public function test_confirmation()
    // {
    //     // to be implemented
    // }

    // public function test_confirmation_validation_fail()
    // {
    //     $response = $this->call(
    //         'GET',
    //         '/email-change/confirm'
    //     );

    //     $response->assertSessionHasErrors(
    //         ['token']
    //     );

    //     $response = $this->call(
    //         'GET',
    //         '/email-change/confirm',
    //         ['token' => Str::random(40)]
    //     );

    //     $response->assertSessionHasErrors(
    //         ['token']
    //     );
    // }
}