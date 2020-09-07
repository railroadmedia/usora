<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSkillLevelToUsersTable extends Migration
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

                    $table->integer('drums_skill_level')
                        ->after('use_legacy_video_player')
                        ->nullable();

                    $table->integer('guitar_skill_level')
                        ->after('drums_skill_level')
                        ->nullable();

                    $table->integer('piano_skill_level')
                        ->after('guitar_skill_level')
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

                    $table->dropColumn('drums_skill_level');

                    $table->dropColumn('guitar_skill_level');

                    $table->dropColumn('piano_skill_level');
                }
            );
    }
}
