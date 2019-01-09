<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class AddUpdatedAtOnEmailChangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('usora.database_connection_name'))->table(
            config('usora.tables.email_changes'),
            function(Blueprint $table) {
                $table->timestamp('updated_at')->after('created_at')->nullable();
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
        Schema::connection(config('usora.database_connection_name'))->table(
            config('usora.tables.email_changes'),
            function ($table) {
                /**
                 * @var $table \Illuminate\Database\Schema\Blueprint
                 */

                $table->dropColumn('updated_at');
            }
        );
    }
}
