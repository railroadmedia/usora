<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Railroad\Usora\Services\ConfigService;

class UsoraCreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(ConfigService::$databaseConnectionName)->create(
            ConfigService::$tableUsers,
            function(Blueprint $table) {
                $table->increments('id');
                
                $table->string('email')->index();
                $table->string('password');
                $table->string('remember_token');
                $table->string('session_salt');

                $table->string('display_name')->index();

                $table->timestamp('created_at')->nullable()->index();
                $table->timestamp('updated_at')->nullable()->index();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(ConfigService::$tableUsers);
    }
}
