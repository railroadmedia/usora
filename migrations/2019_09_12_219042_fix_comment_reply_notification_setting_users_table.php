<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixCommentReplyNotificationSettingUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection(config('usora.database_connection_name'))
            ->table(
                config('usora.tables.users')
            )
            ->where('birthday', '=', '0000-00-00')
            ->update(['birthday' => null]);

        Schema::connection(config('usora.database_connection_name'))
            ->table(
                config('usora.tables.users'),
                function (Blueprint $table) {

                    $table->boolean('notify_on_lesson_comment_reply')
                        ->default(true)
                        ->change();
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

                    $table->string('notify_on_lesson_comment_reply')
                        ->default(true)
                        ->change();

                }
            );
    }
}
