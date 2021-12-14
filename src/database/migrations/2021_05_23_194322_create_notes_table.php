<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notes', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('invoice_headers_id');
			$table->string('code', 10)->nullable(false);
			$table->bigInteger('consecutive')->nullable(false)->unsigned()->autoIncrement(false);
			$table->string('description',300)->nullable(false);
			$table->unsignedBigInteger('note_concepts_id');
			$table->enum('status', [1, 2, 3]);
			$table->double('total', 200, 2)->nullable(false)->unsigned()->default(0);
			$table->unsignedBigInteger('users_id');
			$table->foreign('users_id')->references('id')->on('users')->onUpdate('cascade');
			$table->foreign('invoice_headers_id')->references('id')->on('invoice_headers');
			$table->foreign('note_concepts_id')->references('id')->on('note_concepts');
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
		Schema::dropIfExists('notes');
	}
}
