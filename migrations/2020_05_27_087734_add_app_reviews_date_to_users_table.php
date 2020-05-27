<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppReviewsDateToUsersTable extends Migration
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

                    $table->timestamp('ios_latest_review_display_date')
                        ->after('use_legacy_video_player')
                        ->nullable();

                    $table->integer('ios_count_review_display')
                        ->after('ios_latest_review_display_date')
                        ->default(0);

                    $table->timestamp('google_latest_review_display_date')
                        ->after('ios_count_review_display')
                        ->nullable();

                    $table->integer('google_count_review_display')
                        ->after('google_latest_review_display_date')
                        ->default(0);
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

                    $table->dropColumn('ios_latest_review_display_date');
                    $table->dropColumn('ios_count_review_display');
                    $table->dropColumn('google_latest_review_display_date');
                    $table->dropColumn('google_count_review_display');

                }
            );
    }
}
