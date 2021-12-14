<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceHeadersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invoice_headers', function (Blueprint $table) {
			$table->id();
			$table->string('code', 10)->nullable(false);
			$table->bigInteger('consecutive')->nullable(false)->unsigned()->autoIncrement(false);
			$table->string('description', 300)->nullable(false);
			$table->date('expiration_date')->nullable(false);
			$table->unsignedBigInteger('clients_id');
			$table->string('payment_methods_id', 10)->nullable(false);
			$table->unsignedBigInteger('way_payments_id');
			$table->string('bank_account', 200)->nullable(true)->default(' ');
			$table->enum('status', [1, 2, 3]);
			$table->double('total', 200, 2)->nullable(false)->unsigned()->default(0);
			$table->double('balance', 200, 2)->nullable(false)->unsigned()->default(0);
			$table->unsignedBigInteger('users_id');
			$table->foreign('clients_id')->references('id')->on('clients');
			$table->foreign('payment_methods_id')->references('id')->on('payment_methods')->onUpdate('cascade');
			$table->foreign('way_payments_id')->references('id')->on('way_payments')->onUpdate('cascade');
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
		Schema::dropIfExists('invoice_headers');
	}
}
