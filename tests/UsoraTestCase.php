<?php

namespace Railroad\Usora\Tests;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Mail\Mailer;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Testing\Fakes\MailFake;
use Illuminate\Support\Testing\Fakes\NotificationFake;
use Orchestra\Testbench\TestCase;
use Railroad\Usora\Providers\UsoraServiceProvider;
use Railroad\Usora\Repositories\RepositoryBase;

class UsoraTestCase extends TestCase
{
    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * @var AuthManager
     */
    protected $authManager;

    /**
     * @var BcryptHasher
     */
    protected $hasher;

    /**
     * @var MailFake
     */
    protected $mailFake;

    /**
     * @var NotificationFake
     */
    protected $notificationFake;

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate:fresh', []);
        $this->artisan('cache:clear', []);

        $this->faker = $this->app->make(Generator::class);
        $this->databaseManager = $this->app->make(DatabaseManager::class);
        $this->authManager = $this->app->make(AuthManager::class);
        $this->hasher = $this->app->make(BcryptHasher::class);
        $this->notificationFake = Notification::fake();

        Mail::fake();
        $this->mailFake = Mail::getFacadeRoot();

        Carbon::setTestNow(Carbon::now());
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $defaultConfig = require(__DIR__ . '/../config/usora.php');

        foreach ($defaultConfig as $key => $value) {
            config()->set('usora.' . $key, $value);
        }

        // set database
        config()->set('usora.data_mode', 'host');
        config()->set('usora.database_connection_name', config('usora.connection_mask_prefix') . 'sqlite');
        config()->set('usora.authentication_controller_middleware', []);
        config()->set('database.default', config('usora.connection_mask_prefix') . 'sqlite');
        config()->set(
            'database.connections.' . config('usora.connection_mask_prefix') . 'sqlite',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]
        );

        // set auth to our custom provider
        config()->set('auth.providers.usora.driver', 'usora');
        config()->set('auth.guards.web.provider', 'usora');

        // set password configuration
        config()->set('auth.passwords.users.provider', 'usora');
        config()->set('auth.passwords.users.table', config('usora.table_prefix') . 'password_resets');

        $app->register(UsoraServiceProvider::class);
    }
}