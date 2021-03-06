<?php

namespace Railroad\Usora\Providers;

use Illuminate\Support\ServiceProvider;
use MikeMcLin\WpPassword\WpPasswordProvider;
use Railroad\Usora\Decorators\UserEntityDecorator;
use Railroad\Usora\Decorators\UserFieldDecorator;
use Railroad\Usora\Services\ConfigService;
use Tymon\JWTAuth\Providers\LaravelServiceProvider;

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
                __DIR__ . '/../../config/jwt.php' => config_path('jwt.php'),
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
     */
    public function register()
    {
        $this->app->register(AuthenticationServiceProvider::class);
        $this->app->register(WpPasswordProvider::class);

        $this->app->register(LaravelServiceProvider::class);
    }
}