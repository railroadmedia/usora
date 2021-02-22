<?php

namespace Railroad\Usora\Tests;

use App\Providers\EcommerceUserProvider;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Application;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Testing\Fakes\MailFake;
use Illuminate\Support\Testing\Fakes\NotificationFake;
use MikeMcLin\WpPassword\WpPasswordProvider;
use Mpociot\ApiDoc\ApiDocGeneratorServiceProvider;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Railroad\Doctrine\Hydrators\FakeDataHydrator;
use Railroad\Doctrine\Providers\DoctrineServiceProvider;
use Railroad\DoctrineArrayHydrator\Contracts\UserProviderInterface;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Faker\Factory;
use Railroad\Usora\Faker\Faker;
use Railroad\Usora\Managers\UsoraEntityManager;
use Railroad\Usora\Providers\UsoraServiceProvider;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Tests\Providers\UsoraTestingUserProvider;

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

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var FakeDataHydrator
     */
    protected $fakeDataHydrator;

    protected function setUp()
    {
        parent::setUp();

        // Run the schema update tool using our entity metadata
        $this->entityManager = app(UsoraEntityManager::class);

        $this->entityManager->getMetadataFactory()
            ->getCacheDriver()
            ->deleteAll();

        // make sure laravel is using the same connection
        DB::connection()
            ->setPdo(
                $this->entityManager->getConnection()
                    ->getWrappedConnection()
            );
        DB::connection()
            ->setReadPdo(
                $this->entityManager->getConnection()
                    ->getWrappedConnection()
            );

        $this->artisan('migrate:fresh', []);
        $this->artisan('cache:clear', []);

        $this->faker = Factory::create();
        $this->fakeDataHydrator = new FakeDataHydrator($this->entityManager);

        $this->authManager = $this->app->make(AuthManager::class);
        $this->hasher = $this->app->make(BcryptHasher::class);
        $this->notificationFake = Notification::fake();

        Mail::fake();
        $this->mailFake = Mail::getFacadeRoot();

        Carbon::setTestNow(Carbon::now());

        $this->permissionServiceMock =
            $this->getMockBuilder(PermissionService::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->app->instance(PermissionService::class, $this->permissionServiceMock);

        $this->app['router']->middlewareGroup('test_public_route_group', []);
        $this->app['router']->middlewareGroup('test_logged_in_route_group', []);
        $this->app['router']->middlewareGroup('app_public', []);
        $this->app['router']->middlewareGroup('app_authed', []);

        $this->app->instance(UserProviderInterface::class, app()->make(UsoraTestingUserProvider::class));
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
        $apiDocConfig = require(__DIR__ . '/../config/apidoc.php');


        foreach ($defaultConfig as $key => $value) {
            config()->set('usora.' . $key, $value);
        }

        $jwtConfig = require(__DIR__ . '/../config/jwt.php');
        foreach ($jwtConfig as $key => $value) {
            config()->set('jwt.' . $key, $value);
        }

        // misc
        config()->set('app.debug', true);
        config()->set('usora.data_mode', 'host');
        config()->set('usora.authentication_controller_middleware', []);

        // db
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

        // database
        config()->set('usora.database_user', 'root');
        config()->set('usora.database_password', 'root');
        config()->set('usora.database_driver', 'pdo_sqlite');
        config()->set('usora.database_in_memory', true);

        config()->set('usora.redis_host', $defaultConfig['redis_host']);
        config()->set('usora.redis_port', $defaultConfig['redis_port']);
        config()->set('usora.development_mode', $defaultConfig['development_mode'] ?? true);
        config()->set('usora.database_driver', 'pdo_sqlite');
        config()->set('usora.database_user', 'root');
        config()->set('usora.database_password', 'root');
        config()->set('usora.database_in_memory', true);

        // if new packages entities are required for testing, their entity directory/namespace config should be merged here
        config()->set('usora.entities', $defaultConfig['entities']);

        config()->set('usora.autoload_all_routes', true);
        config()->set('usora.route_middleware_public_groups', ['test_public_route_group']);
        config()->set('usora.route_middleware_logged_in_groups', ['test_logged_in_route_group']);

        // set auth to our custom provider
        config()->set('auth.providers.usora.driver', 'usora');
        config()->set('auth.guards.web.provider', 'usora');
        config()->set('auth.guards.web.driver', 'usora');

        // set password configuration
        config()->set('auth.passwords.users.provider', 'usora');
        config()->set('auth.passwords.users.table', config('usora.tables.password_resets'));

        $app->register(UsoraServiceProvider::class);

        // setup permissions
        config()->set('permissions.cache_duration', 60 * 60 * 24 * 30);
        config()->set('permissions.database_connection_name', config('usora.connection_mask_prefix') . 'sqlite');
        config()->set('permissions.connection_mask_prefix', 'permissions_');
        config()->set('permissions.data_mode', 'host');
        config()->set('permissions.table_prefix', 'permissions_');
        config()->set('permissions.table_users', config('usora.tables.users'));
        config()->set('permissions.brand', 'drumeo');

        //apidoc
        $app['config']->set('apidoc.output', $apiDocConfig['output']);
        $app['config']->set('apidoc.routes', $apiDocConfig['routes']);
        $app['config']->set('apidoc.example_languages', $apiDocConfig['example_languages']);
        $app['config']->set('apidoc.fractal', $apiDocConfig['fractal']);

        $app->register(WpPasswordProvider::class);
        $app->register(ApiDocGeneratorServiceProvider::class);
    }
}