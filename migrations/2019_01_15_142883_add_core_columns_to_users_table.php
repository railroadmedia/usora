<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoreColumnsToUsersTable extends Migration
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

                    $table->string('first_name')
                        ->index()
                        ->after('display_name')
                        ->nullable();
                    $table->string('last_name')
                        ->index()
                        ->after('first_name')
                        ->nullable();
                    $table->string('gender')
                        ->index()
                        ->after('last_name')
                        ->nullable();
                    $table->string('country')
                        ->index()
                        ->after('gender')
                        ->nullable();
                    $table->string('region')
                        ->index()
                        ->after('country')
                        ->nullable();
                    $table->string('city')
                        ->index()
                        ->after('region')
                        ->nullable();
                    $table->date('birthday')
                        ->index()
                        ->after('city')
                        ->nullable();
                    $table->bigInteger('phone_number')
                        ->index()
                        ->after('birthday')
                        ->nullable();
                    $table->string('profile_picture_url')
                        ->after('phone_number')
                        ->nullable();
                    $table->string('timezone')
                        ->index()
                        ->after('profile_picture_url')
                        ->nullable();
                    $table->text('settings')
                        ->after('timezone')
                        ->nullable();
                    $table->text('fields')
                        ->after('settings')
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

                    $table->dropColumn('first_name');
                    $table->dropColumn('last_name');
                    $table->dropColumn('gender');
                    $table->dropColumn('country');
                    $table->dropColumn('region');
                    $table->dropColumn('city');
                    $table->dropColumn('birthday');
                    $table->dropColumn('phone_number');
                    $table->dropColumn('profile_picture_url');
                    $table->dropColumn('timezone');
                    $table->dropColumn('settings');
                    $table->dropColumn('fields');

                }
            );
    }
}
