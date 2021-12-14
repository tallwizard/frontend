<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invoice_details', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('invoice_headers_id');
			$table->string('code', 50)->nullable(false);
			$table->string('name', 200)->nullable(false);
			$table->string('brand', 100)->nullable(true)->default('Ninguna');
			$table->double('amount', 100, 2)->nullable(false)->unsigned();
			$table->double('price', 100, 2)->nullable(false)->unsigned();
			$table->double('discount', 100, 2)->nullable(true)->unsigned()->default(0);
			$table->string('reason_discount', 200)->nullable(true)->default(' ');
			$table->double('iva', 100, 2)->nullable(false)->unsigned();
			$table->foreign('invoice_headers_id')->references('id')->on('invoice_headers')->onUpdate('cascade')->onDelete('cascade');
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
		Schema::dropIfExists('invoice_details');
	}
}
