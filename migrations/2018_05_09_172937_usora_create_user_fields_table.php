<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class UsoraCreateUserFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('usora.database_connection_name'))->create(
            config('usora.tables.user_fields'),
            function (Blueprint $table) {
                $table->increments('id');

                $table->integer('user_id')->index();

                $table->string('key', 191)->index();
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
        Schema::dropIfExists(config('usora.tables.user_fields'));
    }
}
