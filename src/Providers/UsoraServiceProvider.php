<?php

namespace Railroad\Usora\Providers;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\Migrations\MigrationsServiceProvider;
use MikeMcLin\WpPassword\WpPasswordProvider;
use Railroad\Usora\Decorators\UserEntityDecorator;
use Railroad\Usora\Decorators\UserFieldDecorator;
use Railroad\Usora\Services\ConfigService;
use Redis;

class UsoraServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // publish config file
        $this->publishes(
            [
                __DIR__ . '/../../config/usora.php' => config_path('usora.php'),
            ]
        );

        // authentication
        ConfigService::$authenticationMode = config('usora.authentication_mode');
        ConfigService::$domainsToAuthenticateOn = config('usora.domains_to_authenticate_on');
        ConfigService::$domainsToCheckForAuthenticateOn = config('usora.domains_to_check_for_authentication');

        ConfigService::$loginPagePath = config('usora.login_page_path');
        ConfigService::$loginSuccessRedirectPath = config('usora.login_success_redirect_path');
        ConfigService::$rememberMe = config('usora.remember_me');

        // database
        ConfigService::$databaseConnectionName = config('usora.database_connection_name');
        ConfigService::$connectionMaskPrefix = config('usora.connection_mask_prefix');
        ConfigService::$dataMode = config('usora.data_mode');

        // tables
        ConfigService::$tablePrefix = config('usora.table_prefix');
        ConfigService::$tableUsers = ConfigService::$tablePrefix . config('usora.tables.users');
        ConfigService::$tableUserFields = ConfigService::$tablePrefix . config('usora.tables.user_fields');
        ConfigService::$tableUserData = ConfigService::$tablePrefix . config('usora.tables.user_data');
        ConfigService::$tablePasswordResets = ConfigService::$tablePrefix . config('usora.tables.password_resets');
        ConfigService::$tableEmailChanges = ConfigService::$tablePrefix . config('usora.tables.email_changes');

        // password reset
        ConfigService::$passwordResetNotificationClass = config('usora.password_reset_notification_class');
        ConfigService::$passwordResetNotificationChannel = config('usora.password_reset_notification_channel');

        // email change
        ConfigService::$emailChangeNotificationClass = config('usora.email_change_notification_class');
        ConfigService::$emailChangeNotificationChannel = config('usora.email_change_notification_channel');
        ConfigService::$emailChangeTtl = config('usora.email_change_token_ttl');

        // middleware
        ConfigService::$authenticationControllerMiddleware = config('usora.authentication_controller_middleware');

        // migrations and routes and views
        if (ConfigService::$dataMode == 'host') {
            $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        }

        $this->loadRoutesFrom(__DIR__ . '/../../routes/routes.php');
        $this->loadViewsFrom(__DIR__ . '/../../views', 'usora');

        // configure resora
        config()->set(
            'resora.decorators.users',
            array_merge(
                config()->get('resora.decorators.users', []),
                [UserFieldDecorator::class, UserEntityDecorator::class]
            )
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function register()
    {
        $this->app->register(AuthenticationServiceProvider::class);
        $this->app->register(WpPasswordProvider::class);

        // setup doctrine
        // this is where proxy class files will be stored
        $proxyDir = sys_get_temp_dir();

        // redis cache
        $redis = new Redis();
        $redis->connect(config('usora.redis_host'), config('usora.redis_port'));

        $redisCache = new RedisCache();
        $redisCache->setRedis($redis);

        // annotations
        // setup default doctrine annotations
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
            __DIR__ . '/../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );

        // setup annotation reader
        $annotationReader = new AnnotationReader();
        $cachedAnnotationReader = new CachedReader(
            $annotationReader, $redisCache
        );

        // setup annotation driver chain
        $driverChain = new MappingDriverChain();

        \Gedmo\DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
            $driverChain,
            $cachedAnnotationReader
        );

        $annotationDriver = new AnnotationDriver(
            $cachedAnnotationReader, [__DIR__ . '/../Entities']
        );

        $driverChain->addDriver($annotationDriver, 'Railroad\Usora\Entities');

        // setup event listeners for timestampable trait
        $timestampableListener = new \Gedmo\Timestampable\TimestampableListener();
        $timestampableListener->setAnnotationReader($cachedAnnotationReader);

        // setup event manager
        $eventManager = new \Doctrine\Common\EventManager();
        $eventManager->addEventSubscriber($timestampableListener);

        // setup config
        $ormConfiguration = new Configuration();
        $ormConfiguration->setMetadataCacheImpl($redisCache);
        $ormConfiguration->setQueryCacheImpl($redisCache);
        $ormConfiguration->setResultCacheImpl($redisCache);
        $ormConfiguration->setProxyDir($proxyDir);
        $ormConfiguration->setProxyNamespace('DoctrineProxies');
        $ormConfiguration->setAutoGenerateProxyClasses(config('usora.development_mode'));
        $ormConfiguration->setMetadataDriverImpl($driverChain);

        // create entity manager with proper db details
        if (config('usora.database_in_memory') === true) {
            $databaseOptions = [
                'driver' => config('usora.database_driver'),
                'dbname' => config('usora.database_name'),
                'user' => config('usora.database_user'),
                'password' => config('usora.database_password'),
                'host' => config('usora.database_host'),
            ];
        } else {
            $databaseOptions = [
                'driver' => config('usora.database_driver'),
                'user' => config('usora.database_user'),
                'password' => config('usora.database_password'),
                'memory' => true,
            ];
        }

        $entityManager = EntityManager::create(
            $databaseOptions,
            $ormConfiguration,
            $eventManager
        );

        // register the entity manager as a singleton
        app()->instance(EntityManager::class, $entityManager);
    }
}