<?php

namespace Railroad\Usora\Providers;

use Illuminate\Support\ServiceProvider;
use Railroad\Usora\Decorators\UserEntityDecorator;
use Railroad\Usora\Decorators\UserFieldDecorator;
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
        ConfigService::$tableUsers = ConfigService::$tablePrefix . config('usora.tables.users');
        ConfigService::$tableUserFields = ConfigService::$tablePrefix . config('usora.tables.user_fields');
        ConfigService::$tableUserData = ConfigService::$tablePrefix . config('usora.tables.user_data');
        ConfigService::$tablePasswordResets = ConfigService::$tablePrefix . config('usora.tables.password_resets');

        // password reset
        ConfigService::$passwordResetNotificationClass = config('usora.password_reset_notification_class');
        ConfigService::$passwordResetNotificationChannel = config('usora.password_reset_notification_channel');

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
     */
    public function register()
    {
        $this->app->register(AuthenticationServiceProvider::class);
    }
}