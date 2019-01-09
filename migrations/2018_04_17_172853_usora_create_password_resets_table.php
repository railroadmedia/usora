<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class UsoraCreatePasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('usora.database_connection_name'))->create(
            config('usora.tables.password_resets'),
            function(Blueprint $table) {
                $table->increments('id');

                $table->string('email')->index();
                $table->string('token');

                $table->timestamp('created_at')->nullable();
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
        Schema::dropIfExists(config('usora.tables.password_resets'));
    }
}
