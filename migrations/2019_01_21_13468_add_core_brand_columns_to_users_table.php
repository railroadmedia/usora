<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoreBrandColumnsToUsersTable extends Migration
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

                    $table->integer('legacy_drumeo_id')
                        ->index()
                        ->after('permission_level')
                        ->nullable();

                    $table->integer('legacy_pianote_id')
                        ->index()
                        ->after('legacy_drumeo_id')
                        ->nullable();

                    $table->integer('legacy_guitareo_id')
                        ->index()
                        ->after('legacy_pianote_id')
                        ->nullable();

                    $table->integer('legacy_drumeo_wordpress_id')
                        ->index()
                        ->after('legacy_drumeo_id')
                        ->nullable();

                    $table->integer('legacy_drumeo_ipb_id')
                        ->index()
                        ->after('legacy_drumeo_wordpress_id')
                        ->nullable();

                    $table->string('notify_on_lesson_comment_reply')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->default(true);

                    $table->boolean('notify_weekly_update')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->default(true);

                    $table->boolean('notify_on_forum_post_like')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->default(true);

                    $table->boolean('notify_on_forum_followed_thread_reply')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->default(true);

                    $table->boolean('notify_on_lesson_comment_like')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->default(true);

                    $table->integer('drums_playing_since_year')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('drums_gear_photo')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('drums_gear_cymbal_brands')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('drums_gear_set_brands')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('drums_gear_hardware_brands')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('drums_gear_stick_brands')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->integer('guitar_playing_since_year')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('guitar_gear_photo')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('guitar_gear_guitar_brands')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('guitar_gear_amp_brands')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('guitar_gear_pedal_brands')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('guitar_gear_string_brands')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->integer('piano_playing_since_year')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('piano_gear_photo')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('piano_gear_piano_brands')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
                        ->nullable();

                    $table->string('piano_gear_keyboard_brands')
                        ->index()
                        ->after('legacy_drumeo_ipb_id')
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

                    $table->dropColumn('legacy_drumeo_id');
                    $table->dropColumn('legacy_pianote_id');
                    $table->dropColumn('legacy_guitareo_id');
                    $table->dropColumn('legacy_drumeo_wordpress_id');
                    $table->dropColumn('legacy_drumeo_ipb_id');
                    $table->dropColumn('notify_on_lesson_comment_reply');
                    $table->dropColumn('notify_weekly_update');
                    $table->dropColumn('notify_on_forum_post_like');
                    $table->dropColumn('notify_on_forum_followed_thread_reply');
                    $table->dropColumn('notify_on_lesson_comment_like');
                    $table->dropColumn('drums_playing_since_year');
                    $table->dropColumn('drums_gear_photo');
                    $table->dropColumn('drums_gear_cymbal_brands');
                    $table->dropColumn('drums_gear_set_brands');
                    $table->dropColumn('drums_gear_hardware_brands');
                    $table->dropColumn('drums_gear_stick_brands');
                    $table->dropColumn('guitar_playing_since_year');
                    $table->dropColumn('guitar_gear_photo');
                    $table->dropColumn('guitar_gear_guitar_brands');
                    $table->dropColumn('guitar_gear_amp_brands');
                    $table->dropColumn('guitar_gear_pedal_brands');
                    $table->dropColumn('guitar_gear_string_brands');
                    $table->dropColumn('piano_playing_since_year');
                    $table->dropColumn('piano_gear_photo');
                    $table->dropColumn('piano_gear_piano_brands');
                    $table->dropColumn('piano_gear_keyboard_brands');

                }
            );
    }
}
