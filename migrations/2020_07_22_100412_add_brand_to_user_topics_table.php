<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandToUserTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('usora.database_connection_name'))
            ->table(
                config('usora.tables.user_topics'),
                function (Blueprint $table) {

                    $table->string('brand')
                        ->after('topic');
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
            ->table(
                config('usora.tables.user_topics'),
                function (Blueprint $table) {

                    $table->dropColumn('brand');
                }
            );
    }
}
