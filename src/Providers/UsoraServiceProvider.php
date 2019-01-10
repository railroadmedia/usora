<?php

namespace Railroad\Usora\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use MikeMcLin\WpPassword\WpPasswordProvider;
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
    }
}
