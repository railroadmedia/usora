<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsoraCreateUserTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('usora.database_connection_name'))
            ->create(
                config('usora.tables.user_topics'),
                function (Blueprint $table) {
                    $table->increments('id');

                    $table->integer('user_id')
                        ->index();

                    $table->string('topic', 250);

                    $table->timestamp('created_at')
                        ->nullable()
                        ->index();
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
        Schema::dropIfExists(config('usora.tables.user_topics'));
    }
}
