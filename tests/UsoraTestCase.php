<?php

namespace Railroad\Usora\Tests;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Hashing\BcryptHasher;
use Orchestra\Testbench\TestCase;
use Railroad\Usora\Providers\UsoraServiceProvider;
use Railroad\Usora\Repositories\RepositoryBase;
use Railroad\Usora\Services\ConfigService;

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

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate:fresh', []);
        $this->artisan('cache:clear', []);

        $this->faker = $this->app->make(Generator::class);
        $this->databaseManager = $this->app->make(DatabaseManager::class);
        $this->authManager = $this->app->make(AuthManager::class);
        $this->hasher = $this->app->make(BcryptHasher::class);

        RepositoryBase::$connectionMask = null;

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

        $app['config']->set('usora.database_connection_name', 'sqlite');
        $app['config']->set('usora.table_prefix', $defaultConfig['table_prefix']);
        $app['config']->set('usora.data_mode', $defaultConfig['data_mode']);

        $app['config']->set('usora.tables', $defaultConfig['tables']);

        $app['config']->set('usora.domains_to_authenticate_on', $defaultConfig['domains_to_authenticate_on']);
        $app['config']->set(
            'usora.domains_to_check_for_authentication',
            $defaultConfig['domains_to_check_for_authentication']
        );

        $app['config']->set('usora.login_page_path', $defaultConfig['login_page_path']);
        $app['config']->set('usora.login_success_redirect_path', $defaultConfig['login_success_redirect_path']);

        $app['config']->set('database.default', ConfigService::$connectionMaskPrefix . 'sqlite');
        $app['config']->set(
            'database.connections.' . ConfigService::$connectionMaskPrefix . 'sqlite',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]
        );

        // set auth to our custom provider
        $app['config']->set('auth.providers.usora.driver', 'usora');
        $app['config']->set('auth.guards.web.provider', 'usora');

        // set password configuration
        $app['config']->set('auth.passwords.users.provider', 'usora');
        $app['config']->set('auth.passwords.users.table', 'usora');

        $app->register(UsoraServiceProvider::class);
    }
}