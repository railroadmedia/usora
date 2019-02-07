<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RememberTokensTable extends Migration
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
                config('usora.tables.remember_tokens'),
                function (Blueprint $table) {

                    $table->increments('id');

                    $table->integer('user_id')
                        ->index();
                    $table->string('token')
                        ->index();

                    $table->text('device_information');

                    $table->timestamp('expires_at')
                        ->default(DB::raw('CURRENT_TIMESTAMP'))
                        ->index();
                    $table->timestamp('created_at')
                        ->default(DB::raw('CURRENT_TIMESTAMP'))
                        ->index();

                }
            );

        Schema::connection(config('usora.database_connection_name'))
            ->table(
                config('usora.tables.users'),
                function (Blueprint $table) {

                    $table->dropColumn('remember_token');

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
        Schema::connection(config('usora.database_connection_name'))
            ->dropIfExists(config('usora.tables.remember_tokens'));

        Schema::connection(config('usora.database_connection_name'))
            ->table(
                config('usora.tables.users'),
                function (Blueprint $table) {

                    $table->string('remember_token')
                        ->nullable();

                }
            );
    }
}
