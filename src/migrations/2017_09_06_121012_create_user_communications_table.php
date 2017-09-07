<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCommunicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_communications', function (Blueprint $table) {
            $table->increments('id');
			$table->string('object_type', 50)->nullable();
			$table->integer('object_id')->nullable();
			$table->string('type', 100)->comment('Email / Landline / Mobile')->nullable();
			$table->string('value', 250)->nullable();
			$table->boolean('is_primary')->default(0);
			$table->boolean('is_communication')->default(0);
			$table->boolean('is_verified')->default(0);
			$table->boolean('is_visible')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_communications');
    }
}
