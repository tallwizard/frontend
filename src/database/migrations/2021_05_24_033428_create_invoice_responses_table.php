<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_responses', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('invoice_headers_id')->unique();
			$table->text('message')->nullable(true);
			$table->text('error')->nullable(true);
			$table->string('email')->nullable(true);
			$table->enum('status', [1, 2, 3]);
			$table->text('mail_message')->nullable(true);
			$table->text('track_id')->nullable(true);
			$table->longText('data_invoice')->nullable(true);
			$table->text('cufe')->nullable(true);
			$table->text('file_name')->nullable(true);
			$table->text('url')->nullable(true);
			$table->text('qr')->nullable(true);
			$table->foreign('invoice_headers_id')->references('id')->on('invoice_headers');
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
        Schema::dropIfExists('invoice_responses');
    }
}
