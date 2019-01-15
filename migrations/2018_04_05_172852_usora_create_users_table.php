<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsoraCreateUsersTable extends Migration
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
                config('usora.tables.users'),
                function (Blueprint $table) {
                    $table->increments('id');

                    $table->string('email')
                        ->unique();
                    $table->string('password');
                    $table->string('remember_token')
                        ->nullable();
                    $table->string('session_salt')
                        ->nullable();

                    $table->string('display_name')
                        ->index();

                    $table->timestamp('created_at')
                        ->nullable()
                        ->index();
                    $table->timestamp('updated_at')
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
        Schema::dropIfExists(config('usora.tables.users'));
    }
}
