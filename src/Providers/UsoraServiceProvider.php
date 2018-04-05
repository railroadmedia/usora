<?php

namespace Railroad\Usora\Providers;

use Illuminate\Support\ServiceProvider;
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
        $this->publishes(
            [
                __DIR__ . '/../config/usora.php' => config_path('usora.php'),
            ]
        );

        // database
        ConfigService::$databaseConnectionName = config('railcontent.database_connection_name');
        ConfigService::$connectionMaskPrefix = config('railcontent.connection_mask_prefix');

        // tables
        ConfigService::$tablePrefix = config('railcontent.table_prefix');
        ConfigService::$tableUsers = ConfigService::$tablePrefix . 'users';

        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}