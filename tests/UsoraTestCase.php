<?php

namespace Railroad\Usora\Tests;

use Carbon\Carbon;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Application;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Testing\Fakes\MailFake;
use Illuminate\Support\Testing\Fakes\NotificationFake;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Railroad\Permissions\Providers\PermissionsServiceProvider;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Faker\Factory;
use Railroad\Usora\Faker\Faker;
use Railroad\Usora\Providers\UsoraServiceProvider;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Services\ConfigService;

class UsoraTestCase extends TestCase
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Faker
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

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var MockObject
     */
    protected $permissionServiceMock;

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate:fresh', []);
        $this->artisan('cache:clear', []);

        $this->faker = Factory::create();
        $this->databaseManager = $this->app->make(DatabaseManager::class);
        $this->userRepository = $this->app->make(UserRepository::class);

        $this->permissionServiceMock = $this->getMockBuilder(PermissionService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->app->instance(PermissionService::class, $this->permissionServiceMock);

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

        // setup permissions
        config()->set('permissions.cache_duration', 60 * 60 * 24 * 30);
        config()->set('permissions.database_connection_name', config('usora.connection_mask_prefix') . 'sqlite');
        config()->set('permissions.connection_mask_prefix', 'permissions_');
        config()->set('permissions.data_mode', 'host');
        config()->set('permissions.table_prefix', 'permissions_');
        config()->set('permissions.table_users', config('usora.tables.users'));
        config()->set('permissions.brand', 'drumeo');

        $app->register(PermissionsServiceProvider::class);
    }

    /**
     * Create and store a new user
     *
     * @return int
     */
    public function createNewUser()
    {
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

        return $userId;
    }
}