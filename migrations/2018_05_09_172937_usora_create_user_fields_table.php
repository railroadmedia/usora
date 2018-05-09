<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Railroad\Usora\Services\ConfigService;

class UsoraCreateUserFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(ConfigService::$databaseConnectionName)->create(
            ConfigService::$tableUserFields,
            function (Blueprint $table) {
                $table->increments('id');

                $table->integer('user_id')->index();

                $table->string('key', 255)->index();
                $table->text('value')->nullable();
                $table->string('index', 191)->index()->nullable();

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
        Schema::dropIfExists(ConfigService::$tableUserFields);
    }
}
