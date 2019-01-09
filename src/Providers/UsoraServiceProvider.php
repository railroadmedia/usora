<?php

namespace Railroad\Usora\Providers;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\Migrations\MigrationsServiceProvider;
use MikeMcLin\WpPassword\WpPasswordProvider;
use Railroad\Usora\Decorators\UserEntityDecorator;
use Railroad\Usora\Decorators\UserFieldDecorator;
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

        // only run migrations if this is the master 'host' implementation
        if (config('usora.data_mode') == 'host') {
            $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        }

        if (config('usora.autoload_all_routes') == true) {
            $this->routeRegistrar->registerAll();
        }

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
        $this->app->register(AuthenticationServiceProvider::class);
        $this->app->register(WpPasswordProvider::class);

        $this->app->register(LaravelServiceProvider::class);

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

        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

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
        $ormConfiguration->setNamingStrategy(new \Doctrine\ORM\Mapping\UnderscoreNamingStrategy(CASE_LOWER));

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