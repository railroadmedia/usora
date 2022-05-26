<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendNotificationsTypeToUsoraUsersTable extends Migration
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

                    $table->boolean('send_mobile_app_push_notifications')
                        ->index()
                        ->after('notify_on_lesson_comment_reply')
                        ->default(true);
                    $table->boolean('send_email_notifications')
                        ->index()
                        ->after('send_mobile_app_push_notifications')
                        ->default(true);
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
                config('usora.tables.firebase_tokens'),
                function (Blueprint $table) {

                    $table->dropColumn('send_mobile_app_push_notifications');
                    $table->dropColumn('send_email_notifications');
                }
            );
    }
}
