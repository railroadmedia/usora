<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Railroad\Usora\DataFixtures\EmailChangeFixtureLoader;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Events\EmailChangeRequest;
use Railroad\Usora\Tests\UsoraTestCase;

class EmailChangeControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class), app(EmailChangeFixtureLoader::class)]);
    }

    public function test_request()
    {
        Event::fake();
        Notification::fake();

        $user = new User();

        $user->setEmail($this->faker->email);
        $password = $this->faker->word;
        $user->setPassword($password);
        $user->setDisplayName($this->faker->word);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->authManager->guard()
            ->onceUsingId($user->getId());

        $newEmail = $this->faker->email;

        $this->assertNotEquals($newEmail, $user->getEmail());

        $response = $this->call(
            'POST',
            'usora/email-change/request',
            [
                'email' => $newEmail,
                'user_password' => $password,
            ]
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
            ['successes']
        );

        // assert the request data was saved in db
        $this->assertDatabaseHas(
            config('usora.tables.email_changes'),
            [
                'user_id' => $user->getId(),
                'email' => $newEmail,
                'token' => $token,
            ]
        );

        // assert the email was sent and contains the confirmation token
        Notification::assertSentTo(
            (new AnonymousNotifiable)->route(config('usora.email_change_notification_channel'), $newEmail),
            config('usora.email_change_notification_class'),
            function ($notification) use ($token) {
                return $notification->token === $token;
            }
        );
    }

    public function test_request_validation_fail()
    {
        $response = $this->call(
            'POST',
            'usora/email-change/request',
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

        $response = $this->call(
            'POST',
            'usora/email-change/request',
            ['email' => 'test1@test.com']
        );

        $response->assertSessionHasErrors(
            ['user_password']
        );
    }

    public function test_confirmation()
    {
        $newEmail = 'test_change@test.com';

        $response = $this->call(
            'GET',
            'usora/email-change/confirm',
            ['code' => 'token1']
        );

        // assert the new email was saved in users table
        $this->assertDatabaseHas(
            config('usora.tables.users'),
            [
                'id' => 1,
                'email' => $newEmail,
            ]
        );

        // assert response code
        $this->assertEquals(302, $response->getStatusCode());

        // assert session message
        $response->assertSessionHas(
            ['successes']
        );
    }

    public function test_confirmation_validation_fail()
    {
        $response = $this->call(
            'GET',
            'usora/email-change/confirm',
            []
        );

        // assert session has error for missing token
        $response->assertSessionHasErrors(
            ['code']
        );

        app('session.store')->flush();

        $response = $this->call(
            'GET',
            'usora/email-change/confirm',
            ['code' => Str::random(40)]
        );

        // assert session has error for invalid token
        $response->assertSessionHasErrors(
            ['code']
        );

        app('session.store')->flush();

        $response = $this->call(
            'GET',
            'usora/email-change/confirm',
            ['code' => 'token2']
        );

        // assert session has error for expired token
        $response->assertSessionHasErrors(
            ['code']
        );
    }
}