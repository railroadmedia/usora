<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropFirebaseTokenColumnsFromUsersTable extends Migration
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
                    $table->dropColumn(['firebase_token_web', 'firebase_token_ios', 'firebase_token_android']);
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
                    $table->text('firebase_token_web')
                        ->after('support_note')
                        ->nullable();
                    $table->text('firebase_token_ios')
                        ->after('firebase_token_web')
                        ->nullable();
                    $table->text('firebase_token_android')
                        ->after('firebase_token_ios')
                        ->nullable();
                }
            );
    }
}