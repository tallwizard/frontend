<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoteConceptsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('note_concepts', function (Blueprint $table) {
			$table->id();
			$table->string('prefix')->nullable(false);
			$table->string('name', 200)->nullable(false);
			$table->unsignedBigInteger('type_notes_id');
			$table->foreign('type_notes_id')->references('id')->on('type_notes')->onUpdate('cascade')->onDelete('cascade');
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
		Schema::dropIfExists('note_concepts');
	}
}
