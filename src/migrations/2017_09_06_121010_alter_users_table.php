<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
			$table->string('type', 100)->comment('Internal / Registered (has password) / Guest (no password)');
			$table->boolean('has_required_fields_filled')->default(0);
			$table->string('status', 50)->nullable();
			$table->dateTime('creation_date')->nullable();
			$table->dateTime('last_login')->nullable();
			$table->string('signup_source', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
			$table->dropColumn('type');
			$table->dropColumn('has_required_fields_filled');
			$table->dropColumn('status');
			$table->dropColumn('creation_date');
			$table->dropColumn('last_login');
			$table->dropColumn('signup_source');
        });
    }
}
