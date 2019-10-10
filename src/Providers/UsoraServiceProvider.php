<?php

namespace Railroad\Usora\Providers;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Gedmo\DoctrineExtensions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use MikeMcLin\WpPassword\WpPasswordProvider;
use Railroad\Doctrine\TimestampableListener;
use Railroad\Doctrine\Types\Carbon\CarbonDateTimeTimezoneType;
use Railroad\Doctrine\Types\Carbon\CarbonDateTimeType;
use Railroad\Doctrine\Types\Carbon\CarbonDateType;
use Railroad\Doctrine\Types\Carbon\CarbonTimeType;
use Railroad\Doctrine\Types\Domain\GenderType;
use Railroad\Doctrine\Types\Domain\PhoneNumberType;
use Railroad\Doctrine\Types\Domain\TimezoneType;
use Railroad\Doctrine\Types\Domain\UrlType;
use Railroad\Usora\Commands\MigrateUserFieldsToColumns;
use Railroad\Usora\Managers\UsoraEntityManager;
use Railroad\Usora\Routes\RouteRegistrar;
use Redis;
use Tymon\JWTAuth\Providers\LaravelServiceProvider;

class UsoraServiceProvider extends ServiceProvider
{
    /**
     * @var RouteRegistrar
     */
    private $routeRegistrar;

    /**
     * UsoraServiceProvider constructor.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->routeRegistrar = $application->make(RouteRegistrar::class);
    }

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
                __DIR__ . '/../../config/jwt.php' => config_path('jwt.php'),
            ]
        );

        // migrations: only run migrations if this is the master 'host' implementation
        if (config('usora.data_mode') == 'host') {
            $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        }

        // routes
        if (config('usora.autoload_all_routes') == true) {
            $this->routeRegistrar->registerAll();
        }

        // commands
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    MigrateUserFieldsToColumns::class,
                ]
            );
        }

        // views
        $this->loadViewsFrom(__DIR__ . '/../../views', 'usora');
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
        // laravel auth integration
        $this->app->register(AuthenticationServiceProvider::class);

        // wordpress password hash support
        $this->app->register(WpPasswordProvider::class);

        // jwt
        $this->app->register(LaravelServiceProvider::class);

        // doctrine
        Type::overrideType('datetime', CarbonDateTimeType::class);
        Type::overrideType('datetimetz', CarbonDateTimeTimezoneType::class);
        Type::overrideType('date', CarbonDateType::class);
        Type::overrideType('time', CarbonTimeType::class);

        !Type::hasType('url') ? Type::addType('url', UrlType::class) : null;
        !Type::hasType('phone_number') ? Type::addType('phone_number', PhoneNumberType::class) : null;
        !Type::hasType('timezone') ? Type::addType('timezone', TimezoneType::class) : null;
        !Type::hasType('gender') ? Type::addType('gender', GenderType::class) : null;

        // set proxy dir to temp folder on server
        $proxyDir = sys_get_temp_dir();

        // setup redis
        $redis = new Redis();
        $redis->connect(
            config('usora.redis_host'),
            config('usora.redis_port')
        );
        $redisCache = new RedisCache();
        $redisCache->setRedis($redis);

        // redis cache instance is referenced in laravel container to be reused when needed
        app()->instance(RedisCache::class, $redisCache);

        // file cache
        $phpFileCache = new PhpFileCache($proxyDir);

        AnnotationRegistry::registerLoader('class_exists');

        $annotationReader = new AnnotationReader();

        $cachedAnnotationReader = new CachedReader(
            $annotationReader, $redisCache
        );

        $driverChain = new MappingDriverChain();

        DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
            $driverChain,
            $cachedAnnotationReader
        );

        foreach (config('usora.entities') as $driverConfig) {
            $annotationDriver = new AnnotationDriver(
                $cachedAnnotationReader, $driverConfig['path']
            );

            $driverChain->addDriver(
                $annotationDriver,
                $driverConfig['namespace']
            );
        }

        // driver chain instance is referenced in laravel container to be reused when needed
        app()->instance(MappingDriverChain::class, $driverChain);

        $timestampableListener = new TimestampableListener();
        $timestampableListener->setAnnotationReader($cachedAnnotationReader);

        $eventManager = new EventManager();
        $eventManager->addEventSubscriber($timestampableListener);

        $ormConfiguration = new Configuration();
        $ormConfiguration->setMetadataCacheImpl($phpFileCache);
        $ormConfiguration->setQueryCacheImpl($phpFileCache);
        $ormConfiguration->setResultCacheImpl($redisCache);
        $ormConfiguration->setProxyDir($proxyDir);
        $ormConfiguration->setProxyNamespace('DoctrineProxies');
        $ormConfiguration->setAutoGenerateProxyClasses(
            config('usora.development_mode') ? AbstractProxyFactory::AUTOGENERATE_ALWAYS :
                AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS
        );
        $ormConfiguration->setMetadataDriverImpl($driverChain);
        $ormConfiguration->setNamingStrategy(
            new UnderscoreNamingStrategy(CASE_LOWER)
        );

        // orm configuration instance is referenced in laravel container to be reused when needed
        app()->instance(Configuration::class, $ormConfiguration);

        if (config('usora.database_in_memory') !== true) {
            $databaseOptions = [
                'driver' => config('usora.database_driver'),
                'dbname' => config('usora.database_name'),
                'user' => config('usora.database_user'),
                'password' => config('usora.database_password'),
                'host' => config('usora.database_host'),
            ];
        }
        else {
            $databaseOptions = [
                'driver' => config('usora.database_driver'),
                'user' => config('usora.database_user'),
                'password' => config('usora.database_password'),
                'memory' => true,
            ];
        }

        // register the default entity manager
        $entityManager = UsoraEntityManager::create(
            $databaseOptions,
            $ormConfiguration,
            $eventManager
        );

        // register the entity manager as a singleton
        app()->instance(UsoraEntityManager::class, $entityManager);
    }
}
