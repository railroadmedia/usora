<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificationSummaryFrequencyToUsersTable extends Migration
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

                    $table->integer('notifications_summary_frequency_minutes')
                        ->index()
                        ->after('notify_on_lesson_comment_like')
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

                    $table->dropColumn('notifications_summary_frequency_minutes');

                }
            );
    }
}
