<?php

namespace Railroad\Usora\Tests\Functional;

use Illuminate\Notifications\AnonymousNotifiable;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class ForgotPasswordControllerTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class)]);
    }

    public function test_send_reset_link_email_validation_failed()
    {
        $response = $this->call(
            'POST',
            'usora/password/send-reset-email',
            ['email' => '123']
        );

        $response->assertSessionHasErrors(['email']);
    }

    public function test_send_reset_link_email()
    {
        $this->call(
            'POST',
            'usora/password/send-reset-email',
            ['email' => 'test+1@test.com']
        );

        $this->notificationFake->assertSentTo(
            new AnonymousNotifiable(),
            ConfigService::$passwordResetNotificationClass
        );

        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

}