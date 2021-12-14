<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResolutionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('resolutions', function (Blueprint $table) {
			$table->id();
			$table->string('code', 10)->nullable(false)->unique();
			$table->string('number', 200)->nullable(false);
			$table->string('key', 200)->nullable(false);
			$table->date('start_date')->nullable(false);
			$table->date('end_date')->nullable(false);
			$table->string('start_consecutive', 10)->nullable(false);
			$table->string('end_consecutive', 10)->nullable(false);
			$table->string('prefix', 10)->nullable(false);
			$table->boolean('active')->default(true);
			$table->unsignedBigInteger('dependences_id');
			$table->unsignedBigInteger('users_id');
			$table->foreign('users_id')->references('id')->on('users')->onUpdate('cascade');
			$table->foreign('dependences_id')->references('id')->on('dependences');
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
		Schema::dropIfExists('resolutions');
	}
}
