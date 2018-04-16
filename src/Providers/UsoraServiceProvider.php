<?php

namespace Railroad\Usora\Providers;

use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use PDO;
use Railroad\Usora\Services\ConfigService;

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
        ConfigService::$tableUsers = ConfigService::$tablePrefix . 'users';

        // middleware
        ConfigService::$authenticationControllerMiddleware = config('usora.authentication_controller_middleware');

        // migrations and routes and views
        if (ConfigService::$dataMode == 'host') {
            $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        }
        $this->loadRoutesFrom(__DIR__ . '/../../routes/routes.php');
        $this->loadViewsFrom(__DIR__ . '/../../views', 'usora');

        // events
        $listens = [

            // always return associated arrays from database queries
            StatementPrepared::class => [
                function (StatementPrepared $event) {
                    if ($event->connection->getName() ==
                        ConfigService::$connectionMaskPrefix . ConfigService::$databaseConnectionName) {
                        $event->statement->setFetchMode(PDO::FETCH_ASSOC);
                    }
                }
            ],
        ];

        foreach ($listens as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(AuthenticationServiceProvider::class);
    }
}