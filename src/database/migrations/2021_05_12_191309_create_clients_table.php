<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('clients', function (Blueprint $table) {
			$table->id();
			$table->string('name', 200)->nullable(false);
			$table->string('last_name', 200)->nullable(true)->default(' ');
			$table->string('type_clients_id', 10)->nullable(false);
			$table->string('type_documents_id', 10)->nullable(false);
			$table->string('document', 100)->nullable(false)->unique();
			$table->string('phone', 20)->nullable(false);
			$table->string('email', 100)->nullable(false);
			$table->unsignedBigInteger('cities_id');
			$table->string('address', 200)->nullable(false);
			$table->unsignedBigInteger('users_id');
			$table->foreign('type_clients_id')->references('id')->on('type_clients')->onUpdate('cascade');
			$table->foreign('type_documents_id')->references('id')->on('type_documents')->onUpdate('cascade');
			$table->foreign('cities_id')->references('id')->on('cities')->onUpdate('cascade');
			$table->foreign('users_id')->references('id')->on('users')->onUpdate('cascade');
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
		Schema::dropIfExists('clients');
	}
}
