<?php

namespace Railroad\Usora\Tests\Functional;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Railroad\Usora\Events\EmailChangeRequest;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;
use Illuminate\Support\Facades\Event;
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
        Event::fake();
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

        $token = '';

        // assert event raised and get the token value from event
        Event::assertDispatched(
            EmailChangeRequest::class,
            function ($e) use ($newEmail, &$token) {
                $token = $e->token;
                return $e->email === $newEmail;
            }
        );

        // assert response code
        $this->assertEquals(302, $response->getStatusCode());

        // assert session message
        $response->assertSessionHas(
            ['success']
        );

        // assert the request data was saved in db
        $this->assertDatabaseHas(
            ConfigService::$tableEmailChanges,
            [
                'user_id' => $userId,
                'email' => $newEmail,
                'token' => $token
            ]
        );

        // assert the email was sent and contains the confirmation token
        Notification::assertSentTo(
            (new AnonymousNotifiable)
                ->route(ConfigService::$emailChangeNotificationChannel, $newEmail),
            ConfigService::$emailChangeNotificationClass,
            function ($notification) use ($token) {
                return $notification->token === $token;
            }
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

        $this->databaseManager->table(ConfigService::$tableUsers)
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

    public function test_confirmation()
    {
        $rawPassword = $this->faker->word;

        $user = [
            'email' => $this->faker->email,
            'password' => $this->hasher->make($rawPassword),
            'remember_token' => str_random(60),
            'session_salt' => str_random(60),
            'display_name' => $this->faker->words(4, true),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];

        $userId = $this->databaseManager->table(ConfigService::$tableUsers)
            ->insertGetId($user);

        $newEmail = $this->faker->email;

        $this->assertNotEquals($newEmail, $user['email']);

        $emailChangeData = [
            'user_id' => $userId,
            'email' => $newEmail,
            'token' => str_random(60),
            'created_at' => Carbon::now()->toDateTimeString()
        ];

        $this->databaseManager->table(ConfigService::$tableEmailChanges)
            ->insert($emailChangeData);

        $response = $this->call(
            'GET',
            '/email-change/confirm',
            ['token' => $emailChangeData['token']]
        );

        // assert the new email was saved in users table
        $this->assertDatabaseHas(
            ConfigService::$tableUsers,
            [
                'id' => $userId,
                'email' => $newEmail
            ]
        );

        // assert response code
        $this->assertEquals(302, $response->getStatusCode());

        // assert session message
        $response->assertSessionHas(
            ['success']
        );
    }

    public function test_confirmation_validation_fail()
    {
        $response = $this->call(
            'GET',
            '/email-change/confirm',
            []
        );

        // assert session has error for missing token
        $response->assertSessionHasErrors(
            ['token']
        );

        app('session.store')->flush();

        $response = $this->call(
            'GET',
            '/email-change/confirm',
            ['token' => Str::random(40)]
        );

        // assert session has error for invalid token
        $response->assertSessionHasErrors(
            ['token']
        );

        app('session.store')->flush();

        $carbonPastFormat = "-%d hours";
        $expiredCarbonString = sprintf($carbonPastFormat, ConfigService::$emailChangeTtl + 1);
        $expiredDateTimeString = Carbon::parse($expiredCarbonString)->toDateTimeString();

        $emailChangeData = [
            'user_id' => rand(),
            'email' => $this->faker->email,
            'token' => str_random(60),
            'created_at' => $expiredDateTimeString
        ];

        $this->databaseManager->table(ConfigService::$tableEmailChanges)
            ->insert($emailChangeData);

        $response = $this->call(
            'GET',
            '/email-change/confirm',
            ['token' => $emailChangeData['token']]
        );

        // assert session has error for expired token
        $response->assertSessionHasErrors(
            ['token']
        );
    }
}