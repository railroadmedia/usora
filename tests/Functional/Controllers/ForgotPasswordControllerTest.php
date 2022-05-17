<?php

namespace Railroad\Usora\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Faker\ORM\Doctrine\Populator;
use Illuminate\Notifications\AnonymousNotifiable;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Tests\UsoraTestCase;

class ForgotPasswordControllerTest extends UsoraTestCase
{
    protected function setUp(): void
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
        $response = $this->call(
            'POST',
            'usora/password/send-reset-email',
            ['email' => 'login_user_test@email.com']
        );

        $this->notificationFake->assertSentTo(
            new AnonymousNotifiable(),
            config('usora.password_reset_notification_class')
        );

        $this->assertEmpty(
            $this->app->make('auth')
                ->guard()
                ->id()
        );
    }

}