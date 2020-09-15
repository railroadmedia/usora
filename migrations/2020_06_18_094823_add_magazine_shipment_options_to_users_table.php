<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMagazineShipmentOptionsToUsersTable extends Migration
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
                config('usora.tables.users'),
                function (Blueprint $table) {

                    $table->boolean('drumeo_ship_magazine')
                        ->index()
                        ->after('use_legacy_video_player')
                        ->nullable();

                    $table->integer('magazine_shipping_address_id')
                        ->index()
                        ->after('drumeo_ship_magazine')
                        ->nullable();
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
                config('usora.tables.users'),
                function (Blueprint $table) {

                    $table->dropColumn('drumeo_ship_magazine');
                    $table->dropColumn('magazine_shipping_address_id');

                }
            );
    }
}
